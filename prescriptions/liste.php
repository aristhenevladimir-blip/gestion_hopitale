<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/formatters.php";
$pageTitle = "Prescriptions";
require_once __DIR__ . "/../includes/header.php";

$no = (int) ($_GET["no"] ?? 0);
$prescriptions = [];
$consultation = null;
$perPage = 10;
$page = max(1, (int) ($_GET["page"] ?? 1));
$totalPrescriptions = 0;

if ($no > 0) {
    $stmt = $pdo->prepare('SELECT c.*, p.Nom, p.Prenom, p.Id_Patient FROM consultation c
      JOIN patient p ON p.Id_Patient = c.Id_Patient WHERE c.no_Consultation = ?');
    $stmt->execute([$no]);
    $consultation = $stmt->fetch();
    if ($consultation) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prescription WHERE no_consultation = ?");
        $stmt->execute([$no]);
        $totalPrescriptions = (int) $stmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalPrescriptions / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM prescription WHERE no_consultation = ? ORDER BY prescription LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $no, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $prescriptions = $stmt->fetchAll();
    }
}

$recentConsults = [];
if ($no <= 0 || !$consultation) {
    $recentConsults = $pdo->query('SELECT c.no_Consultation, c.Date_Consultation, c.Nom_Prenom_medecin, c.Diagnostique, p.Nom, p.Prenom
      FROM consultation c JOIN patient p ON p.Id_Patient = c.Id_Patient ORDER BY c.no_Consultation DESC LIMIT 10')->fetchAll();
}
?>
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">
        <?php if ($consultation): ?>
        Prescriptions — Consultation n°<?= (int) $consultation["no_Consultation"] ?> — <?= escape_html($consultation["Nom"] . " " . $consultation["Prenom"]) ?> — <?= format_date_fr($consultation["Date_Consultation"]) ?>
        <?php else: ?>Prescriptions d'une consultation<?php endif; ?>
    </h1>
    <p class="text-slate-500 text-sm mb-6">Consultez, ajoutez ou modifiez le texte des prescriptions liées à une consultation.</p>
    <form method="get" action="" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6 flex gap-3">
        <input type="number" name="no" value="<?= $no > 0 ? $no : "" ?>" placeholder="N° de consultation (ex : 1)" required class="flex-1 px-3 py-2 rounded-md border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition">Afficher</button>
    </form>
    <?php if ($consultation): ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
            <div class="text-sm space-y-1">
                <div><span class="text-slate-500">Patient :</span> <span class="font-medium text-slate-900"><?= escape_html($consultation["Nom"] . " " . $consultation["Prenom"]) ?></span> (code #<?= (int) $consultation["Id_Patient"] ?>)</div>
                <div><span class="text-slate-500">Médecin :</span> <span class="font-medium"><?= escape_html($consultation["Nom_Prenom_medecin"] ?: "—") ?></span></div>
                <div><span class="text-slate-500">Date :</span> <span class="font-medium"><?= format_date_fr($consultation["Date_Consultation"]) ?></span></div>
                <div><span class="text-slate-500">Diagnostic :</span> <span class="font-medium"><?= escape_html($consultation["Diagnostique"] ?: "—") ?></span></div>
            </div>
            <div class="flex gap-2">
                <a href="<?= escape_html(url("/prescriptions/ajouter.php?no=" . (int) $consultation["no_Consultation"])) ?>" class="px-3 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition">Ajouter une prescription</a>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Prescriptions</h2>
        <?php if (count($prescriptions) > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-slate-500 border-b border-slate-200"><th class="pb-3 font-medium">Prescription</th><th class="pb-3 font-medium">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($prescriptions as $pr): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 whitespace-pre-wrap break-words"><?= escape_html($pr["prescription"]) ?></td>
                        <td class="py-3 space-x-3">
                            <a href="<?= escape_html(url("/prescriptions/modifier.php?no=" . $no . "&texte=" . rawurlencode($pr["prescription"]))) ?>" class="text-emerald-600 hover:underline">Modifier</a>
                            <?php if (is_admin()): ?>
                            <a href="#" onclick="confirmAction('Supprimer cette prescription ?', '<?= escape_html(url("/prescriptions/supprimer.php?no=" . $no . "&texte=" . rawurlencode($pr["prescription"]))) ?>'); return false;" class="text-red-600 hover:underline">Supprimer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPrescriptions > $perPage): $totalPages = (int) ceil($totalPrescriptions / $perPage); ?>
        <div class="flex justify-center gap-2 mt-4">
            <?php for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? "bg-emerald-600 text-white" : "bg-slate-100 text-slate-700 hover:bg-slate-200"; ?>
            <a href="?no=<?= $no ?>&page=<?= $i ?>" class="px-3 py-1.5 text-sm rounded-md transition <?= $active ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="text-center py-8">
            <p class="text-slate-500 text-sm mb-3">Aucune prescription enregistrée pour cette consultation.</p>
            <a href="<?= escape_html(url("/prescriptions/ajouter.php?no=" . (int) $consultation["no_Consultation"])) ?>" class="inline-flex items-center px-3 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition">Ajouter une prescription</a>
        </div>
        <?php endif; ?>
    </div>
    <?php elseif ($no > 0): ?>
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 text-center">
        <p class="text-slate-700 font-medium">Aucune consultation avec ce numéro.</p>
        <p class="text-sm text-slate-500 mt-1">Vérifiez le numéro saisi ou choisissez une consultation ci-dessous.</p>
    </div>
    <?php endif; ?>
    <?php if (($no <= 0 || !$consultation) && !empty($recentConsults)): ?>
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-slate-200 p-5">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Dernières consultations</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-slate-500 border-b border-slate-200"><th class="pb-3 font-medium">N°</th><th class="pb-3 font-medium">Date</th><th class="pb-3 font-medium">Patient</th><th class="pb-3 font-medium">Médecin</th><th class="pb-3 font-medium">Action</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentConsults as $c): ?>
                    <tr>
                        <td class="py-3 font-medium">#<?= (int) $c["no_Consultation"] ?></td>
                        <td class="py-3"><?= format_date_fr($c["Date_Consultation"]) ?></td>
                        <td class="py-3"><?= escape_html($c["Nom"] . " " . $c["Prenom"]) ?></td>
                        <td class="py-3"><?= escape_html($c["Nom_Prenom_medecin"] ?: "—") ?></td>
                        <td class="py-3"><a href="<?= escape_html(url("/prescriptions/liste.php?no=" . (int) $c["no_Consultation"])) ?>" class="text-emerald-600 hover:underline font-medium">Voir le traitement</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
