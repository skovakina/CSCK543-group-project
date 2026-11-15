<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
$recipes = getDbConnection()->query(
    'SELECT recipe_id, title, image_path, total_time_mins FROM recipes ORDER BY title'
)->fetchAll();

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>

<h1><?= e($site_name) ?></h1>
<p>Find inspiration for your next meal.</p>

<?php if (empty($recipes)): ?>
    <p>No recipes available yet.</p>
<?php else: ?>
    <div class="recipe-grid">
        <?php foreach ($recipes as $recipe): ?>
            <article class="recipe-card">
                <img src="<?= e($recipe['image_path']) ?>" alt="" loading="lazy">
                <h2><a href="recipe.php?id=<?= (int)$recipe['recipe_id'] ?>"><?= e($recipe['title']) ?></a></h2>
                <p><?= (int)$recipe['total_time_mins'] ?> minutes</p>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
