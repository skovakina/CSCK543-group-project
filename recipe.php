<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$recipe = false;
$categories = [];
$ingredients = [];
$steps = [];
$reviews = [];
$user_rating = false;
$rating_summary = ['average_rating' => null, 'rating_count' => 0];
$error = '';
$form_error = '';
$rating_errors = [];
$rating_values = [
    'overall_rating' => '',
    'taste_rating' => '',
    'aesthetics_rating' => '',
    'difficulty_rating' => '',
    'review_title' => '',
    'review_comment' => '',
];

if ($id !== false) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            'SELECT recipe_id, title, description, image_path, total_time_mins, servings, bbc_url
             FROM recipes WHERE recipe_id = :id'
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

            // Validate the review submission before writing user-provided text.
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = current_user_id();
                if ($user_id === null) {
                    http_response_code(403);
                    $form_error = 'Log in to add or update a rating and review.';
                } elseif (!has_valid_csrf_token()) {
                    http_response_code(403);
                    $form_error = 'This form could not be verified. Reload the page and try again.';
                } else {
                    $rating_fields = [
                        'overall_rating' => 'an overall rating',
                        'taste_rating' => 'a taste rating',
                        'aesthetics_rating' => 'an appearance rating',
                        'difficulty_rating' => 'a difficulty rating',
                    ];

                    foreach ($rating_fields as $field => $label) {
                        $rating_values[$field] = post_value($field);
                        $score = filter_var($rating_values[$field], FILTER_VALIDATE_INT, [
                            'options' => ['min_range' => 1, 'max_range' => 5],
                        ]);
                        if ($score === false) {
                            $rating_errors[$field] = 'Choose ' . $label . ' from 1 to 5.';
                        }
                    }

                    $rating_values['review_title'] = trim(post_value('review_title'));
                    $rating_values['review_comment'] = trim(post_value('review_comment'));

                    if (char_length($rating_values['review_title']) > 120) {
                        $rating_errors['review_title'] = 'Keep the title to 120 characters or fewer.';
                    }

                    if (char_length($rating_values['review_comment']) > 3000) {
                        $rating_errors['review_comment'] = 'Keep the review to 3,000 characters or fewer.';
                    }

                    if (empty($rating_errors)) {
                        $stmt = $pdo->prepare(
                            'INSERT INTO recipe_ratings
                                (recipe_id, user_id, taste_rating, aesthetics_rating, difficulty_rating,
                                 overall_rating, review_title, review_comment)
                             VALUES
                                (:recipe_id, :user_id, :taste_rating, :aesthetics_rating, :difficulty_rating,
                                 :overall_rating, :review_title, :review_comment)
                             ON DUPLICATE KEY UPDATE
                                taste_rating = VALUES(taste_rating),
                                aesthetics_rating = VALUES(aesthetics_rating),
                                difficulty_rating = VALUES(difficulty_rating),
                                overall_rating = VALUES(overall_rating),
                                review_title = VALUES(review_title),
                                review_comment = VALUES(review_comment)'
                        );
                        $stmt->execute([
                            ':recipe_id' => (int)$recipe['recipe_id'],
                            ':user_id' => $user_id,
                            ':taste_rating' => (int)$rating_values['taste_rating'],
                            ':aesthetics_rating' => (int)$rating_values['aesthetics_rating'],
                            ':difficulty_rating' => (int)$rating_values['difficulty_rating'],
                            ':overall_rating' => (int)$rating_values['overall_rating'],
                            ':review_title' => $rating_values['review_title'],
                            ':review_comment' => $rating_values['review_comment'],
                        ]);

                        $saved_message = $rating_values['review_title'] !== '' || $rating_values['review_comment'] !== ''
                            ? 'Your rating and review have been saved.'
                            : 'Your rating has been saved.';
                        set_flash_notice($saved_message);
                        redirect_to('recipe.php?id=' . (int)$recipe['recipe_id']);
                    }
                }
            }

            $stmt = $pdo->prepare(
                'SELECT AVG(overall_rating) AS average_rating, COUNT(*) AS rating_count
                 FROM recipe_ratings WHERE recipe_id = :id'
            );
            $stmt->execute([':id' => $id]);
            $rating_summary = $stmt->fetch();

            $stmt = $pdo->prepare(
                'SELECT rr.overall_rating, rr.taste_rating, rr.aesthetics_rating, rr.difficulty_rating,
                        rr.review_title, rr.review_comment, rr.created_at, u.full_name
                 FROM recipe_ratings rr
                 JOIN users u ON u.user_id = rr.user_id
                 WHERE rr.recipe_id = :id AND (rr.review_title <> \'\' OR rr.review_comment <> \'\')
                 ORDER BY rr.created_at DESC, rr.rating_id DESC'
            );
            $stmt->execute([':id' => $id]);
            $reviews = $stmt->fetchAll();

            $user_id = current_user_id();
            if ($user_id !== null) {
                $stmt = $pdo->prepare(
                    'SELECT overall_rating, taste_rating, aesthetics_rating, difficulty_rating,
                            review_title, review_comment
                     FROM recipe_ratings WHERE recipe_id = :recipe_id AND user_id = :user_id'
                );
                $stmt->execute([':recipe_id' => $id, ':user_id' => $user_id]);
                $user_rating = $stmt->fetch();

                if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $user_rating) {
                    foreach ($rating_values as $field => $value) {
                        $rating_values[$field] = (string)$user_rating[$field];
                    }
                    // Existing seed data may use a decimal average; editing uses whole stars.
                    $rating_values['overall_rating'] = (string)(int)round((float)$user_rating['overall_rating']);
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Recipe page query failed (SQLSTATE ' . $e->getCode() . ').');
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
        <img src="<?= e($recipe['image_path']) ?>" alt="Finished <?= e($recipe['title']) ?>">
        <p><?= e($recipe['description']) ?></p>
        <p><?= (int)$recipe['total_time_mins'] ?> minutes &middot; Serves <?= (int)$recipe['servings'] ?></p>
        <p><strong>Categories:</strong> <?= e(implode(', ', $categories)) ?></p>

        <?php if (is_string($recipe['bbc_url']) && preg_match('#^https://www\.bbc\.co\.uk/food/#', $recipe['bbc_url'])): ?>
            <p class="recipe-source">
                Recipe source:
                <a href="<?= e($recipe['bbc_url']) ?>" target="_blank" rel="noopener noreferrer">
                    View the original BBC Food recipe <span class="visually-hidden">(opens in a new tab)</span>
                </a>
            </p>
        <?php endif; ?>

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

        <section class="reviews-section" aria-labelledby="reviews-heading">
            <h2 id="reviews-heading">Ratings and reviews</h2>

            <p class="rating-summary">
                <strong>Average rating:</strong>
                <?php if ((int)$rating_summary['rating_count'] > 0): ?>
                    <?= number_format((float)$rating_summary['average_rating'], 1) ?> out of 5
                    from <?= (int)$rating_summary['rating_count'] ?>
                    <?= (int)$rating_summary['rating_count'] === 1 ? 'rating' : 'ratings' ?>
                <?php else: ?>
                    No ratings yet
                <?php endif; ?>
            </p>

            <?php if (empty($reviews)): ?>
                <p>No written reviews yet.</p>
            <?php else: ?>
                <ul class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <?php $review_timestamp = strtotime((string)$review['created_at']); ?>
                        <li class="review-card">
                            <?php if ($review['review_title'] !== ''): ?>
                                <h3><?= e($review['review_title']) ?></h3>
                            <?php endif; ?>
                            <p class="review-byline">
                                <?= e($review['full_name']) ?>
                                <?php if ($review_timestamp !== false): ?>
                                    &middot; <time datetime="<?= e(date('Y-m-d', $review_timestamp)) ?>"><?= e(date('j M Y', $review_timestamp)) ?></time>
                                <?php endif; ?>
                            </p>
                            <p class="review-score">
                                Overall <?= e(number_format((float)$review['overall_rating'], 1)) ?>/5
                                &middot; Taste <?= (int)$review['taste_rating'] ?>/5
                                &middot; Appearance <?= (int)$review['aesthetics_rating'] ?>/5
                                &middot; Difficulty <?= (int)$review['difficulty_rating'] ?>/5
                            </p>
                            <p><?= nl2br(e($review['review_comment'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="review-form-panel">
                <h3><?= $user_rating ? 'Update your rating' : 'Rate this recipe' ?></h3>
                <?php if (current_user_id() === null): ?>
                    <p><a href="login.php">Log in</a> to rate this recipe and write a review.</p>
                <?php else: ?>
                    <p class="hint">Choose a score for each category. The written review is optional.</p>
                    <?php if ($form_error !== ''): ?>
                        <p class="notice notice-error" role="alert"><?= e($form_error) ?></p>
                    <?php elseif (!empty($rating_errors)): ?>
                        <p class="notice notice-error" role="alert">Please correct the rating and review fields below.</p>
                    <?php endif; ?>

                    <form method="post" action="recipe.php?id=<?= (int)$recipe['recipe_id'] ?>">
                        <?php csrf_field(); ?>

                        <?php
                        $rating_labels = [
                            'overall_rating' => 'Overall rating',
                            'taste_rating' => 'Taste',
                            'aesthetics_rating' => 'Appearance',
                            'difficulty_rating' => 'Difficulty',
                        ];
                        foreach ($rating_labels as $field => $label):
                            $field_error = isset($rating_errors[$field]) ? $rating_errors[$field] : '';
                        ?>
                            <fieldset class="rating-options" aria-describedby="<?= e($field) ?>-hint <?= e($field) ?>-error"<?= $field_error !== '' ? ' aria-invalid="true"' : '' ?>>
                                <legend><?= e($label) ?> <span class="required-label">(required)</span></legend>
                                <p class="hint" id="<?= e($field) ?>-hint">Choose 1 (lowest) to 5 (highest).</p>
                                <div class="rating-choices">
                                    <?php for ($score = 1; $score <= 5; $score++): ?>
                                        <?php $choice_id = $field . '_' . $score; ?>
                                        <label class="rating-choice" for="<?= e($choice_id) ?>">
                                            <input id="<?= e($choice_id) ?>" type="radio" name="<?= e($field) ?>" value="<?= $score ?>"<?= $rating_values[$field] === (string)$score ? ' checked' : '' ?><?= $score === 1 ? ' required' : '' ?>>
                                            <span><?= $score ?></span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <p class="field-error" id="<?= e($field) ?>-error" role="alert"><?= e($field_error) ?></p>
                            </fieldset>
                        <?php endforeach; ?>

                        <?php
                        form_field(
                            'review_title',
                            'Review title',
                            'text',
                            'off',
                            $rating_values['review_title'],
                            $rating_errors,
                            '',
                            false,
                            'maxlength="120"'
                        );
                        ?>
                        <div class="field">
                            <label for="review_comment">Your review</label>
                            <p class="hint" id="review_comment-hint">Optional. Share what worked well or what you would change. Maximum 3,000 characters.</p>
                            <textarea id="review_comment" name="review_comment" rows="5" maxlength="3000" aria-describedby="review_comment-hint review_comment-error"<?= isset($rating_errors['review_comment']) ? ' aria-invalid="true"' : '' ?>><?= e($rating_values['review_comment']) ?></textarea>
                            <p class="field-error" id="review_comment-error" role="alert"><?= isset($rating_errors['review_comment']) ? e($rating_errors['review_comment']) : '' ?></p>
                        </div>

                        <button class="btn" type="submit">Save review</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </article>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
