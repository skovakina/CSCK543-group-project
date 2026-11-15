<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = current_user_id();
if ($user_id === null) {
    set_flash_notice('Log in to view your saved recipes.');
    redirect_to('login.php');
}

$user = false;
$recipes = [];
$error = '';
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT full_name, email FROM users WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare(
            'SELECT r.recipe_id, r.title, r.image_path, r.total_time_mins
             FROM recipes r JOIN user_favourites uf ON uf.recipe_id = r.recipe_id
             WHERE uf.user_id = :user_id ORDER BY r.title'
        );
        $stmt->execute([':user_id' => $user_id]);
        $recipes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log('Account query failed (SQLSTATE ' . $e->getCode() . ').');
    http_response_code(500);
    $error = 'We could not load your account. Please try again.';
}

if (!$user && $error === '') {
    http_response_code(404);
    $error = 'This account does not exist.';
}

$saved_recipes = array_fill_keys(array_column($recipes, 'recipe_id'), true);
$return_to = 'account.php';
$page_title = 'My account';
require __DIR__ . '/includes/header.php';
?>

<?php if ($error !== ''): ?>
    <h1>My account</h1>
    <p class="notice notice-error" role="alert"><?= e($error) ?></p>
<?php else: ?>
    <header class="account-header">
        <h1><?= e($user['full_name']) ?></h1>
        <p><?= e($user['email']) ?></p>
    </header>

    <h2>Saved recipes</h2>
    <?php if (empty($recipes)): ?>
        <p>You haven't saved any recipes yet. <a href="index.php">Browse recipes</a></p>
    <?php else: ?>
        <?php require __DIR__ . '/includes/recipe-grid.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
