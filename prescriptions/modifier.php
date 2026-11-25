<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/formatters.php";

$no = (int) ($_GET["no"] ?? 0);
$originalText = is_string($_GET["texte"] ?? null) ? $_GET["texte"] : "";
$stmt = $pdo->prepare('SELECT pr.*, c.Date_Consultation, c.Nom_Prenom_medecin, c.Diagnostique, p.Nom, p.Prenom, p.Id_Patient
  FROM prescription pr JOIN consultation c ON c.no_Consultation = pr.no_consultation
  JOIN patient p ON p.Id_Patient = c.Id_Patient WHERE pr.no_consultation = ? AND pr.prescription = ?');
$stmt->execute([$no, $originalText]);
$presc = $stmt->fetch();

if (!$presc) {
    $_SESSION["msg"] = "Prescription introuvable.";
    $_SESSION["msg_type"] = "error";
    header("Location: " . url("/prescriptions/liste.php"));
    exit();
}

$error = "";
$texte = $presc["prescription"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $error = "Token CSRF invalide.";
    } else {
        $texte = is_string($_POST["prescription"] ?? null) ? trim($_POST["prescription"]) : "";
        if (!isset($_POST["prescription"]) || !is_string($_POST["prescription"])) {
            $error = "La prescription est invalide.";
        } elseif ($texte === "") {
            $error = "La prescription ne peut pas être vide.";
        } elseif (text_length($texte) > 250) {
            $error = "La prescription ne peut pas dépasser 250 caractères.";
        } else {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM prescription WHERE no_consultation = ? AND LOWER(TRIM(prescription)) = LOWER(TRIM(?)) AND prescription <> ?");
            $chk->execute([(int) $presc["no_consultation"], $texte, $originalText]);
            if ((int) $chk->fetchColumn() > 0) {
                $error = "Cette prescription existe déjà pour cette consultation.";
            } else {
                $pdo->prepare("UPDATE prescription SET prescription = ? WHERE no_consultation = ? AND prescription = ?")
                    ->execute([$texte, (int) $presc["no_consultation"], $originalText]);
                $_SESSION["msg"] = "Prescription modifiée avec succès.";
                $_SESSION["msg_type"] = "success";
                header("Location: " . url("/prescriptions/liste.php?no=" . (int) $presc["no_consultation"]));
                exit();
            }
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Modifier une prescription</h1>
    <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm">
        <div class="font-semibold text-slate-900">Consultation n°<?= (int) $presc["no_consultation"] ?> — <?= escape_html($presc["Nom"] . " " . $presc["Prenom"]) ?> — <?= format_date_fr($presc["Date_Consultation"]) ?></div>
        <div class="text-slate-600 mt-1">Médecin : <?= escape_html($presc["Nom_Prenom_medecin"] ?: "—") ?></div>
    </div>
    <?php if ($error): ?><div class="mb-4 px-4 py-3 rounded-md bg-red-100 text-red-700 text-sm"><?= escape_html($error) ?></div><?php endif; ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <form method="post" action="" class="space-y-4">
            <?php csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Prescription *</label>
                <textarea name="prescription" rows="4" maxlength="250" required
                    class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= escape_html($texte) ?></textarea>
                <p class="text-xs text-slate-500 mt-1">Maximum 250 caractères.</p>
            </div>
            <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Enregistrer les modifications</button>
        </form>
    </div>
    <p class="mt-4"><a href="<?= escape_html(url("/prescriptions/liste.php?no=" . (int) $presc["no_consultation"])) ?>" class="text-emerald-600 hover:underline">← Retour à la liste des prescriptions</a></p>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
