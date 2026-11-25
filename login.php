<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/base.php";
require_once __DIR__ . "/includes/csrf.php";
start_app_session();
if (!empty($_SESSION["user"])) { header("Location: " . url("/index.php")); exit(); }

$erreur = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify()) {
        $erreur = "Token CSRF invalide. Rechargez la page.";
    } else {
        $username = isset($_POST["username"]) && is_string($_POST["username"]) ? trim($_POST["username"]) : "";
        $password = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user["password_hash"])) {
            if (($user["role"] ?? "") === "disabled") { $erreur = "Ce compte est désactivé."; }
            else {
                session_regenerate_id(true);
                $_SESSION["user"] = ["id" => $user["id_utilisateur"], "username" => $user["username"], "role" => $user["role"]];
                $_SESSION["last_login"] = date("Y-m-d H:i:s");
                try {
                    require_once __DIR__ . "/includes/activity.php";
                    ensure_activity_log_table($pdo);
                    $pdo->prepare("UPDATE utilisateur SET derniere_connexion = NOW() WHERE id_utilisateur = ?")->execute([$user["id_utilisateur"]]);
                    log_activity($pdo, "connexion", "Connexion réussie");
                } catch (Exception $e) {}
                header("Location: " . url("/index.php")); exit();
            }
        } else { $erreur = "Identifiants incorrects."; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Hôpital</title>
    <script src="<?= htmlspecialchars(url("/assets/vendor/tailwindcss.js"), ENT_QUOTES, "UTF-8") ?>"></script>
    <style>body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif}</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="text-center mb-6">
                <img src="<?= htmlspecialchars(url("/assets/img/logo.png"), ENT_QUOTES, "UTF-8") ?>" alt="Logo" class="w-16 h-16 mx-auto mb-2 rounded-lg">
                <h1 class="text-2xl font-bold text-slate-900">Connexion</h1>
                <p class="text-slate-500 text-sm">Gestion des consultations - Hôpital</p>
            </div>
            <?php if ($erreur): ?>
            <div class="mb-4 px-4 py-3 rounded-md bg-red-100 text-red-700 text-sm font-medium"><?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8") ?></div>
            <?php endif; ?>
            <form method="post" class="space-y-4">
                <?php csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom d'utilisateur</label>
                    <input type="text" name="username" required autofocus class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Se connecter</button>
            </form>
            <p class="mt-4 text-center text-xs text-slate-500">Compte par défaut : <strong>admin</strong> / <strong>admin123</strong></p>
        </div>
    </div>
</body>
</html>
