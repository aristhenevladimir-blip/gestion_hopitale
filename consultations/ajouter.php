<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/consultation_validation.php";

$patients = $pdo->query("SELECT Id_Patient, Nom, Prenom FROM patient ORDER BY Nom, Prenom")->fetchAll();
$errors = [];
$values = [
    "id_patient" => (int) ($_GET["patient"] ?? 0), "medecin" => "", "telephone" => "",
    "poids" => "", "poids_unit" => "kg", "taille" => "", "taille_unit" => "cm",
    "diagnostique" => "", "date_consultation" => date("Y-m-d"),
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
                $cols = array_keys($fields);
                $pdo->prepare("INSERT INTO consultation (" . implode(", ", $cols) . ") VALUES (" . implode(", ", array_fill(0, count($cols), "?")) . ")")
                    ->execute(array_values($fields));
                $no = (int) $pdo->lastInsertId();
                $_SESSION["msg"] = "Consultation enregistrée (n° $no). Vous pouvez maintenant ajouter le traitement et les prescriptions.";
                $_SESSION["msg_type"] = "success";
                header("Location: " . url("/prescriptions/ajouter.php?no=" . $no));
                exit();
            }
        }
    }
}

$pageTitle = "Nouvelle consultation";
require_once __DIR__ . "/../includes/header.php";
?>
<div class="mx-auto max-w-7xl space-y-4">
    <section class="relative isolate flex min-h-[88px] items-center overflow-hidden rounded-xl shadow-sm">
        <img src="<?= escape_html(url("/assets/img/banniere_hopital.png")) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-700/95 via-emerald-600/75 to-emerald-700/10"></div>
        <div class="relative z-10 flex items-center gap-3 px-5 py-4 text-white sm:px-7">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/10">
                <i data-lucide="stethoscope" class="h-6 w-6"></i>
            </span>
            <div>
                <h1 class="text-xl font-bold sm:text-2xl">Nouvelle consultation</h1>
                <p class="mt-0.5 text-xs text-emerald-50 sm:text-sm">Enregistrez les informations médicales du patient</p>
            </div>
        </div>
    </section>
    <?php if (!empty($errors)): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= escape_html($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <form method="post" action="" class="space-y-4">
        <?php csrf_field(); ?>
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <h2 class="mb-4 text-sm font-bold text-slate-900">Informations de la consultation</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="text-xs font-semibold text-slate-700 sm:col-span-2">Patient *
                    <select name="id_patient" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="">-- Sélectionner un patient --</option>
                        <?php foreach ($patients as $p): $sel = (int) $values["id_patient"] === (int) $p["Id_Patient"] ? "selected" : ""; ?>
                        <option value="<?= (int) $p["Id_Patient"] ?>" <?= $sel ?>><?= escape_html($p["Nom"] . " " . $p["Prenom"] . " (code " . $p["Id_Patient"] . ")") ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-700">Médecin *
                    <input type="text" name="medecin" required value="<?= escape_html((string) $values["medecin"]) ?>" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-700">Téléphone du médecin
                    <input type="text" name="telephone" value="<?= escape_html((string) $values["telephone"]) ?>" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-700">Date de consultation *
                    <input type="date" name="date_consultation" required max="<?= date("Y-m-d") ?>" value="<?= escape_html((string) $values["date_consultation"]) ?>" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-700">Poids (kg)
                    <input type="number" step="0.01" min="1" max="400" name="poids" value="<?= escape_html((string) $values["poids"]) ?>" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <input type="hidden" name="poids_unit" value="kg">
                </label>
                <label class="text-xs font-semibold text-slate-700">Taille (cm)
                    <input type="number" step="0.01" min="30" max="260" name="taille" value="<?= escape_html((string) $values["taille"]) ?>" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <input type="hidden" name="taille_unit" value="cm">
                </label>
                <label class="text-xs font-semibold text-slate-700 sm:col-span-2">Diagnostic *
                    <textarea name="diagnostique" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"><?= escape_html((string) $values["diagnostique"]) ?></textarea>
                </label>
            </div>
        </section>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="<?= escape_html(url("/consultations/liste.php")) ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"><i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i>Retour à la liste</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <i data-lucide="save" class="h-4 w-4"></i>Enregistrer et ajouter les prescriptions
            </button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
