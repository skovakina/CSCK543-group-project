<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$recipe = false;
$error = '';

if ($id !== false) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            'SELECT title, description, image_path, total_time_mins, servings FROM recipes WHERE recipe_id = :id'
        );
        $stmt->execute([':id' => $id]);
        $recipe = $stmt->fetch();

        if ($recipe) {
            $stmt = $pdo->prepare(
                'SELECT c.name FROM categories c
                 JOIN recipe_categories rc ON rc.category_id = c.category_id
                 WHERE rc.recipe_id = :id ORDER BY c.name'
            );
            $stmt->execute([':id' => $id]);
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $pdo->prepare(
                'SELECT ingredient_name, quantity_display FROM recipe_ingredients
                 WHERE recipe_id = :id ORDER BY sort_order, ingredient_id'
            );
            $stmt->execute([':id' => $id]);
            $ingredients = $stmt->fetchAll();

            $stmt = $pdo->prepare(
                'SELECT step_number, stage_title, instruction, duration_minutes FROM recipe_steps
                 WHERE recipe_id = :id ORDER BY step_number'
            );
            $stmt->execute([':id' => $id]);
            $steps = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Recipe query failed (SQLSTATE ' . $e->getCode() . ').');
        http_response_code(500);
        $error = 'We could not load this recipe. Please try again.';
    }
}

if ($error !== '') {
    $page_title = 'Recipe unavailable';
} elseif (!$recipe) {
    http_response_code(404);
    $page_title = 'Recipe not found';
    $error = 'This recipe does not exist.';
} else {
    $page_title = $recipe['title'];
}

require __DIR__ . '/includes/header.php';
?>

<p><a href="index.php">Back to recipes</a></p>

<?php if ($error !== ''): ?>
    <h1><?= e($page_title) ?></h1>
    <p class="notice notice-error" role="alert"><?= e($error) ?></p>
<?php else: ?>
    <article class="recipe-detail">
        <h1><?= e($recipe['title']) ?></h1>
        <img src="<?= e($recipe['image_path']) ?>" alt="">
        <p><?= e($recipe['description']) ?></p>
        <p><?= (int)$recipe['total_time_mins'] ?> minutes &middot; Serves <?= (int)$recipe['servings'] ?></p>
        <p><strong>Categories:</strong> <?= e(implode(', ', $categories)) ?></p>

        <h2>Ingredients</h2>
        <ul>
            <?php foreach ($ingredients as $ingredient): ?>
                <li><?= e($ingredient['quantity_display'] . ' ' . $ingredient['ingredient_name']) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Method</h2>
        <ol>
            <?php foreach ($steps as $step): ?>
                <li value="<?= (int)$step['step_number'] ?>">
                    <h3><?= e($step['stage_title']) ?></h3>
                    <p class="hint"><?= (int)$step['duration_minutes'] ?> minutes</p>
                    <p><?= e($step['instruction']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </article>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
