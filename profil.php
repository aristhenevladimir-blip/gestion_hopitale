<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/csrf.php";

$id = (int) ($_SESSION["user"]["id"] ?? 0);
$message = "";
$error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $message = "Session invalide. Rechargez la page.";
        $error = true;
    } else {
        $current = (string) ($_POST["current_password"] ?? "");
        $new = (string) ($_POST["new_password"] ?? "");
        $confirm = (string) ($_POST["confirm_password"] ?? "");
        $stmt = $pdo->prepare("SELECT password_hash FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            $message = "Mot de passe actuel incorrect.";
            $error = true;
        } elseif (strlen($new) < 6 || strlen($new) > 72) {
            $message = "Le nouveau mot de passe doit contenir entre 6 et 72 caractères.";
            $error = true;
        } elseif ($new !== $confirm) {
            $message = "Les mots de passe ne correspondent pas.";
            $error = true;
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateur SET password_hash = ? WHERE id_utilisateur = ?");
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $id]);
            $message = "Mot de passe modifié.";
        }
    }
}

$pageTitle = "Mon profil";
require_once __DIR__ . "/includes/header.php";
?>
<div class="mx-auto max-w-3xl space-y-4">
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">Mon profil</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= escape_html($_SESSION["user"]["username"] ?? "") ?>
            — <?= ($_SESSION["user"]["role"] ?? "") === "admin" ? "Administrateur" : "Utilisateur" ?>
        </p>
    </section>

    <?php if ($message): ?>
        <p class="rounded-lg p-3 text-sm <?= $error ? "bg-red-50 text-red-700" : "bg-emerald-50 text-emerald-700" ?>">
            <?= escape_html($message) ?>
        </p>
    <?php endif; ?>

    <form method="post" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-900">Changer le mot de passe</h2>
        <?php csrf_field(); ?>
        <input type="password" name="current_password" required autocomplete="current-password"
               placeholder="Mot de passe actuel" class="w-full rounded-lg border p-2.5">
        <input type="password" name="new_password" required minlength="6" maxlength="72"
               autocomplete="new-password" placeholder="Nouveau mot de passe"
               class="w-full rounded-lg border p-2.5">
        <input type="password" name="confirm_password" required minlength="6" maxlength="72"
               autocomplete="new-password" placeholder="Confirmer le nouveau mot de passe"
               class="w-full rounded-lg border p-2.5">
        <button class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            Enregistrer
        </button>
    </form>
</div>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
