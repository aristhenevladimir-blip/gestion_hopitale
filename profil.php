<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/csrf.php";

$userId = (int) ($_SESSION["user"]["id"] ?? 0);
$message = "";
$messageType = "success";

$stmt = $pdo->prepare(
    "SELECT id_utilisateur, username, role, date_creation, derniere_connexion
     FROM utilisateur WHERE id_utilisateur = ?"
);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $message = "Session invalide. Veuillez réessayer.";
        $messageType = "error";
    } else {
        $currentPassword = (string) ($_POST["current_password"] ?? "");
        $newPassword = (string) ($_POST["new_password"] ?? "");
        $confirmPassword = (string) ($_POST["confirm_password"] ?? "");

        $stmt = $pdo->prepare(
            "SELECT password_hash FROM utilisateur WHERE id_utilisateur = ?"
        );
        $stmt->execute([$userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account || !password_verify($currentPassword, $account["password_hash"])) {
            $message = "Mot de passe actuel incorrect.";
            $messageType = "error";
        } elseif (strlen($newPassword) < 6) {
            $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            $messageType = "error";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "Les nouveaux mots de passe ne correspondent pas.";
            $messageType = "error";
        } else {
            $stmt = $pdo->prepare(
                "UPDATE utilisateur SET password_hash = ? WHERE id_utilisateur = ?"
            );
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            $message = "Mot de passe modifié avec succès.";
        }
    }
}

$username = $user["username"] ?? ($_SESSION["user"]["username"] ?? "");
$role = $user["role"] ?? ($_SESSION["user"]["role"] ?? "user");
$roleLabel = $role === "admin" ? "Administrateur" : "Utilisateur";
$pageTitle = "Mon profil";

require_once __DIR__ . "/includes/header.php";
?>

<div class="mx-auto max-w-4xl space-y-5">
    <section class="relative isolate overflow-hidden rounded-2xl bg-emerald-700 px-6 py-6 text-white shadow-sm">
        <img src="<?= escape_html(url("/assets/img/banniere_hopital.png")) ?>"
             alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-25">
        <h1 class="text-2xl font-bold">Mon profil</h1>
        <p class="mt-1 text-sm text-emerald-100">Informations du compte et mot de passe.</p>
    </section>

    <?php if ($message): ?>
        <?php $messageClass = $messageType === "error"
            ? "border-red-200 bg-red-50 text-red-700"
            : "border-emerald-200 bg-emerald-50 text-emerald-700"; ?>
        <div class="rounded-lg border px-4 py-3 text-sm font-medium <?= $messageClass ?>">
            <?= escape_html($message) ?>
        </div>
    <?php endif; ?>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-600 text-white">
                <i data-lucide="user-round" class="h-8 w-8"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900"><?= escape_html($username) ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?= escape_html($roleLabel) ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 font-bold text-slate-900">Changer le mot de passe</h2>
        <form method="post" class="grid gap-4 sm:grid-cols-2">
            <?php csrf_field(); ?>

            <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                Mot de passe actuel
                <input type="password" name="current_password" required
                       autocomplete="current-password"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2.5">
            </label>

            <label class="text-sm font-medium text-slate-700">
                Nouveau mot de passe
                <input type="password" name="new_password" required minlength="6"
                       maxlength="72" autocomplete="new-password"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2.5">
            </label>

            <label class="text-sm font-medium text-slate-700">
                Confirmer le mot de passe
                <input type="password" name="confirm_password" required minlength="6"
                       maxlength="72" autocomplete="new-password"
                       class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2.5">
            </label>

            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 sm:col-span-2">
                Enregistrer
            </button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
