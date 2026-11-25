<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/csrf.php";
require_once __DIR__ . "/includes/activity.php";
require_once __DIR__ . "/includes/formatters.php";
require_admin();
ensure_activity_log_table($pdo);

$msg = ""; $msgType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if (!csrf_verify()) {
        $msg = "Token CSRF invalide."; $msgType = "error";
    } elseif ($action === "delete") {
        $delId = (int) ($_POST["id"] ?? 0);
        $currentId = (int) ($_SESSION["user"]["id"] ?? 0);
        if ($delId <= 0) { $msg = "Utilisateur invalide."; $msgType = "error"; }
        elseif ($delId === $currentId) { $msg = "Vous ne pouvez pas supprimer votre propre compte."; $msgType = "error"; }
        else {
            $pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?")->execute([$delId]);
            log_activity($pdo, "admin", "Suppression du compte #" . $delId);
            $msg = "Utilisateur supprimé."; $msgType = "success";
        }
    } elseif ($action === "toggle") {
        $tid = (int) ($_POST["id"] ?? 0);
        $currentId = (int) ($_SESSION["user"]["id"] ?? 0);
        if ($tid > 0 && $tid !== $currentId) {
            $pdo->prepare("UPDATE utilisateur SET role = CASE WHEN role = 'disabled' THEN 'user' ELSE 'disabled' END WHERE id_utilisateur = ?")->execute([$tid]);
            log_activity($pdo, "admin", "Activation/désactivation du compte #" . $tid);
            $msg = "Statut du compte mis à jour."; $msgType = "success";
        } else { $msg = "Action non autorisée."; $msgType = "error"; }
    } elseif ($action === "add") {
        $username = is_string($_POST["username"] ?? null) ? trim($_POST["username"]) : "";
        $password = is_string($_POST["password"] ?? null) ? $_POST["password"] : "";
        $role = is_string($_POST["role"] ?? null) ? $_POST["role"] : "user";
        if ($role !== "admin" && $role !== "user") { $msg = "Rôle invalide."; $msgType = "error"; }
        elseif ($username === "" || text_length($username) > 50 || strlen($password) < 6 || strlen($password) > 72) {
            $msg = "Nom d'utilisateur (50 caractères max.) et mot de passe (6 à 72 octets) requis."; $msgType = "error";
        } else {
            try {
                $pdo->prepare("INSERT INTO utilisateur (username, password_hash, role) VALUES (?, ?, ?)")
                    ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                log_activity($pdo, "admin", "Création du compte " . $username);
                $msg = "Utilisateur créé."; $msgType = "success";
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) { $msg = "Ce nom d'utilisateur existe déjà."; $msgType = "error"; }
                else { throw $e; }
            }
        }
    }
}

$users = $pdo->query("SELECT id_utilisateur, username, role, date_creation, derniere_connexion FROM utilisateur ORDER BY id_utilisateur")->fetchAll();
$pageTitle = "Administration des comptes";
require_once __DIR__ . "/includes/header.php";
?>
<div class="-m-6 min-h-full bg-slate-50 px-4 py-6 sm:px-6">
    <section class="mx-auto max-w-7xl rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-5 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i data-lucide="users-round" class="h-5 w-5"></i></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Administration des comptes</h1>
                <p class="mt-0.5 text-xs text-slate-500">Créer et gérer les accès des utilisateurs autorisés.</p>
            </div>
        </div>
        <?php if ($msg):
            $msgClass = $msgType === "error" ? "border-red-200 bg-red-50 text-red-700" : "border-emerald-200 bg-emerald-50 text-emerald-700"; ?>
        <div role="status" class="mb-4 rounded-lg border px-4 py-3 text-sm font-medium <?= $msgClass ?>"><?= escape_html($msg) ?></div>
        <?php endif; ?>
        <form method="post" action="" class="grid grid-cols-1 gap-3 border-b border-slate-100 pb-5 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <label class="block text-sm font-semibold text-slate-700">Nom d'utilisateur
                <input type="text" name="username" required maxlength="50" autocomplete="username" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Mot de passe
                <input type="password" name="password" required minlength="6" maxlength="72" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Rôle
                <select name="role" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 font-normal focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </label>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"><i data-lucide="user-plus" class="h-4 w-4"></i> Créer un compte</button>
        </form>
        <div class="mt-5 overflow-hidden">
            <div class="mb-2 flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-800">Utilisateurs</h2>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600"><?= count($users) ?> compte(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs text-slate-500">
                            <th class="px-2 py-3 font-semibold">Utilisateur</th><th class="px-2 py-3 font-semibold">Rôle</th><th class="px-2 py-3 font-semibold">Créé le</th><th class="px-2 py-3 font-semibold">Dernière connexion</th><th class="px-2 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $u):
                            $r = $u["role"] ?? "user";
                            if ($r === "admin") { $roleLabel = "Administrateur"; $roleClass = "bg-emerald-100 text-emerald-700"; }
                            elseif ($r === "disabled") { $roleLabel = "Désactivé"; $roleClass = "bg-amber-100 text-amber-700"; }
                            else { $roleLabel = "Utilisateur"; $roleClass = "bg-slate-100 text-slate-700"; }
                            $uid = (int) ($u["id_utilisateur"] ?? 0);
                            $currentId = (int) ($_SESSION["user"]["id"] ?? 0);
                        ?>
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-2 py-3.5 font-semibold text-slate-800"><?= escape_html($u["username"]) ?></td>
                            <td class="px-2 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold <?= $roleClass ?>"><?= escape_html($roleLabel) ?></span></td>
                            <td class="whitespace-nowrap px-2 py-3.5 text-slate-500"><?= format_date_fr($u["date_creation"] ?? null) ?></td>
                            <td class="whitespace-nowrap px-2 py-3.5 text-slate-500"><?= format_datetime_fr($u["derniere_connexion"] ?? null) ?></td>
                            <td class="whitespace-nowrap px-2 py-3.5 text-right">
                                <?php if ($uid === $currentId): ?>
                                <span class="text-xs font-medium text-slate-400">Vous</span>
                                <?php else: ?>
                                <div class="inline-flex items-center gap-3">
                                    <form method="post"><?php csrf_field(); ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $uid ?>">
                                        <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline"><?= $r === "disabled" ? "Activer" : "Désactiver" ?></button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $uid ?>">
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
