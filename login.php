<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Log in';
$email = '';
$errors = [];
$form_error = '';
$csrf_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !has_valid_csrf_token()) {
    http_response_code(403);
    $csrf_error = 'This form could not be verified. Reload the page and try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = normalize_email(post_value('email'));
    $password = post_value('password');

    $errors = validate_login($email, $password);

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare(
                'SELECT user_id, full_name, email, password_hash FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, (string)$user['password_hash'])) {
                login_user_session($user);
                set_flash_notice('Welcome back, ' . (string)$user['full_name'] . '!');
                redirect_to('index.php');
            }

            $form_error = 'Incorrect email address or password.';
        } catch (PDOException $e) {
            error_log('Login query failed (SQLSTATE ' . $e->getCode() . ').');
            $form_error = 'We could not log you in right now. Please try again.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <p class="notice notice-error" role="alert">Please correct the errors below.</p>
<?php elseif ($csrf_error !== ''): ?>
    <p class="notice notice-error" role="alert"><?= e($csrf_error) ?></p>
<?php elseif ($form_error !== ''): ?>
    <p class="notice notice-error" role="alert"><?= e($form_error) ?></p>
<?php elseif ($is_logged_in): ?>
    <p class="notice" role="status">
        You are currently logged in as <?= e(current_user_name()) ?>.
        Use the Log out button in the main menu when you are finished.
    </p>
<?php endif; ?>

<div class="form-panel">
    <h1>Log in</h1>

    <form id="login-form" method="post" action="login.php" data-validate>
        <?php csrf_field(); ?>
        <?php
        form_field('email', 'Email address', 'email', 'email', $email, $errors);
        form_field('password', 'Password', 'password', 'current-password', '', $errors);
        ?>
        <button class="btn" type="submit">Log in</button>
    </form>

    <p class="form-link">New here? <a href="register.php">Create an account</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
