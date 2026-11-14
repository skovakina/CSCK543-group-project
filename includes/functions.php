<?php
// Shared helper and validation functions.

// Makes text safe to print in HTML.
function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
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
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or fewer.';
    }

    if (email_error($email) !== '') {
        $errors['email'] = email_error($email);
    }

    if ($password === '') {
        $errors['password'] = 'Enter a password.';
    } elseif (strlen($password) < 8) {
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

    if (email_error($email) !== '') {
        $errors['email'] = email_error($email);
    }

    if ($password === '') {
        $errors['password'] = 'Enter your password.';
    }

    return $errors;
}
