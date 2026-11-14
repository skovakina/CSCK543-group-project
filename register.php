<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Register';
$name = '';
$email = '';
$errors = [];
$is_valid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post_value('name'));
    $email = trim(post_value('email'));

    $errors = validate_registration($name, $email, post_value('password'), post_value('confirm_password'));

    // TODO: hash the password and save the user once the users table is merged.
    $is_valid = empty($errors);
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <p class="notice notice-error" role="alert">Please correct the errors below.</p>
<?php elseif ($is_valid): ?>
    <p class="notice" role="status">Your details are valid, but accounts cannot be saved until the database is connected.</p>
<?php endif; ?>

<div class="form-panel">
    <h1>Register</h1>

    <form id="register-form" method="post" action="register.php" data-validate>
        <?php
        form_field('name', 'Name', 'text', 'name', $name, $errors);
        form_field('email', 'Email address', 'email', 'email', $email, $errors);
        form_field('password', 'Password', 'password', 'new-password', '', $errors, 'Use at least 8 characters.');
        form_field('confirm_password', 'Confirm password', 'password', 'new-password', '', $errors);
        ?>
        <button class="btn" type="submit">Create account</button>
    </form>

    <p class="form-link">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
