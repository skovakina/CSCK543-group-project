<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Register';
$name = '';
$email = '';
$errors = [];
$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post_value('name'));
    $email = normalize_email(post_value('email'));
    $password = post_value('password');
    $confirm_password = post_value('confirm_password');

    $errors = validate_registration($name, $email, $password, $confirm_password);

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $check_stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
            $check_stmt->execute([':email' => $email]);

            if ($check_stmt->fetch()) {
                $errors['email'] = 'An account with this email address already exists.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)'
                );
                $insert_stmt->execute([
                    ':full_name'     => $name,
                    ':email'         => $email,
                    ':password_hash' => $password_hash,
                ]);

                set_flash_notice('Your account has been created. You can now log in.');
                redirect_to('login.php');
            }
        } catch (PDOException $e) {
            if (is_duplicate_key_exception($e)) {
                $errors['email'] = 'An account with this email address already exists.';
            } else {
                error_log('Registration query failed (SQLSTATE ' . $e->getCode() . ').');
                $form_error = 'We could not create your account right now. Please try again.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <p class="notice notice-error" role="alert">Please correct the errors below.</p>
<?php elseif ($form_error !== ''): ?>
    <p class="notice notice-error" role="alert"><?= e($form_error) ?></p>
<?php elseif ($is_logged_in): ?>
    <p class="notice" role="status">
        You are currently logged in as <?= e(current_user_name()) ?>.
        <a href="logout.php">Log out</a>
    </p>
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
