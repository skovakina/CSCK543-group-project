<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Allowed choices. Only these values are used in the SQL.
$diet_columns = [
    'vegetarian' => 'is_vegetarian',
    'vegan' => 'is_vegan',
    'gluten_free' => 'is_gluten_free',
    'dairy_free' => 'is_dairy_free',
];
$diet_labels = [
    'vegetarian' => 'Vegetarian',
    'vegan' => 'Vegan',
    'gluten_free' => 'Gluten free',
    'dairy_free' => 'Dairy free',
];
$difficulty_options = ['' => 'Any', 'Easy' => 'Easy', 'Medium' => 'Medium', 'Hard' => 'Hard'];
$rating_options = ['' => 'Any', '3' => '3 stars and up', '4' => '4 stars and up', '4.5' => '4.5 stars and up'];
$sort_labels = [
    'title' => 'Title (A to Z)',
    'time' => 'Quickest first',
    'calories' => 'Fewest calories',
    'difficulty' => 'Easiest first',
    'rating' => 'Highest rated',
    'popular' => 'Most saved',
];
$sort_orders = [
    'title' => 'r.title',
    'time' => 'r.total_time_mins, r.title',
    'calories' => 'r.calories_per_serving, r.title',
    'difficulty' => 'r.difficulty, r.title',
    'rating' => 'avg_rating IS NULL, avg_rating DESC, r.title',
    'popular' => 'save_count DESC, r.title',
];

// Read the form. Anything not in the lists above is ignored.
$q = trim(query_value('q'));
$exclude = trim(query_value('exclude'));
$max_time = trim(query_value('max_time'));
$max_calories = trim(query_value('max_calories'));
$category = query_value('category');
$difficulty = isset($difficulty_options[query_value('difficulty')]) ? query_value('difficulty') : '';
$min_rating = isset($rating_options[query_value('min_rating')]) ? query_value('min_rating') : '';
$sort = isset($sort_orders[query_value('sort')]) ? query_value('sort') : 'title';

$diets = [];
$selected_diets = isset($_GET['diet']) && is_array($_GET['diet']) ? $_GET['diet'] : [];
foreach ($diet_columns as $diet => $column) {
    if (in_array($diet, $selected_diets, true)) {
        $diets[] = $diet;
    }
}

$errors = [];
if (char_length($q) > 100) {
    $errors['q'] = 'Keep this to 100 characters or fewer.';
}
if (char_length($exclude) > 100) {
    $errors['exclude'] = 'Keep this to 100 characters or fewer.';
}
$time_error = whole_number_error($max_time, 1, 600);
if ($time_error !== '') {
    $errors['max_time'] = $time_error;
}
$calories_error = whole_number_error($max_calories, 1, 2000);
if ($calories_error !== '') {
    $errors['max_calories'] = $calories_error;
}

$category_options = ['' => 'Any'];
$recipes = [];
$saved_recipes = [];
$db_error = '';

