<?php
/**
 * CSCK543 Networks and Web Technology - Group Project
 * Application Entry Point & Database Connection Verification (Issues #1, #2, #3)
 *
 * Author: Lolwa Al-Kuwari
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
$driver = (string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

// Fetch table record counts (Issue #2 verification)
$tables = [
    'users'              => 'Registered users (name, email, password hash, dietary preferences)',
    'recipes'            => 'Recipe records (title, prep/cook time, servings, difficulty, nutrition)',
    'categories'         => 'Course, Dietary, and Style categories',
    'recipe_categories'  => 'Many-to-many links between recipes and categories',
    'recipe_ingredients' => 'Ingredients with numeric & display quantities and units',
    'recipe_steps'       => 'Ordered recipe steps with duration in minutes per step',
    'user_favourites'    => 'Users\' saved favourite recipes',
    'recipe_ratings'     => 'Recipe ratings (taste, aesthetics, difficulty, overall)',
];

$tableCounts = [];
foreach (array_keys($tables) as $tableName) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
    $tableCounts[$tableName] = (int)$stmt->fetchColumn();
}

// Fetch populated BBC Food sample recipes with categories, ingredient counts, and step durations (Issue #3 verification)
$recipesSql = "
    SELECT
        r.recipe_id,
        r.title,
        r.difficulty,
        r.prep_time_mins,
        r.cook_time_mins,
        r.total_time_mins,
        r.servings,
        r.bbc_url,
        (SELECT COUNT(*) FROM recipe_ingredients ri WHERE ri.recipe_id = r.recipe_id) AS ingredient_count,
        (SELECT COUNT(*) FROM recipe_steps rs WHERE rs.recipe_id = r.recipe_id) AS step_count,
        (SELECT COALESCE(SUM(rs.duration_minutes), 0) FROM recipe_steps rs WHERE rs.recipe_id = r.recipe_id) AS total_step_mins
    FROM recipes r
    ORDER BY r.recipe_id ASC
";
$recipes = $pdo->query($recipesSql)->fetchAll();

// Fetch categories per recipe
$catStmt = $pdo->query("
    SELECT rc.recipe_id, c.name
    FROM recipe_categories rc
    INNER JOIN categories c ON c.category_id = rc.category_id
    ORDER BY c.name ASC
");
$recipeCategories = [];
foreach ($catStmt->fetchAll() as $row) {
    $recipeCategories[(int)$row['recipe_id']][] = (string)$row['name'];
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSCK543 Recipe Web Application — Database &amp; PHP Setup</title>
    <style>
        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --accent: #0f766e;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            padding: 2rem 1rem;
        }
        main {
            max-width: 1100px;
            margin: 0 auto;
        }
        header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        h1 { margin: 0 0 0.5rem 0; font-size: 1.6rem; }
        h2 { font-size: 1.25rem; margin-top: 0; }
        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.925rem;
        }
        th, td {
            text-align: left;
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
        }
        .tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin: 0.1rem;
        }
        a { color: var(--accent); }
    </style>
</head>
<body>
<main>
    <header>
        <h1>CSCK543 Recipe Web Application — Foundation Setup</h1>
        <p style="margin: 0.25rem 0 0.75rem; color: var(--muted);">
            Issues #1, #2, and #3: PHP 7/8 + XAMPP MySQL Connection, 3NF Relational Schema, and Sample BBC Food Recipes
        </p>
        <span class="status-badge">
            Connected via PDO (<?= e(strtoupper($driver)) ?>) &bull; PHP <?= e(PHP_VERSION) ?>
        </span>
    </header>

    <section class="card" aria-labelledby="schema-heading">
        <h2 id="schema-heading">1. Relational Database Schema Summary (Issue #2)</h2>
        <table>
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Purpose / Stored Data</th>
                    <th>Records Loaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $tableName => $description): ?>
                    <tr>
                        <td><code><?= e($tableName) ?></code></td>
                        <td><?= e($description) ?></td>
                        <td><strong><?= $tableCounts[$tableName] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card" aria-labelledby="recipes-heading">
        <h2 id="recipes-heading">2. Populated Sample Recipes from BBC Food (Issue #3)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Recipe Title</th>
                    <th>Categories</th>
                    <th>Difficulty</th>
                    <th>Ingredients</th>
                    <th>Ordered Steps (Step Time Sum)</th>
                    <th>BBC Food Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipes as $recipe): ?>
                    <?php
                    $rid = (int)$recipe['recipe_id'];
                    $cats = $recipeCategories[$rid] ?? [];
                    ?>
                    <tr>
                        <td><?= $rid ?></td>
                        <td><strong><?= e((string)$recipe['title']) ?></strong></td>
                        <td>
                            <?php foreach ($cats as $catName): ?>
                                <span class="tag"><?= e($catName) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?= e((string)$recipe['difficulty']) ?></td>
                        <td><?= (int)$recipe['ingredient_count'] ?> items</td>
                        <td><?= (int)$recipe['step_count'] ?> steps (<?= (int)$recipe['total_step_mins'] ?> mins)</td>
                        <td><a href="<?= e((string)$recipe['bbc_url']) ?>" target="_blank" rel="noopener">BBC Food</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
