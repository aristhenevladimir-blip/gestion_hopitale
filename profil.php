<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/csrf.php";
require_once __DIR__ . "/includes/activity.php";
require_once __DIR__ . "/includes/formatters.php";
ensure_activity_log_table($pdo);

$userId = (int) ($_SESSION["user"]["id"] ?? 0);
$msg = ""; $msgType = "";

$stmt = $pdo->prepare("SELECT id_utilisateur, username, role, date_creation, derniere_connexion FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    $user = [
        "id_utilisateur" => $userId, "username" => $_SESSION["user"]["username"] ?? "",
        "role" => $_SESSION["user"]["role"] ?? "user", "date_creation" => null,
        "derniere_connexion" => $_SESSION["last_login"] ?? null,
    ];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "password";
    if (!csrf_verify()) {
        $msg = "Token CSRF invalide."; $msgType = "error";
    } elseif ($action === "password") {
        $current = is_string($_POST["current_password"] ?? null) ? $_POST["current_password"] : "";
        $new = is_string($_POST["new_password"] ?? null) ? $_POST["new_password"] : "";
        $confirm = is_string($_POST["confirm_password"] ?? null) ? $_POST["confirm_password"] : "";
        $stmt = $pdo->prepare("SELECT password_hash FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row["password_hash"])) { $msg = "Mot de passe actuel incorrect."; $msgType = "error"; }
        elseif (strlen($new) < 6 || strlen($new) > 72) { $msg = "Le nouveau mot de passe doit contenir entre 6 et 72 octets."; $msgType = "error"; }
        elseif ($new !== $confirm) { $msg = "Les nouveaux mots de passe ne correspondent pas."; $msgType = "error"; }
        else {
            $pdo->prepare("UPDATE utilisateur SET password_hash = ? WHERE id_utilisateur = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
            log_activity($pdo, "securite", "Changement de mot de passe");
            $msg = "Mot de passe modifié avec succès."; $msgType = "success";
        }
    }
    $fresh = $pdo->prepare("SELECT id_utilisateur, username, role, date_creation, derniere_connexion FROM utilisateur WHERE id_utilisateur = ?");
    $fresh->execute([$userId]);
    $f = $fresh->fetch();
    if ($f) $user = $f;
}

$nbPatients = (int) $pdo->query("SELECT COUNT(*) FROM patient")->fetchColumn();
$nbConsultations = (int) $pdo->query("SELECT COUNT(*) FROM consultation")->fetchColumn();
$nbPrescriptions = (int) $pdo->query("SELECT COUNT(*) FROM prescription")->fetchColumn();
$nbConsultJour = (int) $pdo->query("SELECT COUNT(*) FROM consultation WHERE Date_Consultation = CURDATE()")->fetchColumn();

$journal = [];
try { $st = $pdo->prepare("SELECT action, details, date_action FROM journal_activite WHERE id_utilisateur = ? ORDER BY id_journal DESC LIMIT 10"); $st->execute([$userId]); $journal = $st->fetchAll() ?: []; } catch (Exception $e) {}

$fullName = $user["username"] ?? "";
$userRole = $user["role"] ?? "";
$roleLabel = $userRole === "admin" ? "Administrateur" : ($userRole === "disabled" ? "Désactivé" : "Utilisateur");
$pageTitle = "Mon profil";
require_once __DIR__ . "/includes/header.php";

$activityCards = [
    ["Patients enregistrés", $nbPatients, "users-round", "sky", "#38bdf8"],
    ["Consultations", $nbConsultations, "stethoscope", "emerald", "#34d399"],
    ["Prescriptions", $nbPrescriptions, "pill", "violet", "#c084fc"],
    ["Consultations du jour", $nbConsultJour, "calendar-days", "orange", "#fb923c"],
];
$activityStyles = [
    "sky" => "border-sky-100 bg-sky-50/80 text-sky-700", "emerald" => "border-emerald-100 bg-emerald-50/80 text-emerald-700",
    "violet" => "border-violet-100 bg-violet-50/80 text-violet-700", "orange" => "border-orange-100 bg-orange-50/80 text-orange-700",
];
$journalStyles = [
    "prescription" => ["file-text", "bg-emerald-100 text-emerald-700"], "consultation" => ["stethoscope", "bg-sky-100 text-sky-700"],
    "patient" => ["user-round", "bg-violet-100 text-violet-700"], "connexion" => ["calendar-days", "bg-orange-100 text-orange-700"],
];
$dateCreation = $user["date_creation"] ?? null;
$derniereConnexion = $user["derniere_connexion"] ?? ($_SESSION["last_login"] ?? null);
?>
<div class="-m-6 min-h-full bg-slate-50 px-4 py-5 sm:px-6">
    <div class="mx-auto max-w-7xl space-y-4">
        <section class="relative isolate flex min-h-[88px] items-center gap-4 overflow-hidden rounded-xl shadow-sm">
            <img src="<?= escape_html(url("/assets/img/banniere_hopital.png")) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-800/95 via-emerald-600/75 to-emerald-600/5"></div>
            <div class="relative z-10 flex items-center gap-3 px-5 py-4 text-white sm:px-7">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/10"><i data-lucide="user-round" class="h-6 w-6"></i></span>
                <div>
                    <h1 class="text-xl font-bold sm:text-2xl">Mon profil</h1>
                    <p class="mt-0.5 text-xs text-emerald-50 sm:text-sm">Informations du compte, activité et sécurité.</p>
                </div>
            </div>
        </section>
        <?php if ($msg):
            $msgClass = $msgType === "error" ? "border-red-200 bg-red-50 text-red-700" : "border-emerald-200 bg-emerald-50 text-emerald-700"; ?>
        <div role="status" class="rounded-lg border px-4 py-3 text-sm font-medium <?= $msgClass ?>"><?= escape_html($msg) ?></div>
        <?php endif; ?>
        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
            <div class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm">
                <i data-lucide="user-round" class="h-11 w-11 fill-white/90 stroke-emerald-600"></i>
                <span class="absolute -bottom-0.5 -right-0.5 flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-emerald-700 text-white"><i data-lucide="camera" class="h-3.5 w-3.5"></i></span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900"><?= escape_html($fullName) ?></h2>
                    <?php $roleIcon = $userRole === "admin" ? "shield-check" : "user-round"; ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                        <i data-lucide="<?= $roleIcon ?>" class="h-3.5 w-3.5"></i><?= escape_html($roleLabel) ?>
                    </span>
                </div>
                <div class="mt-2 grid gap-1 text-xs text-slate-600 sm:grid-cols-2">
                    <p class="flex items-center gap-2"><i data-lucide="calendar-days" class="h-3.5 w-3.5 text-slate-400"></i>Compte créé le : <strong class="font-semibold"><?= format_date_fr($dateCreation) ?></strong></p>
                    <p class="flex items-center gap-2"><i data-lucide="clock-3" class="h-3.5 w-3.5 text-slate-400"></i>Dernière connexion : <strong class="font-semibold"><?= format_datetime_fr($derniereConnexion) ?></strong></p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <a href="#securityDetails" onclick="document.getElementById('securityDetails').open = true" class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"><i data-lucide="settings-2" class="h-4 w-4"></i> Modifier mon profil</a>
                <?php if (is_admin()): ?>
                <a href="<?= escape_html(url("/utilisateurs.php")) ?>" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"><i data-lucide="users-round" class="h-4 w-4"></i> Gérer les comptes</a>
                <?php endif; ?>
            </div>
        </section>
        <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-3 flex items-center gap-2">
                    <i data-lucide="chart-no-axes-column-increasing" class="h-5 w-5 text-emerald-700"></i>
                    <div><h2 class="font-bold text-slate-900">Mon activité</h2><p class="text-[10px] text-slate-500">Vue d'ensemble des données de l'hôpital</p></div>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <?php foreach ($activityCards as [$label, $value, $icon, $tone, $waveColor]): ?>
                    <article class="relative isolate min-h-24 overflow-hidden rounded-xl border p-3 <?= $activityStyles[$tone] ?>">
                        <svg aria-hidden="true" viewBox="0 0 240 42" preserveAspectRatio="none" class="pointer-events-none absolute inset-x-0 bottom-0 h-8 w-full opacity-50">
                            <path d="M0,30 C25,30 25,15 48,22 S75,34 97,20 S122,16 144,27 S170,33 192,19 S220,22 240,12" fill="none" stroke="<?= escape_html($waveColor) ?>" stroke-width="2"></path>
                        </svg>
                        <div class="relative z-10 flex items-start justify-between gap-2">
                            <div><p class="text-2xl font-bold leading-none"><?= (int) $value ?></p><p class="mt-1 text-[10px] font-medium text-slate-600"><?= escape_html($label) ?></p></div>
                            <span class="rounded-full bg-white/80 p-2"><i data-lucide="<?= escape_html($icon) ?>" class="h-4 w-4"></i></span>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2"><i data-lucide="history" class="h-5 w-5 text-emerald-700"></i><h2 class="font-bold text-slate-900">Journal d'activité</h2></div>
                    <?php if (count($journal) > 4): ?>
                    <button id="activityToggle" type="button" onclick="toggleActivityHistory()" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-100">Voir tout <i data-lucide="arrow-right" class="h-3 w-3"></i></button>
                    <?php endif; ?>
                </div>
                <?php if (count($journal) > 0): ?>
                <ul id="activityHistory" class="divide-y divide-slate-100">
                    <?php foreach ($journal as $index => $entry):
                        $actionKey = strtolower(trim((string) ($entry["action"] ?? "")));
                        $style = $journalStyles[$actionKey] ?? ["activity", "bg-slate-100 text-slate-600"];
                        $eventTs = !empty($entry["date_action"]) ? strtotime($entry["date_action"]) : false;
                        $extraClass = $index >= 4 ? " hidden" : "";
                        $extraAttr = $index >= 4 ? "true" : "false";
                    ?>
                    <li class="flex items-start gap-2.5 py-2.5 first:pt-1 last:pb-1<?= $extraClass ?>" data-activity-extra="<?= $extraAttr ?>">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?= $style[1] ?>"><i data-lucide="<?= escape_html($style[0]) ?>" class="h-4 w-4"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800"><?= escape_html(ucfirst((string) ($entry["action"] ?? ""))) ?></p>
                            <p class="mt-0.5 text-[10px] leading-relaxed text-slate-500"><?= escape_html($entry["details"] ?? "—") ?></p>
                        </div>
                        <time class="shrink-0 text-right text-[9px] leading-relaxed text-slate-400">
                            <?= format_date_fr($entry["date_action"] ?? null) ?><br><?php if ($eventTs) echo date("H:i", $eventTs); ?>
                        </time>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="flex min-h-32 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 px-4 text-center">
                    <i data-lucide="history" class="h-6 w-6 text-slate-300"></i>
                    <p class="mt-2 text-xs text-slate-500">Aucune activité enregistrée pour le moment.</p>
                </div>
                <?php endif; ?>
            </section>
        </div>
        <?php $detailsOpen = $msgType === "error" ? " open" : ""; ?>
        <details id="securityDetails" class="group overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-teal-50 shadow-sm"<?= $detailsOpen ?>>
            <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-5">
                <span class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"><i data-lucide="shield-check" class="h-5 w-5"></i></span>
                    <span><span class="block text-xs font-bold text-slate-800">Votre compte est sécurisé</span><span class="mt-0.5 block text-[10px] text-slate-500">Dernière connexion : <?= format_datetime_fr($derniereConnexion) ?></span></span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-[10px] font-semibold text-white transition hover:bg-emerald-700"><i data-lucide="lock-keyhole" class="h-3.5 w-3.5"></i> Changer mon mot de passe</span>
            </summary>
            <form method="post" action="" class="grid grid-cols-1 gap-3 border-t border-emerald-100/80 px-4 py-4 sm:grid-cols-2 lg:grid-cols-4 sm:px-5">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="password">
                <label class="text-[11px] font-semibold text-slate-700">Mot de passe actuel
                    <input type="password" name="current_password" required autocomplete="current-password" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </label>
                <label class="text-[11px] font-semibold text-slate-700">Nouveau mot de passe
                    <input type="password" name="new_password" required minlength="6" maxlength="72" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </label>
                <label class="text-[11px] font-semibold text-slate-700">Confirmer le nouveau mot de passe
                    <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </label>
                <div class="flex items-end"><button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-emerald-700">Enregistrer le mot de passe</button></div>
            </form>
        </details>
    </div>
</div>
<script>
function toggleActivityHistory(){
    var ex=document.querySelectorAll('[data-activity-extra="true"]'),btn=document.getElementById('activityToggle');
    if(!btn)return;var exp=btn.getAttribute('data-expanded')==='true';
    for(var i=0;i<ex.length;i++){exp?ex[i].classList.add('hidden'):ex[i].classList.remove('hidden');}
    btn.setAttribute('data-expanded',exp?'false':'true');
    btn.innerHTML=(exp?'Voir tout ':'Réduire ')+'<i data-lucide="arrow-right" class="h-3 w-3"></i>';
    if(window.lucide)lucide.createIcons();
}
</script>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
