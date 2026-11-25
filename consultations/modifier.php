<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/consultation_validation.php";

$pageTitle = "Modifier une consultation";
$no = (int) ($_GET["no"] ?? $_GET["id"] ?? 0);
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
$values = [
    "id_patient" => (int) $consult["Id_Patient"], "medecin" => $consult["Nom_Prenom_medecin"] ?? "",
    "telephone" => $consult["Telephone"] ?? "", "poids" => (string) ($consult["Poids"] ?? ""),
    "poids_unit" => "kg", "taille" => (string) ($consult["Taille_cm"] ?? ""),
    "taille_unit" => "cm", "diagnostique" => $consult["Diagnostique"] ?? "",
    "date_consultation" => $consult["Date_Consultation"] ?? date("Y-m-d"),
];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $errors[] = "Token CSRF invalide.";
    } else {
        foreach ($values as $key => $value) {
            if (isset($_POST[$key]) && !is_string($_POST[$key])) {
                $errors[] = "Les données envoyées sont invalides.";
                break;
            }
            if (isset($_POST[$key])) $values[$key] = trim($_POST[$key]);
        }

        if (!$errors) {
            $values["id_patient"] = (int) $values["id_patient"];
            $measurements = consultation_convert_measurements($values);
            $poidsKg = $measurements["poids_kg"];
            $tailleCm = $measurements["taille_cm"];
            $errors = consultation_validation_errors($values, $poidsKg, $tailleCm);
        }

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT 1 FROM patient WHERE Id_Patient = ?");
            $stmt->execute([$values["id_patient"]]);
            if (!$stmt->fetchColumn()) {
                $errors[] = "Le patient sélectionné n'existe pas.";
            } else {
                $fields = consultation_fields($values, $poidsKg, $tailleCm);
                $sql = "UPDATE consultation SET Id_Patient=?, Nom_Prenom_medecin=?, Telephone=?,
                        Poids=?, Taille_cm=?, Diagnostique=?, Date_Consultation=? WHERE no_Consultation=?";
                $pdo->prepare($sql)->execute([...array_values($fields), $no]);
                $_SESSION["msg"] = "Consultation #$no modifiée avec succès.";
                $_SESSION["msg_type"] = "success";
                header("Location: " . url("/consultations/liste.php"));
                exit();
            }
        }
    }
}

$medecinsList = $pdo->query(
    "SELECT DISTINCT Nom_Prenom_medecin FROM consultation
     WHERE Nom_Prenom_medecin IS NOT NULL AND Nom_Prenom_medecin <> ''
     ORDER BY Nom_Prenom_medecin"
)->fetchAll(PDO::FETCH_COLUMN);
require_once __DIR__ . "/../includes/header.php";
?>
<div class="mx-auto max-w-7xl space-y-4">
    <section class="relative isolate flex min-h-[88px] items-center overflow-hidden rounded-xl shadow-sm">
        <img src="<?= escape_html(url('/assets/img/banniere_hopital.png')) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-700/95 via-amber-600/75 to-amber-700/10"></div>
        <div class="relative z-10 flex w-full items-center justify-between gap-3 px-5 py-4 text-white sm:px-7">
            <div class="flex items-center gap-3">
                <i data-lucide="pencil" class="h-6 w-6"></i>
                <div><h1 class="text-xl font-bold sm:text-2xl">Modifier la consultation #<?= $no ?></h1>
                    <p class="text-xs text-amber-100 sm:text-sm">Mettez à jour les informations de cette consultation</p></div>
            </div>
            <a href="<?= escape_html(url('/consultations/liste.php')) ?>" class="rounded-lg bg-white/20 px-4 py-2 text-sm text-white hover:bg-white/30">Retour à la liste</a>
        </div>
    </section>

    <?php if ($errors): ?>
        <div class="rounded-md bg-red-100 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5"><?php foreach ($errors as $error): ?><li><?= escape_html($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="space-y-6">
        <?php csrf_field(); ?>
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Informations de la consultation</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Patient *
                    <select name="id_patient" required class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2">
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= (int) $patient["Id_Patient"] ?>" <?= (int) $values["id_patient"] === (int) $patient["Id_Patient"] ? "selected" : "" ?>><?= escape_html($patient["Nom"] . " " . $patient["Prenom"] . " (code " . $patient["Id_Patient"] . ")") ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block text-sm font-medium text-slate-700">Médecin *
                    <input name="medecin" required list="medecinsList" value="<?= escape_html((string) $values["medecin"]) ?>" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    <datalist id="medecinsList"><?php foreach ($medecinsList as $doctor): ?><option value="<?= escape_html($doctor) ?>"><?php endforeach; ?></datalist>
                </label>
                <label class="block text-sm font-medium text-slate-700">Téléphone du médecin
                    <input name="telephone" value="<?= escape_html((string) $values["telephone"]) ?>" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">Date de consultation *
                    <input type="date" name="date_consultation" required max="<?= date("Y-m-d") ?>" value="<?= escape_html((string) $values["date_consultation"]) ?>" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">Poids (kg)
                    <input type="number" step="0.01" min="1" max="400" name="poids" value="<?= escape_html((string) $values["poids"]) ?>" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    <input type="hidden" name="poids_unit" value="kg">
                </label>
                <label class="block text-sm font-medium text-slate-700">Taille (cm)
                    <input type="number" step="0.01" min="30" max="260" name="taille" value="<?= escape_html((string) $values["taille"]) ?>" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    <input type="hidden" name="taille_unit" value="cm">
                </label>
                <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Diagnostic *
                    <textarea name="diagnostique" rows="4" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><?= escape_html((string) $values["diagnostique"]) ?></textarea>
                </label>
            </div>
        </section>
        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="flex-1 rounded-md bg-emerald-600 px-4 py-3 font-medium text-white hover:bg-emerald-700">Enregistrer les modifications</button>
            <a href="<?= escape_html(url("/prescriptions/liste.php?no=" . $no)) ?>" class="rounded-md bg-slate-100 px-4 py-3 text-center font-medium text-slate-700">Traitement / Prescriptions</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
