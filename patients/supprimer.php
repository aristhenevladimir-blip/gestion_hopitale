<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/base.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_verify()) {
    $_SESSION["msg"] = "Action invalide. Rechargez la page et réessayez.";
    $_SESSION["msg_type"] = "error";
    header("Location: " . url("/patients/rechercher.php"));
    exit();
}
require_admin();

$code = (int) ($_GET["code"] ?? 0);
if ($code > 0) {
    $pdo->prepare("DELETE FROM patient WHERE Id_Patient = ?")->execute([$code]);
    $_SESSION["msg"] = "Patient #$code supprimé (ainsi que ses consultations et prescriptions).";
    $_SESSION["msg_type"] = "success";
} else {
    $_SESSION["msg"] = "Code patient invalide.";
    $_SESSION["msg_type"] = "error";
}
header("Location: " . url("/patients/rechercher.php"));
exit();
