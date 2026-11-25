<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/csrf.php";
require_admin();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $msg = "Session invalide.";
    } elseif (($_POST["action"] ?? "") === "add") {
        $name = trim((string) ($_POST["username"] ?? ""));
        $password = (string) ($_POST["password"] ?? "");
        $role = (string) ($_POST["role"] ?? "user");
        if ($name === "" || strlen($password) < 6 || !in_array($role, ["user", "admin"], true)) {
            $msg = "Informations invalides.";
        } else {
            try {
                $pdo->prepare("INSERT INTO utilisateur (username,password_hash,role) VALUES (?,?,?)")
                    ->execute([$name, password_hash($password, PASSWORD_DEFAULT), $role]);
                $msg = "Utilisateur créé.";
            } catch (PDOException $e) {
                $msg = "Utilisateur déjà existant.";
            }
        }
    } else {
        $id = (int) ($_POST["id"] ?? 0);
        $self = (int) ($_SESSION["user"]["id"] ?? 0);
        if ($id > 0 && $id !== $self) {
            if (($_POST["action"] ?? "") === "toggle") {
                $pdo->prepare("UPDATE utilisateur SET role=IF(role='disabled','user','disabled') WHERE id_utilisateur=?")
                    ->execute([$id]);
                $msg = "Statut modifié.";
            }
            if (($_POST["action"] ?? "") === "delete") {
                $pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur=?")->execute([$id]);
                $msg = "Utilisateur supprimé.";
            }
        }
    }
}

$users = $pdo->query("SELECT id_utilisateur,username,role FROM utilisateur ORDER BY id_utilisateur")
    ->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Utilisateurs";
require_once __DIR__ . "/includes/header.php";
?>
<div class="mx-auto max-w-5xl space-y-5">
    <section class="rounded-xl border border-slate-200 bg-white p-4">
        <h1 class="font-bold text-slate-900">Gestion des utilisateurs</h1>
        <?php if ($msg): ?><p class="mt-2 text-sm text-emerald-700"><?= escape_html($msg) ?></p><?php endif; ?>
        <form method="post" class="mt-3 grid gap-2 sm:grid-cols-4">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <input name="username" required maxlength="50" placeholder="Nom"
                   class="rounded-lg border px-3 py-2">
            <input type="password" name="password" required minlength="6" maxlength="72" placeholder="Mot de passe"
                   class="rounded-lg border px-3 py-2">
            <select name="role" class="rounded-lg border px-3 py-2">
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
            </select>
            <button class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white">Créer</button>
        </form>
    </section>
    <section class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <tr class="border-b bg-slate-50 text-left text-slate-500">
                <th class="p-3">Utilisateur</th><th>Rôle</th><th class="p-3 text-right">Actions</th>
            </tr>
            <?php foreach ($users as $u):
                $id = (int) $u["id_utilisateur"];
                $role = $u["role"] === "admin" ? "Administrateur" : ($u["role"] === "disabled" ? "Désactivé" : "Utilisateur");
            ?>
            <tr class="border-b">
                <td class="p-3"><?= escape_html($u["username"]) ?></td>
                <td><?= escape_html($role) ?></td>
                <td class="p-3 text-right">
                    <?php if ($id !== (int) ($_SESSION["user"]["id"] ?? 0)): ?>
                        <form method="post" class="inline"><?php csrf_field(); ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button class="mr-3 text-emerald-700"><?= $u["role"] === "disabled" ? "Activer" : "Désactiver" ?></button>
                        </form>
                        <button type="button" onclick="confirmAction('Supprimer cet utilisateur ?', '', 'delete', <?= $id ?>)" class="text-red-600">Supprimer</button>
                    <?php else: ?>Vous<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
