<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/csrf.php";
require_once __DIR__ . "/../includes/activity.php";
require_once __DIR__ . "/../includes/patient_schema.php";

$errors = [];
$nom = $prenom = $sexe = $tel = $adresse = $email = "";
$date = date("Y-m-d");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $errors[] = "Token CSRF invalide. Rechargez la page.";
    } else {
        $nom = is_string($_POST["nom"] ?? null) ? trim($_POST["nom"]) : "";
        $prenom = is_string($_POST["prenom"] ?? null) ? trim($_POST["prenom"]) : "";
        $sexe = is_string($_POST["sexe"] ?? null) ? trim($_POST["sexe"]) : "";
        $tel = is_string($_POST["tel"] ?? null) ? trim($_POST["tel"]) : "";
        $adresse = is_string($_POST["adresse"] ?? null) ? trim($_POST["adresse"]) : "";
        $email = is_string($_POST["email"] ?? null) ? trim($_POST["email"]) : "";
        $date = trim(is_string($_POST["date_enregistrement"] ?? null) ? $_POST["date_enregistrement"] : date("Y-m-d"));
        $errors = patient_validation_errors($nom, $prenom, $sexe, $date, $tel, $adresse, $email);
        if (empty($errors)) {
            $pdo->prepare("INSERT INTO patient (Nom, Prenom, Sexe, Tel, Adresse, Email, Date_enregistrement) VALUES (?,?,?,?,?,?,?)")
                ->execute([$nom, $prenom, $sexe, $tel, $adresse, $email, $date]);
            $id = (int) $pdo->lastInsertId();
            log_activity($pdo, "patient", "Patient ajouté #" . $id . " (" . $nom . " " . $prenom . ")");
            $_SESSION["msg"] = "Patient enregistré avec succès. Code patient : $id";
            $_SESSION["msg_type"] = "success";
            header("Location: " . url("/patients/rechercher.php?code=" . $id));
            exit();
        }
    }
}

$pageTitle = "Ajouter un patient";
require_once __DIR__ . "/../includes/header.php";
?>
<div class="mx-auto max-w-7xl space-y-4">
    <section class="relative isolate flex min-h-[88px] items-center overflow-hidden rounded-xl shadow-sm">
        <img src="<?= escape_html(url("/assets/img/banniere_hopital.png")) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-700/95 via-emerald-600/75 to-emerald-700/10"></div>
        <div class="relative z-10 flex items-center gap-3 px-5 py-4 text-white sm:px-7">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/10"><i data-lucide="user-round-plus" class="h-6 w-6"></i></span>
            <div>
                <h1 class="text-xl font-bold sm:text-2xl">Ajouter un patient</h1>
                <p class="mt-0.5 text-xs text-emerald-50 sm:text-sm">Enregistrez un nouveau patient dans le système</p>
            </div>
        </div>
    </section>
    <?php if (!empty($errors)): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= escape_html($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-white px-4 py-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white"><i data-lucide="user-round" class="h-4 w-4"></i></span>
            <div>
                <h2 class="text-sm font-bold text-slate-900">Informations du patient</h2>
                <p class="text-xs text-slate-500">Les champs marqués d'une étoile (*) sont obligatoires.</p>
            </div>
        </div>
        <form method="post" action="" class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-12 sm:p-5">
            <?php csrf_field(); ?>
            <div class="space-y-3 sm:col-span-8">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Nom *</label>
                        <input type="text" name="nom" required value="<?= escape_html($nom) ?>" placeholder="Entrez le nom du patient" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Prénom *</label>
                        <input type="text" name="prenom" required value="<?= escape_html($prenom) ?>" placeholder="Entrez le prénom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Sexe *</label>
                        <select name="sexe" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" <?= $sexe === "Masculin" ? "selected" : "" ?>>Masculin</option>
                            <option value="Féminin" <?= $sexe === "Féminin" ? "selected" : "" ?>>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Téléphone</label>
                        <input type="text" name="tel" placeholder="+509 00 00 0000" value="<?= escape_html($tel) ?>" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Adresse</label>
                        <input type="text" name="adresse" value="<?= escape_html($adresse) ?>" placeholder="Entrez l'adresse complète" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="<?= escape_html($email) ?>" placeholder="exemple@email.com" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Date d'enregistrement</label>
                        <input type="date" name="date_enregistrement" value="<?= escape_html($date) ?>" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 pt-1 sm:grid-cols-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"><i data-lucide="save" class="h-4 w-4"></i>Enregistrer le patient</button>
                    <button type="reset" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"><i data-lucide="rotate-ccw" class="h-4 w-4"></i>Réinitialiser</button>
                </div>
            </div>
            <aside class="flex flex-col items-center justify-center rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-5 text-center sm:col-span-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="user-round-plus" class="h-7 w-7"></i></span>
                <h3 class="mt-3 text-sm font-bold text-slate-800">Enregistrez facilement un nouveau patient</h3>
                <ul class="mt-3 w-full space-y-2 border-t border-emerald-100 pt-3 text-left text-xs text-slate-600">
                    <li class="flex items-center gap-2"><i data-lucide="circle-check" class="h-4 w-4 shrink-0 text-emerald-600"></i>Informations sécurisées</li>
                    <li class="flex items-center gap-2"><i data-lucide="circle-check" class="h-4 w-4 shrink-0 text-emerald-600"></i>Accès rapide au dossier</li>
                    <li class="flex items-center gap-2"><i data-lucide="circle-check" class="h-4 w-4 shrink-0 text-emerald-600"></i>Gestion centralisée</li>
                </ul>
            </aside>
        </form>
    </section>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide)lucide.createIcons();});</script>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
