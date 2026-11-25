<?php
require_once __DIR__ . "/../config/base.php";
start_app_session();

if (empty($_SESSION["user"])) { header("Location: " . url("/login.php")); exit(); }

require_once __DIR__ . "/../config/db.php";
$userId = (int) ($_SESSION["user"]["id"] ?? 0);
$stmt = $pdo->prepare("SELECT role FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$userId]);
$currentRole = $stmt->fetchColumn();

if ($currentRole === false || $currentRole === "disabled") {
    $_SESSION = []; session_destroy();
    header("Location: " . url("/login.php")); exit();
}
$_SESSION["user"]["role"] = (string) $currentRole;

function is_admin() { return ($_SESSION["user"]["role"] ?? "") === "admin"; }
function require_admin() { if (!is_admin()) { header("Location: " . url("/403.php")); exit(); } }
