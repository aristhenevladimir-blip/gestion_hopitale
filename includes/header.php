<?php
require_once __DIR__ . "/../config/base.php";
start_app_session();
require_once __DIR__ . "/csrf.php";
require_once __DIR__ . "/formatters.php";
$currentPath = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH) ?: "/";
$username = $_SESSION["user"]["username"] ?? "";
$role = $_SESSION["user"]["role"] ?? "";

function nav_active($href, $currentPath) {
    return parse_url($href, PHP_URL_PATH) === $currentPath
        ? "bg-emerald-50 text-emerald-700 border-l-2 border-emerald-600"
        : "text-slate-600 hover:bg-slate-100 border-l-2 border-transparent";
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Hôpital — <?= escape_html(isset($pageTitle) ? $pageTitle : "Consultations patients") ?></title>
    <link rel="icon" href="<?= escape_html(url("/assets/img/logo.png")) ?>">
    <script src="<?= escape_html(url("/assets/vendor/tailwindcss.js")) ?>"></script>
    <script src="<?= escape_html(url("/assets/vendor/lucide.min.js")) ?>"></script>
    <style>
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:#f1f5f9}
        .sidebar-scroll::-webkit-scrollbar{width:4px}
        .sidebar-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:2px}
        @media(max-height:560px){#sidebar .sidebar-decoration-container{padding-top:.5rem;padding-bottom:.5rem}#sidebar .sidebar-decoration{width:6.5rem}}
        main{animation:fadeIn .25s ease-out}
        @keyframes fadeIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
    </style>
</head>
<body class="text-slate-800 min-h-screen">
<div class="flex min-h-screen">
    <button id="hamburgerBtn" onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white shadow border border-slate-200">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="lg:hidden hidden fixed inset-0 bg-black/40 z-30"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-slate-200 flex flex-col sidebar-scroll overflow-y-auto transform -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-200">
            <img src="<?= escape_html(url("/assets/img/logo.png")) ?>" alt="Logo" class="w-9 h-9 rounded-md object-cover">
            <div>
                <div class="font-semibold text-slate-900 text-sm">Gestion Hôpital</div>
                <div class="text-xs text-slate-500">Plateforme médicale</div>
            </div>
        </div>
        <nav class="flex-1 px-3 py-3 space-y-1">
            <?php
            $links = [
                ["href" => url("/index.php"), "icon" => "layout-dashboard", "label" => "Tableau de bord", "sub" => false],
                ["href" => url("/patients/ajouter.php"), "icon" => "user-plus", "label" => "Ajouter patient", "sub" => false],
                ["href" => url("/patients/rechercher.php"), "icon" => "search", "label" => "Rechercher", "sub" => false],
                ["href" => url("/consultations/liste.php"), "icon" => "list", "label" => "Liste des consultations", "sub" => true],
                ["href" => url("/profil.php"), "icon" => "user", "label" => "Profil", "sub" => false],
            ];
            if ($role === "admin") $links[] = ["href" => url("/utilisateurs.php"), "icon" => "users", "label" => "Utilisateurs", "sub" => false];
            foreach ($links as $l):
                $active = nav_active($l["href"], $currentPath);
                $indent = !empty($l["sub"]) ? "pl-8" : "px-3";
                $subLine = !empty($l["sub"]) ? "<span class='mr-1 text-slate-300 text-xs'>└</span>" : ""; ?>
            <a href="<?= escape_html($l["href"]) ?>" class="flex items-center gap-2 <?= $indent ?> py-2 rounded-md text-sm font-medium transition <?= $active ?>">
                <?= $subLine ?><i data-lucide="<?= escape_html($l["icon"]) ?>" class="w-4 h-4 shrink-0"></i><?= escape_html($l["label"]) ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="px-3 py-3 border-t border-slate-200">
            <a href="<?= escape_html(url("/logout.php")) ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50 transition">
                <i data-lucide="log-out" class="w-4 h-4"></i>Déconnexion
            </a>
        </div>
        <div class="sidebar-decoration-container flex shrink-0 items-center justify-center px-4 pt-3 pb-5">
            <img src="<?= escape_html(url("/assets/images/decoration_ecg_croix.png")) ?>" alt="" aria-hidden="true" class="sidebar-decoration h-auto w-40 max-w-full object-contain opacity-30">
        </div>
    </aside>

    <div class="flex-1 lg:ml-60 flex flex-col min-w-0">
        <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
            <div class="flex items-center justify-between px-6 py-3 gap-4 pl-16 lg:pl-6">
                <div class="flex-1 max-w-md">
                    <form action="<?= escape_html(url("/patients/rechercher.php")) ?>" method="get" class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="search" name="code" placeholder="Rechercher par code ou nom..." class="w-full pl-10 pr-4 py-2 rounded-md border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    </form>
                </div>
                <div class="text-sm text-slate-500 hidden sm:block">
                    <?= (int)date("H") >= 6 && (int)date("H") < 18 ? "Bonjour" : "Bonsoir" ?>, <span class="font-semibold text-slate-900"><?= escape_html($username) ?></span>
                </div>
            </div>
        </header>

        <?php if (!empty($_SESSION["msg"])): ?>
        <?php $flashType = $_SESSION["msg_type"] ?? "";
              $flashClass = $flashType === "error" ? "bg-red-100 text-red-700 border-l-4 border-red-500" : "bg-green-100 text-green-700 border-l-4 border-green-500"; ?>
        <div class="px-6 pt-4"><div class="px-4 py-3 rounded-md text-sm font-medium <?= $flashClass ?>"><?= escape_html($_SESSION["msg"]) ?></div></div>
        <?php unset($_SESSION["msg"], $_SESSION["msg_type"]); endif; ?>

        <main class="flex-1 p-6">
<script>
function toggleSidebar(){var s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay');s.classList.toggle('-translate-x-full');o.classList.toggle('hidden');}
function confirmAction(message,url){document.getElementById('confirmMsg').textContent=message;document.getElementById('confirmForm').action=url;document.getElementById('confirmModal').classList.remove('hidden');}
function closeConfirm(){document.getElementById('confirmModal').classList.add('hidden');}
</script>
<div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">Confirmation</h3>
        <p id="confirmMsg" class="text-slate-600 text-sm mb-4"></p>
        <form id="confirmForm" method="post" class="flex gap-3 justify-end">
            <?php csrf_field(); ?>
            <button type="button" onclick="closeConfirm()" class="px-4 py-2 text-sm rounded-md bg-slate-100 hover:bg-slate-200 transition">Annuler</button>
            <button type="submit" class="px-4 py-2 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white transition">Confirmer</button>
        </form>
    </div>
</div>