try {
    $pdo = getDbConnection();

    $categories = $pdo->query('SELECT slug, name FROM categories ORDER BY name')->fetchAll();
    foreach ($categories as $row) {
        $category_options[$row['slug']] = $row['name'];
    }
    if (!isset($category_options[$category])) {
        $category = '';
    }

    if (empty($errors)) {
        $where = [];
        $params = [];

        if ($q !== '') {
            // Each placeholder can only be used once, so the keyword has three.
            $where[] = '(r.title LIKE :q1 OR r.description LIKE :q2 OR EXISTS (
                SELECT 1 FROM recipe_ingredients ri WHERE ri.recipe_id = r.recipe_id AND ri.ingredient_name LIKE :q3))';
            $params[':q1'] = like_pattern($q);
            $params[':q2'] = like_pattern($q);
            $params[':q3'] = like_pattern($q);
        }
        if ($exclude !== '') {
            $where[] = 'NOT EXISTS (
                SELECT 1 FROM recipe_ingredients ri WHERE ri.recipe_id = r.recipe_id AND ri.ingredient_name LIKE :exclude)';
            $params[':exclude'] = like_pattern($exclude);
        }
        if ($category !== '') {
            $where[] = 'EXISTS (
                SELECT 1 FROM recipe_categories rc JOIN categories c ON c.category_id = rc.category_id
                WHERE rc.recipe_id = r.recipe_id AND c.slug = :category)';
            $params[':category'] = $category;
        }
        if ($difficulty !== '') {
            $where[] = 'r.difficulty = :difficulty';
            $params[':difficulty'] = $difficulty;
        }
        foreach ($diets as $diet) {
            $where[] = 'r.' . $diet_columns[$diet] . ' = 1';
        }
        if ($max_time !== '') {
            $where[] = 'r.total_time_mins <= :max_time';
            $params[':max_time'] = (int)$max_time;
        }
        if ($max_calories !== '') {
            $where[] = 'r.calories_per_serving <= :max_calories';
            $params[':max_calories'] = (int)$max_calories;
        }
        if ($min_rating !== '') {
            $where[] = '(SELECT AVG(overall_rating) FROM recipe_ratings WHERE recipe_id = r.recipe_id) >= :min_rating';
            $params[':min_rating'] = $min_rating;
        }

        $sql = 'SELECT r.recipe_id, r.title, r.image_path, r.total_time_mins, r.difficulty, r.calories_per_serving,
                (SELECT AVG(overall_rating) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS avg_rating,
                (SELECT COUNT(*) FROM recipe_ratings WHERE recipe_id = r.recipe_id) AS rating_count,
                (SELECT COUNT(*) FROM user_favourites WHERE recipe_id = r.recipe_id) AS save_count
                FROM recipes r';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ' . $sort_orders[$sort];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll();
    }

    $user_id = current_user_id();
    if ($user_id !== null) {
        $stmt = $pdo->prepare('SELECT recipe_id FROM user_favourites WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
        $saved_recipes = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
    }
} catch (PDOException $e) {
    error_log('Search query failed (SQLSTATE ' . $e->getCode() . ').');
    http_response_code(500);
    $db_error = 'We could not search the recipes right now. Please try again.';
}

// The heart buttons bring people back to these same results.
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$return_to = $query_string === '' ? 'search.php' : 'search.php?' . $query_string;

$page_title = 'Search';
require __DIR__ . '/includes/header.php';
?>

<h1>Search recipes</h1>

<?php if (!empty($errors)): ?>
    <p class="notice notice-error" role="alert">Please correct the errors below.</p>
<?php elseif ($db_error !== ''): ?>
    <p class="notice notice-error" role="alert"><?= e($db_error) ?></p>
<?php endif; ?>

<form class="search-form" method="get" action="search.php" data-validate>
    <div class="search-fields">
        <?php
        form_field('q', 'Keyword', 'search', 'off', $q, $errors, 'Searches titles, descriptions and ingredients.', false);
        select_field('category', 'Category', $category_options, $category);
        select_field('difficulty', 'Difficulty', $difficulty_options, $difficulty);
        form_field('max_time', 'Maximum total time (minutes)', 'number', 'off', $max_time, $errors, '', false, 'min="1" max="600"');
        form_field('max_calories', 'Maximum calories per serving', 'number', 'off', $max_calories, $errors, '', false, 'min="1" max="2000"');
        select_field('min_rating', 'Rating', $rating_options, $min_rating);
        form_field('exclude', 'Leave out ingredient', 'text', 'off', $exclude, $errors, 'For example, mushrooms.', false);
        select_field('sort', 'Sort by', $sort_labels, $sort);
        ?>
    </div>

    <fieldset class="diet-options">
        <legend>Dietary needs</legend>
        <?php foreach ($diet_labels as $diet => $label): ?>
            <label class="checkbox">
                <input type="checkbox" name="diet[]" value="<?= e($diet) ?>"<?= in_array($diet, $diets, true) ? ' checked' : '' ?>>
                <?= e($label) ?>
            </label>
        <?php endforeach; ?>
    </fieldset>

    <div class="search-actions">
        <button class="btn" type="submit">Search</button>
        <a href="search.php">Clear filters</a>
    </div>
</form>

<?php if (empty($errors) && $db_error === ''): ?>
    <h2 class="results-heading"><?= count($recipes) ?> <?= count($recipes) === 1 ? 'recipe' : 'recipes' ?> found</h2>

    <?php if (empty($recipes)): ?>
        <p>No recipes match your search. Try removing a filter.</p>
    <?php else: ?>
        <?php require __DIR__ . '/includes/recipe-grid.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
