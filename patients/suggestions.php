<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
header("Content-Type: application/json; charset=UTF-8");

$q = is_string($_GET["q"] ?? null) ? trim($_GET["q"]) : "";
if ($q === "") {
    echo json_encode(["items" => []], JSON_UNESCAPED_UNICODE);
    exit();
}

$params = [];
$where = [];
$code = "";
if (preg_match('/^#?\s*(?:PAT[\s-]*)(\d+)$/i', $q, $m)) {
    $code = ltrim($m[1], "0") ?: "0";
}
if ($code !== "") {
    $where[] = "CAST(Id_Patient AS CHAR) LIKE ?";
    $params[] = $code . "%";
}
$where[] = "Nom LIKE ?"; $params[] = "%" . $q . "%";
$where[] = "Prenom LIKE ?"; $params[] = "%" . $q . "%";
$where[] = "CONCAT(Nom,' ',Prenom) LIKE ?"; $params[] = "%" . $q . "%";
$where[] = "CONCAT(Prenom,' ',Nom) LIKE ?"; $params[] = "%" . $q . "%";

$stmt = $pdo->prepare("SELECT Id_Patient, Nom, Prenom, Tel, Date_enregistrement FROM patient WHERE " . implode(" OR ", $where) . " ORDER BY Id_Patient DESC LIMIT 8");
$stmt->execute($params);
$items = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $items[] = [
        "id" => (int) $r["Id_Patient"],
        "nom" => (string) $r["Nom"],
        "prenom" => (string) $r["Prenom"],
        "tel" => (string) ($r["Tel"] ?? ""),
        "date" => (string) ($r["Date_enregistrement"] ?? ""),
    ];
}
echo json_encode(["items" => $items], JSON_UNESCAPED_UNICODE);
