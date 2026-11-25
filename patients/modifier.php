<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/activity.php";
require_once __DIR__ . "/../includes/patient_schema.php";

$code = (int) ($_GET["code"] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM patient WHERE Id_Patient = ?");
$stmt->execute([$code]);
$patient = $stmt->fetch();

if (!$patient) {
    $_SESSION["msg"] = "Patient introuvable.";
    $_SESSION["msg_type"] = "error";
    header("Location: " . url("/patients/rechercher.php"));
    exit();
}

$errors = [];
$nom = $patient["Nom"] ?? "";
$prenom = $patient["Prenom"] ?? "";
$sexeRaw = $patient["Sexe"] ?? "";
$sexe = $sexeRaw === "Feminin" ? "Féminin" : $sexeRaw;
$tel = $patient["Tel"] ?? "";
$adresse = $patient["Adresse"] ?? "";
$email = $patient["Email"] ?? "";
$date = $patient["Date_enregistrement"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $errors[] = "Token CSRF invalide.";
    } else {
        $nom = is_string($_POST["nom"] ?? null) ? trim($_POST["nom"]) : "";
        $prenom = is_string($_POST["prenom"] ?? null) ? trim($_POST["prenom"]) : "";
        $sexe = is_string($_POST["sexe"] ?? null) ? trim($_POST["sexe"]) : "";
        $tel = is_string($_POST["tel"] ?? null) ? trim($_POST["tel"]) : "";
        $adresse = is_string($_POST["adresse"] ?? null) ? trim($_POST["adresse"]) : "";
        $email = is_string($_POST["email"] ?? null) ? trim($_POST["email"]) : "";
        $date = is_string($_POST["date_enregistrement"] ?? null) ? trim($_POST["date_enregistrement"]) : "";
        $errors = patient_validation_errors($nom, $prenom, $sexe, $date, $tel, $adresse, $email);
        if (empty($errors)) {
            $pdo->prepare("UPDATE patient SET Nom=?, Prenom=?, Sexe=?, Tel=?, Adresse=?, Email=?, Date_enregistrement=? WHERE Id_Patient=?")
                ->execute([$nom, $prenom, $sexe, $tel, $adresse, $email, $date, $code]);
            log_activity($pdo, "patient", "Patient modifié #" . $code);
            $_SESSION["msg"] = "Patient #$code modifié avec succès.";
            $_SESSION["msg_type"] = "success";
            header("Location: " . url("/patients/rechercher.php?code=" . $code));
            exit();
        }
    }
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
        <i data-lucide="home" class="w-3.5 h-3.5"></i><span>Accueil</span><i data-lucide="chevron-right" class="w-3 h-3"></i><span>Patients</span><i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-slate-900 font-medium">Modifier</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Modifier le patient #<?= $code ?></h1>
    <?php if (!empty($errors)): ?>
    <div class="mb-4 px-4 py-3 rounded-md bg-red-100 text-red-700 text-sm">
        <ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= escape_html($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <form method="post" action="" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                <input type="text" name="nom" value="<?= escape_html($nom) ?>" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Prénom *</label>
                <input type="text" name="prenom" value="<?= escape_html($prenom) ?>" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sexe *</label>
                <select name="sexe" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Choisir --</option>
                    <option value="Masculin" <?= $sexe === "Masculin" ? "selected" : "" ?>>Masculin</option>
                    <option value="Féminin" <?= $sexe === "Féminin" ? "selected" : "" ?>>Féminin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                <input type="text" name="tel" placeholder="+509 00 00 0000" value="<?= escape_html($tel) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                <input type="text" name="adresse" value="<?= escape_html($adresse) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?= escape_html($email) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Date d'enregistrement</label>
                <input type="date" name="date_enregistrement" value="<?= escape_html($date) ?>" class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
    <p class="mt-4"><a href="<?= escape_html(url("/patients/rechercher.php?code=" . $code)) ?>" class="text-emerald-600 hover:underline">← Retour à la fiche patient</a></p>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
