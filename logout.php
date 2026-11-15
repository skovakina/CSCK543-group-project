<?php
require_once __DIR__ . '/includes/functions.php';

ensure_session_started();
$_SESSION = [];
session_regenerate_id(true);
set_flash_notice('You have been logged out.');
redirect_to('login.php');
