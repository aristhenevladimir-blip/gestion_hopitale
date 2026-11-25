<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/base.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/activity.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_verify()) {
    $_SESSION["msg"] = "Action invalide. Rechargez la page et réessayez.";
    $_SESSION["msg_type"] = "error";
    header("Location: " . url("/prescriptions/liste.php"));
    exit();
}
require_admin();

$no = (int) ($_GET["no"] ?? 0);
$texte = is_string($_GET["texte"] ?? null) ? $_GET["texte"] : "";
$retour = $no;
if ($no > 0 && $texte !== "") {
    $stmt = $pdo->prepare("DELETE FROM prescription WHERE no_consultation = ? AND prescription = ?");
    $stmt->execute([$no, $texte]);
    if ($stmt->rowCount() > 0) {
        log_activity($pdo, "suppression", "Prescription supprimée (consultation #" . $no . ")");
        $_SESSION["msg"] = "Prescription supprimée.";
        $_SESSION["msg_type"] = "success";
    } else {
        $_SESSION["msg"] = "Prescription introuvable.";
        $_SESSION["msg_type"] = "error";
    }
} else {
    $_SESSION["msg"] = "Référence de prescription invalide.";
    $_SESSION["msg_type"] = "error";
}
header("Location: " . ($retour > 0 ? url("/prescriptions/liste.php?no=" . $retour) : url("/prescriptions/liste.php")));
exit();
