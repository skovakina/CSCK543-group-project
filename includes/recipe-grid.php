<?php
if (!isset($_SESSION['favourite_token'])) {
    $_SESSION['favourite_token'] = bin2hex(random_bytes(32));
}
?>

<div class="recipe-grid">
    <?php foreach ($recipes as $recipe): ?>
        <?php $is_saved = isset($saved_recipes[$recipe['recipe_id']]); ?>
        <article class="recipe-card">
            <img src="<?= e($recipe['image_path']) ?>" alt="" loading="lazy">
            <form method="post" action="favourite.php">
                <input type="hidden" name="recipe_id" value="<?= (int)$recipe['recipe_id'] ?>">
                <input type="hidden" name="token" value="<?= e($_SESSION['favourite_token']) ?>">
                <input type="hidden" name="action" value="<?= $is_saved ? 'remove' : 'save' ?>">
                <input type="hidden" name="return_to" value="<?= e($return_to) ?>">
                <button class="favourite-button" type="submit"
                    aria-label="<?= e(($is_saved ? 'Remove from favourites: ' : 'Save to favourites: ') . $recipe['title']) ?>"
                    aria-pressed="<?= $is_saved ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M12 20.5C9.5 18.2 3 13.7 3 8.5A5 5 0 0 1 12 5.5A5 5 0 0 1 21 8.5C21 13.7 14.5 18.2 12 20.5Z"/>
                    </svg>
                </button>
            </form>
            <h2><a href="recipe.php?id=<?= (int)$recipe['recipe_id'] ?>"><?= e($recipe['title']) ?></a></h2>
            <p><?= (int)$recipe['total_time_mins'] ?> minutes</p>
        </article>
    <?php endforeach; ?>
</div>
