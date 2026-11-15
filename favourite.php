<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    die('Use the heart button to save a recipe.');
}

$user_id = current_user_id();
if ($user_id === null) {
    set_flash_notice('Log in to save recipes.');
    redirect_to('login.php');
}

if (!isset($_SESSION['favourite_token']) || !hash_equals($_SESSION['favourite_token'], post_value('token'))) {
    http_response_code(403);
    die('Invalid request. Reload the homepage and try again.');
}

$recipe_id = filter_var(post_value('recipe_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($recipe_id === false) {
    http_response_code(400);
    die('Choose a valid recipe.');
}

$action = post_value('action');
if ($action !== 'save' && $action !== 'remove') {
    http_response_code(400);
    die('Choose whether to save or remove the recipe.');
}

$return_to = post_value('return_to');
if ($return_to === '') {
    $return_to = 'index.php';
}
if ($return_to !== 'index.php' && $return_to !== 'account.php') {
    http_response_code(400);
    die('Invalid return page.');
}

try {
    $sql = $action === 'remove'
        ? 'DELETE FROM user_favourites WHERE user_id = :user_id AND recipe_id = :recipe_id'
        : 'INSERT INTO user_favourites (user_id, recipe_id) VALUES (:user_id, :recipe_id)
           ON DUPLICATE KEY UPDATE user_id = user_id';
    $stmt = getDbConnection()->prepare($sql);
    $stmt->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
} catch (PDOException $e) {
    error_log('Updating favourite failed (SQLSTATE ' . $e->getCode() . ').');
    set_flash_notice('We could not update your favourites. Please try again.');
}

redirect_to($return_to);
