<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/activity.php";
require_once __DIR__ . "/../includes/consultation_validation.php";

$pageTitle = "Modifier une consultation";
$no = (int) ($_GET["no"] ?? ($_GET["id"] ?? 0));
$stmt = $pdo->prepare("SELECT * FROM consultation WHERE no_Consultation = ?");
$stmt->execute([$no]);
$consult = $stmt->fetch();

if (!$consult) {
    $_SESSION["msg"] = "Consultation introuvable.";
    $_SESSION["msg_type"] = "error";
    header("Location: " . url("/patients/rechercher.php"));
    exit();
}

$patients = $pdo->query("SELECT Id_Patient, Nom, Prenom FROM patient ORDER BY Nom, Prenom")->fetchAll();
$errors = [];
$values = [
    "id_patient" => (int) $consult["Id_Patient"],
    "medecin" => $consult["Nom_Prenom_medecin"] ?? "",
    "telephone" => $consult["Telephone"] ?? "",
    "poids" => $consult["Poids"] !== null ? (string) $consult["Poids"] : "",
    "poids_unit" => "kg",
    "taille" => $consult["Taille_cm"] !== null ? (string) $consult["Taille_cm"] : "",
    "taille_unit" => "cm",
    "diagnostique" => $consult["Diagnostique"] ?? "",
    "date_consultation" => $consult["Date_Consultation"] ?? date("Y-m-d"),
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $errors[] = "Token CSRF invalide.";
    } else {
        $invalidInput = false;
        foreach ($values as $k => $_) {
            if (isset($_POST[$k])) {
                if (!is_string($_POST[$k])) { $invalidInput = true; }
                else { $values[$k] = trim($_POST[$k]); }
            }
        }
        if ($invalidInput) {
            $errors[] = "Les données envoyées sont invalides.";
        } else {
            $values["id_patient"] = (int) ($values["id_patient"] ?? 0);
            $measurements = consultation_convert_measurements($values);
            $poidsKg = $measurements["poids_kg"];
            $tailleCm = $measurements["taille_cm"];
            $errors = consultation_validation_errors($values, $poidsKg, $tailleCm);
        }
        if (empty($errors)) {
            $patientStmt = $pdo->prepare("SELECT 1 FROM patient WHERE Id_Patient = ?");
            $patientStmt->execute([$values["id_patient"]]);
            if (!$patientStmt->fetchColumn()) {
                $errors[] = "Le patient sélectionné n'existe pas.";
            } else {
                $fields = consultation_fields($values, $poidsKg, $tailleCm);
                $sets = []; $params = [];
                foreach ($fields as $col => $val) { $sets[] = $col . "=?"; $params[] = $val; }
                $params[] = $no;
                $pdo->prepare("UPDATE consultation SET " . implode(", ", $sets) . " WHERE no_Consultation=?")->execute($params);
                log_activity($pdo, "consultation", "Consultation modifiée #" . $no);
                $_SESSION["msg"] = "Consultation #$no modifiée avec succès.";
                $_SESSION["msg_type"] = "success";
                header("Location: " . url("/consultations/liste.php"));
                exit();
            }
        }
    }
}

$medecinsList = [];
try { $medecinsList = $pdo->query("SELECT DISTINCT Nom_Prenom_medecin FROM consultation WHERE Nom_Prenom_medecin IS NOT NULL AND Nom_Prenom_medecin <> '' ORDER BY Nom_Prenom_medecin")->fetchAll(PDO::FETCH_COLUMN); } catch (Exception $e) {}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="mx-auto max-w-7xl space-y-4">
    <section class="relative isolate flex min-h-[88px] items-center overflow-hidden rounded-xl shadow-sm">
        <img src="<?= escape_html(url('/assets/img/banniere_hopital.png')) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-700/95 via-amber-600/75 to-amber-700/10"></div>
        <div class="relative z-10 flex items-center justify-between w-full gap-3 px-5 py-4 text-white sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/10"><i data-lucide="pencil" class="h-6 w-6"></i></span>
                <div>
                    <h1 class="text-xl font-bold sm:text-2xl">Modifier la consultation #<?= $no ?></h1>
                    <p class="mt-0.5 text-xs text-amber-100 sm:text-sm">Mettez à jour les informations de cette consultation</p>
                </div>
            </div>
            <a href="<?= escape_html(url('/consultations/liste.php')) ?>" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-4 py-2 rounded-lg transition"><i data-lucide="arrow-left" class="h-4 w-4"></i>Retour à la liste</a>
        </div>
    </section>
    <?php if (!empty($errors)): ?>
    <div class="mb-4 px-4 py-3 rounded-md bg-red-100 text-red-700 text-sm">
        <ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= escape_html($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <form method="post" action="" class="space-y-6">
        <?php csrf_field(); ?>
        <section class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Informations de la consultation</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Patient *</label>
                    <select name="id_patient" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white">
                        <?php foreach ($patients as $p): $sel = (int) $values["id_patient"] === (int) $p["Id_Patient"] ? "selected" : ""; ?>
                        <option value="<?= (int) $p["Id_Patient"] ?>" <?= $sel ?>><?= escape_html($p["Nom"] . " " . $p["Prenom"] . " (code " . $p["Id_Patient"] . ")") ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom et prénom du médecin *</label>
                    <input type="text" name="medecin" required list="medecinsList" value="<?= escape_html((string) $values["medecin"]) ?>" placeholder="Ex: Jean Claude" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <datalist id="medecinsList">
                        <?php foreach ($medecinsList as $m): ?><option value="<?= escape_html($m) ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone du médecin</label>
                    <input type="text" name="telephone" value="<?= escape_html((string) $values["telephone"]) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date de consultation *</label>
                    <input type="date" name="date_consultation" required max="<?= date("Y-m-d") ?>" value="<?= escape_html((string) $values["date_consultation"]) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Poids (kg)</label>
                    <input type="number" step="0.01" min="1" max="400" name="poids" value="<?= escape_html((string) $values["poids"]) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white">
                    <input type="hidden" name="poids_unit" value="kg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Taille (cm)</label>
                    <input type="number" step="0.01" min="30" max="260" name="taille" value="<?= escape_html((string) $values["taille"]) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white">
                    <input type="hidden" name="taille_unit" value="cm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Diagnostic *</label>
                    <textarea name="diagnostique" rows="4" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white"><?= escape_html((string) $values["diagnostique"]) ?></textarea>
                </div>
            </div>
        </section>
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Enregistrer les modifications</button>
            <a href="<?= escape_html(url("/prescriptions/liste.php?no=" . $no)) ?>" class="px-4 py-3 bg-slate-100 rounded-md font-medium text-slate-700 text-center">Traitement / Prescriptions</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
