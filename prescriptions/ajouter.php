<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/activity.php";
require_once __DIR__ . "/../includes/formatters.php";

$selectedNo = (int) ($_GET["no"] ?? ($_POST["no_consultation"] ?? 0));
$consultations = $pdo->query('SELECT c.no_Consultation, c.Date_Consultation, c.Nom_Prenom_medecin, c.Diagnostique, p.Nom, p.Prenom
  FROM consultation c JOIN patient p ON p.Id_Patient = c.Id_Patient ORDER BY c.no_Consultation DESC LIMIT 250')->fetchAll();

$selectedConsult = null;
if ($selectedNo > 0) {
    $st = $pdo->prepare("SELECT c.no_Consultation, c.Date_Consultation, c.Nom_Prenom_medecin, c.Diagnostique, p.Nom, p.Prenom, p.Id_Patient FROM consultation c JOIN patient p ON p.Id_Patient=c.Id_Patient WHERE c.no_Consultation=?");
    $st->execute([$selectedNo]);
    $selectedConsult = $st->fetch();
}

$errors = [];
$texte = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $errors[] = "Token CSRF invalide.";
    } else {
        $selectedNo = (int) ($_POST["no_consultation"] ?? 0);
        $texte = is_string($_POST["prescription"] ?? null) ? trim($_POST["prescription"]) : "";
        $another = isset($_POST["another"]);
        if ($selectedNo <= 0) $errors[] = "Veuillez sélectionner une consultation.";
        if (isset($_POST["prescription"]) && !is_string($_POST["prescription"])) $errors[] = "La prescription est invalide.";
        if ($texte === "") $errors[] = "Veuillez saisir la prescription.";
        elseif (text_length($texte) > 250) $errors[] = "La prescription ne peut pas dépasser 250 caractères.";
        if (empty($errors)) {
            $chkConsult = $pdo->prepare("SELECT 1 FROM consultation WHERE no_Consultation = ?");
            $chkConsult->execute([$selectedNo]);
            if (!$chkConsult->fetchColumn()) {
                $errors[] = "La consultation sélectionnée n'existe pas.";
            } else {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM prescription WHERE no_consultation = ? AND LOWER(TRIM(prescription)) = LOWER(TRIM(?))");
                $chk->execute([$selectedNo, $texte]);
                if ((int) $chk->fetchColumn() > 0) {
                    $errors[] = "Cette prescription existe déjà pour cette consultation.";
                } else {
                    $pdo->prepare("INSERT INTO prescription (no_consultation, prescription) VALUES (?,?)")->execute([$selectedNo, $texte]);
                    log_activity($pdo, "prescription", "Prescription ajoutée à la consultation #" . $selectedNo);
                    $_SESSION["msg"] = "Prescription enregistrée avec succès.";
                    $_SESSION["msg_type"] = "success";
                    header("Location: " . url("/prescriptions/" . ($another ? "ajouter" : "liste") . ".php?no=" . $selectedNo));
                    exit();
                }
            }
        }
        if ($selectedNo > 0) {
            $st = $pdo->prepare("SELECT c.no_Consultation, c.Date_Consultation, c.Nom_Prenom_medecin, c.Diagnostique, p.Nom, p.Prenom, p.Id_Patient FROM consultation c JOIN patient p ON p.Id_Patient=c.Id_Patient WHERE c.no_Consultation=?");
            $st->execute([$selectedNo]);
            $selectedConsult = $st->fetch();
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
        <i data-lucide="home" class="w-3.5 h-3.5"></i><span>Accueil</span><i data-lucide="chevron-right" class="w-3 h-3"></i><span>Prescriptions</span><i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-slate-900 font-medium">Ajouter</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Ajouter une prescription</h1>
    <p class="text-slate-500 text-sm mb-6">Ajoutez une prescription à la fois pour la consultation sélectionnée.</p>
    <?php if ($selectedConsult): ?>
    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm">
        <div class="font-semibold text-slate-900">Consultation n°<?= (int) $selectedConsult["no_Consultation"] ?> — <?= escape_html($selectedConsult["Nom"] . " " . $selectedConsult["Prenom"]) ?> — <?= format_date_fr($selectedConsult["Date_Consultation"]) ?></div>
        <div class="text-slate-600 mt-1">Médecin : <?= escape_html($selectedConsult["Nom_Prenom_medecin"] ?: "—") ?> · Diagnostic : <?= escape_html(shorten_text($selectedConsult["Diagnostique"] ?? "", 80)) ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
    <div class="mb-4 px-4 py-3 rounded-md bg-red-100 text-red-700 text-sm">
        <ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= escape_html($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <form method="post" action="" class="space-y-4">
            <?php csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Consultation *</label>
                <select name="no_consultation" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Sélectionner une consultation --</option>
                    <?php foreach ($consultations as $c): $sel = (int) $selectedNo === (int) $c["no_Consultation"] ? "selected" : ""; ?>
                    <option value="<?= (int) $c["no_Consultation"] ?>" <?= $sel ?>>
                        N°<?= (int) $c["no_Consultation"] ?> — <?= escape_html($c["Nom"] . " " . $c["Prenom"]) ?> — <?= format_date_fr($c["Date_Consultation"]) ?> — Dr. <?= escape_html($c["Nom_Prenom_medecin"] ?: "—") ?> — <?= escape_html(shorten_text($c["Diagnostique"] ?? "", 30)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Prescription *</label>
                <textarea name="prescription" rows="4" maxlength="250" required placeholder="Saisissez la prescription"
                    class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= escape_html($texte) ?></textarea>
                <p class="text-xs text-slate-500 mt-1">Maximum 250 caractères. Une prescription par enregistrement.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" name="another" value="1" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Enregistrer et en ajouter une autre</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Enregistrer la prescription</button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
