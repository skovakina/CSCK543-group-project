<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Log in';
$email = '';
$errors = [];
$is_valid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(post_value('email'));

    $errors = validate_login($email, post_value('password'));

    // TODO: check the email and password once the users table is merged.
    $is_valid = empty($errors);
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <p class="notice notice-error" role="alert">Please correct the errors below.</p>
<?php elseif ($is_valid): ?>
    <p class="notice" role="status">Your details are valid, but you cannot log in until the database is connected.</p>
<?php endif; ?>

<div class="form-panel">
    <h1>Log in</h1>

    <form id="login-form" method="post" action="login.php" data-validate>
        <?php
        form_field('email', 'Email address', 'email', 'email', $email, $errors);
        form_field('password', 'Password', 'password', 'current-password', '', $errors);
        ?>
        <button class="btn" type="submit">Log in</button>
    </form>

    <p class="form-link">New here? <a href="register.php">Create an account</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
