<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$recipes = $pdo->query(
    'SELECT r.recipe_id, r.title, r.image_path, r.total_time_mins,
            (SELECT AVG(overall_rating) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS avg_rating,
            (SELECT COUNT(*) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS rating_count
     FROM recipes r ORDER BY r.title'
)->fetchAll();

$user_id = current_user_id();
$saved_recipes = [];
if ($user_id !== null) {
    try {
        $stmt = $pdo->prepare('SELECT recipe_id FROM user_favourites WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $saved_recipes = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
    } catch (PDOException $e) {
        error_log('Favourites query failed (SQLSTATE ' . $e->getCode() . ').');
        http_response_code(500);
        die('We could not load your saved recipes. Please try again.');
    }
}

$return_to = 'index.php';
$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>

<h1><?= e($site_name) ?></h1>
<p>Find inspiration for your next meal.</p>

<?php if (empty($recipes)): ?>
    <p>No recipes available yet.</p>
<?php else: ?>
    <?php require __DIR__ . '/includes/recipe-grid.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
