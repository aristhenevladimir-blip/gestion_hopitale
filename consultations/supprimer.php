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

$no = (int) ($_GET["no"] ?? 0);
$retour = (int) ($_GET["patient"] ?? 0);
if ($no > 0) {
    $pdo->prepare("DELETE FROM consultation WHERE no_Consultation = ?")->execute([$no]);
    $_SESSION["msg"] = "Consultation #$no supprimée (ainsi que ses prescriptions).";
    $_SESSION["msg_type"] = "success";
} else {
    $_SESSION["msg"] = "Numéro de consultation invalide.";
    $_SESSION["msg_type"] = "error";
}
header("Location: " . ($retour > 0 ? url("/patients/rechercher.php?code=" . $retour) : url("/patients/rechercher.php")));
exit();
