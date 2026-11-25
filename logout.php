<?php
require_once __DIR__ . "/config/base.php";
start_app_session();
$_SESSION = [];
session_destroy();
header("Location: " . url("/login.php"));
exit();
