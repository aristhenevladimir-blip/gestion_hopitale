<?php
require_once __DIR__ . "/../config/base.php";
start_app_session();
if (empty($_SESSION["csrf_token"])) { $_SESSION["csrf_token"] = bin2hex(random_bytes(32)); }

function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") . '">';
}
function csrf_verify() {
    return !empty($_POST["csrf_token"]) && !empty($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"]);
}
