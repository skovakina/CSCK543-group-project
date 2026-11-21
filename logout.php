<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    die('Use the Log out button in the main menu.');
}

if (!has_valid_csrf_token()) {
    http_response_code(403);
    die('This request could not be verified. Return to the page and try again.');
}

ensure_session_started();
$_SESSION = [];
session_regenerate_id(true);
set_flash_notice('You have been logged out.');
redirect_to('login.php');
