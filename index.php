<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth.php";

$patients = (int) $pdo->query("SELECT COUNT(*) FROM patient")->fetchColumn();
$consultations = (int) $pdo->query("SELECT COUNT(*) FROM consultation")->fetchColumn();
$prescriptions = (int) $pdo->query("SELECT COUNT(*) FROM prescription")->fetchColumn();

$pageTitle = "Tableau de bord";
require_once __DIR__ . "/includes/header.php";
?>

<div class="mx-auto max-w-5xl space-y-5">
    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <h1 class="text-xl font-bold text-slate-900">Tableau de bord</h1>
        <p class="mt-1 text-sm text-slate-500">Gestion de l'hôpital</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>"
           class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Patients</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700"><?= $patients ?></p>
        </a>

        <a href="<?= escape_html(url("/consultations/liste.php")) ?>"
           class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Consultations</p>
            <p class="mt-2 text-3xl font-bold text-blue-700"><?= $consultations ?></p>
        </a>

        <a href="<?= escape_html(url("/prescriptions/liste.php")) ?>"
           class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Prescriptions</p>
            <p class="mt-2 text-3xl font-bold text-violet-700"><?= $prescriptions ?></p>
        </a>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="font-bold text-slate-900">Actions rapides</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="<?= escape_html(url("/patients/ajouter.php")) ?>"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                Ajouter un patient
            </a>
            <a href="<?= escape_html(url("/consultations/ajouter.php")) ?>"
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                Nouvelle consultation
            </a>
        </div>
    </section>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
