<?php
// Shared helper and validation functions.

// Makes text safe to print in HTML.
function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Counts Unicode characters rather than UTF-8 bytes.
function char_length($text)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }

    $count = preg_match_all('/./us', $text, $matches);
    return $count === false ? strlen($text) : $count;
}

// Trims and lowercases an email address safely.
function normalize_email($email)
{
    $trimmed = trim($email);
    return function_exists('mb_strtolower') ? mb_strtolower($trimmed, 'UTF-8') : strtolower($trimmed);
}

// Ensures a PHP session is active before reading or writing $_SESSION.
function ensure_session_started()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Returns the signed-in user's ID, or null if not logged in.
function current_user_id()
{
    ensure_session_started();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

// Returns the signed-in user's display name, or an empty string.
function current_user_name()
{
    ensure_session_started();
    return isset($_SESSION['user_name']) && is_string($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

// Stores a one-time status message in the session for the next request.
function set_flash_notice($message)
{
    ensure_session_started();
    $_SESSION['flash_notice'] = $message;
}

// Reads and clears any pending one-time status message.
function consume_flash_notice()
{
    ensure_session_started();
    if (!isset($_SESSION['flash_notice']) || !is_string($_SESSION['flash_notice'])) {
        return '';
    }
    $message = $_SESSION['flash_notice'];
    unset($_SESSION['flash_notice']);
    return $message;
}

// Establishes an authenticated session and rotates the session ID.
function login_user_session(array $user)
{
    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['user_name'] = (string)$user['full_name'];
    $_SESSION['user_email'] = (string)$user['email'];
}

// Detects MySQL/PDO unique constraint violations (SQLSTATE 23000 / 1062).
function is_duplicate_key_exception(PDOException $e)
{
    if ($e->getCode() === '23000') {
        return true;
    }
    return isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062;
}

// Redirects to a relative page and stops script execution.
function redirect_to($location)
{
    header('Location: ' . $location);
    exit;
}

// Gets a submitted form value as a string.
function post_value($name)
{
    return isset($_POST[$name]) && is_string($_POST[$name]) ? $_POST[$name] : '';
}

// Prints a labelled input with space for a hint and an error.
function form_field($name, $label, $type, $autocomplete, $value = '', $errors = [], $hint = '')
{
    $error = isset($errors[$name]) ? $errors[$name] : '';
    $described_by = $name . '-error';

    echo '<div class="field">';
    echo '<label for="' . e($name) . '">' . e($label) . '</label>';

    if ($hint !== '') {
        echo '<p class="hint" id="' . e($name) . '-hint">' . e($hint) . '</p>';
        $described_by = $name . '-hint ' . $described_by;
    }

    echo '<input type="' . e($type) . '" id="' . e($name) . '" name="' . e($name) . '"';
    echo ' value="' . e($value) . '" autocomplete="' . e($autocomplete) . '" required';
    echo ' aria-describedby="' . e($described_by) . '"';
    echo $error !== '' ? ' aria-invalid="true">' : '>';

    echo '<p class="field-error" id="' . e($name) . '-error">' . e($error) . '</p>';
    echo '</div>';
}

// Checks the email field and returns an error message, or an empty string.
function email_error($email)
{
    if ($email === '') {
        return 'Enter your email address.';
    }
    if (char_length($email) > 150) {
        return 'Email address must be 150 characters or fewer.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }
    return '';
}

// Checks the registration form and returns any errors.
function validate_registration($name, $email, $password, $confirm_password)
{
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Enter your name.';
    } elseif (char_length($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or fewer.';
    }

    $email_msg = email_error($email);
    if ($email_msg !== '') {
        $errors['email'] = $email_msg;
    }

    if ($password === '') {
        $errors['password'] = 'Enter a password.';
    } elseif (char_length($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($confirm_password === '') {
        $errors['confirm_password'] = 'Confirm your password.';
    } elseif ($confirm_password !== $password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    return $errors;
}

// Checks the login form and returns any errors.
function validate_login($email, $password)
{
    $errors = [];

    $email_msg = email_error($email);
    if ($email_msg !== '') {
        $errors['email'] = $email_msg;
    }

    if ($password === '') {
        $errors['password'] = 'Enter your password.';
    }

    return $errors;
}
