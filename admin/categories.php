<?php
// admin/categories.php
// Admin: list, add, edit and delete categories.

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_guard.php';   // admins only

$errors = [];
$old = ['id' => 0, 'name' => '', 'description' => '', 'image_url' => null];   // id 0 = adding

// Edit mode: categories.php?edit=5 fills the form with category 5
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, name, description, image_url FROM categories WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('danger', 'Category not found.');
        redirect('admin/categories.php');
    }
    $old = $found;
}

// Delete a category (the small form on each row sends action=delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    // Security first
    verify_csrf();

    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, image_url FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $to_delete = $stmt->fetch();
    if (!$to_delete) {
        set_flash('danger', 'Category not found.');
        redirect('admin/categories.php');
    }

    // Count ALL its products, even soft-deleted ones (the database still sees them)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        set_flash('danger', 'This category still has products. Move or delete them first.');
        redirect('admin/categories.php');
    }

    // No products: delete the row, then its image file
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    delete_image($to_delete['image_url']);

    set_flash('success', 'Category deleted.');
    redirect('admin/categories.php');
}

// Add or edit a category (the form below sends POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security first
    verify_csrf();

    // Read the inputs, and make the slug from the name
    $old['id']          = (int) ($_POST['id'] ?? 0);   // 0 = add, a number = edit
    $old['name']        = trim($_POST['name'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $slug = make_slug($old['name']);

    // Editing? Load the category first (we need its current image)
    $current = null;
    if ($old['id'] > 0) {
        $stmt = $pdo->prepare('SELECT id, image_url FROM categories WHERE id = ?');
        $stmt->execute([$old['id']]);
        $current = $stmt->fetch();
        if (!$current) {
            set_flash('danger', 'Category not found.');
            redirect('admin/categories.php');
        }
        $old['image_url'] = $current['image_url'];
    }

    // Validation
    if ($old['name'] === '') {
        $errors[] = 'Name is required.';
    } elseif (strlen($old['name']) > 100) {
        $errors[] = 'Name is too long.';
    } elseif ($slug === '') {
        $errors[] = 'Name must contain English letters or numbers.';
    }

    // The image is optional: check it only if the admin chose a file
    $image = $_FILES['image'] ?? null;
    $has_image = $image && $image['error'] !== UPLOAD_ERR_NO_FILE;
    if ($has_image) {
        $image_problem = image_error($image);
        if ($image_problem !== '') {
            $errors[] = $image_problem;
        }
    }

    // Is the slug used by ANOTHER category? (when adding, id is 0, so nothing is skipped)
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $old['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'A category with this name already exists.';
        }
    }

    // Save, then flash + redirect
    if (!$errors) {
        if ($current) {
            // Edit: keep the old image, unless a new one was chosen
            $image_url = $has_image ? save_image($image, 'categories') : $current['image_url'];

            $stmt = $pdo->prepare(
                'UPDATE categories SET name = ?, slug = ?, description = ?, image_url = ? WHERE id = ?'
            );
            $stmt->execute([
                $old['name'],
                $slug,
                $old['description'] === '' ? null : $old['description'],
                $image_url,
                $old['id'],
            ]);

            // A new image replaced the old one, so delete the OLD file
            if ($has_image) {
                delete_image($current['image_url']);
            }
            set_flash('success', 'Category updated.');
        } else {
            // Add: save the image only now, when everything else is OK
            $image_url = $has_image ? save_image($image, 'categories') : null;

            $stmt = $pdo->prepare(
                'INSERT INTO categories (name, slug, description, image_url) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $old['name'],
                $slug,
                $old['description'] === '' ? null : $old['description'],
                $image_url,
            ]);
            set_flash('success', 'Category added.');
        }
        redirect('admin/categories.php');
    }
}

// List: every category + how many products it has
$stmt = $pdo->prepare(
    'SELECT c.id, c.name, c.slug, c.image_url, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.deleted_at IS NULL
     GROUP BY c.id
     ORDER BY c.name'
);
$stmt->execute();
$categories = $stmt->fetchAll();

$page_title = 'Categories';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Categories</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3"><?= $old['id'] ? 'Edit category' : 'Add category' ?></h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>admin/categories.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($old['id']) ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control" maxlength="100"
                    value="<?= e($old['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                <textarea id="description" name="description" class="form-control" rows="2"><?= e($old['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image <span class="text-muted">(optional, JPG / PNG / WEBP, max 2 MB)</span></label>
                <?php if ($old['image_url']): ?>
                    <div class="mb-2">
                        <img src="<?= e(UPLOAD_URL . $old['image_url']) ?>" alt="" width="80" height="80" style="object-fit: cover;">
                        <span class="text-muted small ms-2">Current image. Choose a new file to replace it.</span>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-control"
                    accept="image/jpeg,image/png,image/webp">
            </div>

            <button type="submit" class="btn btn-dark"><?= $old['id'] ? 'Save changes' : 'Add category' ?></button>
            <?php if ($old['id']): ?>
                <a href="<?= BASE_URL ?>admin/categories.php" class="btn btn-link">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Products</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td>
                    <?php if ($category['image_url']): ?>
                        <img src="<?= e(UPLOAD_URL . $category['image_url']) ?>" alt="" width="50" height="50" style="object-fit: cover;">
                    <?php else: ?>
                        <span class="text-muted">No image</span>
                    <?php endif; ?>
                </td>
                <td><?= e($category['name']) ?></td>
                <td><?= e($category['slug']) ?></td>
                <td><?= e($category['product_count']) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>admin/categories.php?edit=<?= e($category['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="POST" action="<?= BASE_URL ?>admin/categories.php" class="d-inline"
                        onsubmit="return confirm('Delete this category?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>