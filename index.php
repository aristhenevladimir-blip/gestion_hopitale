<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/formatters.php";

$nbPatients = (int) $pdo->query("SELECT COUNT(*) FROM patient")->fetchColumn();
$nbConsultations = (int) $pdo->query("SELECT COUNT(*) FROM consultation")->fetchColumn();
$nbPrescriptions = (int) $pdo->query("SELECT COUNT(*) FROM prescription")->fetchColumn();

$lastConsults = [];
try {
    $lastConsults = $pdo->query("SELECT c.no_Consultation, c.Date_Consultation, c.Diagnostique, c.Nom_Prenom_medecin, p.Id_Patient, p.Nom, p.Prenom FROM consultation c JOIN patient p ON p.Id_Patient = c.Id_Patient ORDER BY c.Date_Consultation DESC, c.no_Consultation DESC LIMIT 3")->fetchAll();
} catch (Exception $e) {}

$todayConsults = [];
try {
    $todayConsults = $pdo->query("SELECT c.no_Consultation, c.Date_Consultation, c.Diagnostique, c.Nom_Prenom_medecin, p.Id_Patient, p.Nom, p.Prenom FROM consultation c JOIN patient p ON p.Id_Patient = c.Id_Patient WHERE DATE(c.Date_Consultation) = CURDATE() ORDER BY c.no_Consultation DESC LIMIT 8")->fetchAll();
} catch (Exception $e) {}

$activityLabels = [];
$activityData = [];
$prescriptionActivityData = [];
$activityStart = date("Y-m-d", strtotime("-13 days"));

$stmt = $pdo->prepare("SELECT DATE(Date_Consultation) AS jour, COUNT(*) AS total FROM consultation WHERE Date_Consultation >= ? AND Date_Consultation <= CURDATE() GROUP BY DATE(Date_Consultation)");
$stmt->execute([$activityStart]);
$consultationsByDay = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $consultationsByDay[$row["jour"]] = (int) $row["total"];
}

$stmt = $pdo->prepare("SELECT DATE(c.Date_Consultation) AS jour, COUNT(*) AS total FROM prescription pr JOIN consultation c ON c.no_Consultation = pr.no_consultation WHERE c.Date_Consultation >= ? AND c.Date_Consultation <= CURDATE() GROUP BY DATE(c.Date_Consultation)");
$stmt->execute([$activityStart]);
$prescriptionsByDay = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $prescriptionsByDay[$row["jour"]] = (int) $row["total"];
}

for ($i = 13; $i >= 0; $i--) {
    $d = date("Y-m-d", strtotime("-$i days"));
    $activityLabels[] = date("d/m", strtotime($d));
    $activityData[] = $consultationsByDay[$d] ?? 0;
    $prescriptionActivityData[] = $prescriptionsByDay[$d] ?? 0;
}

$sexeLabels = [];
$sexeData = [];
$sexeColors = [];
try {
    $sexeRows = $pdo->query("SELECT COALESCE(NULLIF(Sexe,''), 'Non renseigné') AS Sexe, COUNT(*) as nb FROM patient GROUP BY COALESCE(NULLIF(Sexe,''), 'Non renseigné')")->fetchAll();
    foreach ($sexeRows as $s) {
        $raw = $s["Sexe"];
        $sexeLabels[] = $raw === "Feminin" ? "Féminin" : ($raw === "Masculin" ? "Masculin" : $raw);
        $sexeData[] = (int) $s["nb"];
        $sexeColors[] = $raw === "Masculin" ? "#3b82f6" : ($raw === "Feminin" ? "#ec4899" : "#94a3b8");
    }
} catch (Exception $e) {
    $sexeLabels = ["Aucune donnée"];
    $sexeData = [0];
    $sexeColors = ["#94a3b8"];
}

$activities = [];
$recentActivities = $pdo->query("SELECT username, action, details, date_action FROM journal_activite WHERE action IN ('patient', 'consultation', 'prescription', 'suppression') ORDER BY id_journal DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$activityPresentation = [
    "patient" => ["label" => "Dossier patient", "icon" => "user-round", "style" => "bg-emerald-50 text-emerald-700", "dot" => "bg-emerald-500"],
    "consultation" => ["label" => "Consultation", "icon" => "stethoscope", "style" => "bg-blue-50 text-blue-700", "dot" => "bg-blue-500"],
    "prescription" => ["label" => "Prescription", "icon" => "pill", "style" => "bg-violet-50 text-violet-700", "dot" => "bg-violet-500"],
    "suppression" => ["label" => "Suppression", "icon" => "trash-2", "style" => "bg-red-50 text-red-700", "dot" => "bg-red-500"],
];
foreach ($recentActivities as $activity) {
    $action = (string) $activity["action"];
    $timestamp = strtotime((string) $activity["date_action"]);
    $activityText = trim((string) ($activity["details"] ?? ""));
    if ($activityText === "") $activityText = ucfirst($action);
    $activityUser = trim((string) ($activity["username"] ?? ""));
    if ($activityUser === "") $activityUser = "Utilisateur non renseigné";
    $activities[] = [
        "text" => $activityText,
        "action" => $activityPresentation[$action] ?? $activityPresentation["patient"],
        "username" => $activityUser,
        "datetime" => $timestamp !== false ? date(DATE_ATOM, $timestamp) : "",
        "time" => $timestamp !== false ? date("d/m/Y · H:i", $timestamp) : "Date non renseignée",
    ];
}
$activityTotal = array_sum($activityData) + array_sum($prescriptionActivityData);
$sexeTotal = array_sum($sexeData);

require_once __DIR__ . "/includes/header.php";

$moisFrancais = french_months();
$dateAujourdhui = date("j") . " " . $moisFrancais[(int) date("n")] . " " . date("Y");
$medecinImage = null;
foreach (["assets/img", "img", "assets/images"] as $dir) {
    foreach (["png", "jpg", "jpeg", "webp", "svg"] as $ext) {
        if (is_file(__DIR__ . "/$dir/medecin_pose.$ext")) {
            $medecinImage = url("/$dir/medecin_pose.$ext");
            break 2;
        }
    }
}

require __DIR__ . "/includes/dashboard_widgets.php";
require __DIR__ . "/includes/dashboard_charts.php";
require_once __DIR__ . "/includes/footer.php";
