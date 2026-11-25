<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/header.php';
$pageTitle = 'Liste des consultations';

try {
    $consultations = $pdo->query("SELECT c.no_Consultation, c.Date_Consultation, c.Diagnostique,
            c.Nom_Prenom_medecin, c.Poids, c.Taille_cm, p.Id_Patient, p.Nom, p.Prenom
     FROM consultation c JOIN patient p ON p.Id_Patient = c.Id_Patient
     ORDER BY c.Date_Consultation DESC, c.no_Consultation DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $consultations = [];
    $loadError = 'Erreur lors du chargement des consultations.';
}
$total = count($consultations);
$patientsUniques = count(array_unique(array_column($consultations, 'Id_Patient')));
$moisActuel = date('Y-m');
$duMois = count(array_filter($consultations, fn($c) => str_starts_with($c['Date_Consultation'], $moisActuel)));
?>
<div class="relative overflow-hidden rounded-2xl mb-8"
     style="background: linear-gradient(135deg,#0f766e 0%,#059669 60%,#065f46 100%); min-height:160px;">
    <div class="absolute inset-0 opacity-10"
         style="background-image:url('<?= escape_html(url('/assets/img/banniere_hopital.png')) ?>');background-size:cover;background-position:center;background-color:#0f766e;"></div>
    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-8 py-8">
        <div class="flex items-center gap-4">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3"><i data-lucide="stethoscope" class="w-7 h-7 text-white"></i></div>
            <div>
                <h1 class="text-2xl font-bold text-white">Liste des consultations</h1>
                <p class="text-emerald-100 text-sm mt-0.5">Historique complet des consultations médicales</p>
            </div>
        </div>
        <a href="<?= escape_html(url('/consultations/ajouter.php')) ?>" class="flex items-center gap-2 bg-white text-emerald-700 font-semibold text-sm px-5 py-2.5 rounded-xl shadow hover:bg-emerald-50 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>Nouvelle consultation
        </a>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="bg-emerald-100 rounded-lg p-2.5"><i data-lucide="clipboard-list" class="w-5 h-5 text-emerald-700"></i></div>
        <div><div class="text-2xl font-bold text-slate-900"><?= $total ?></div><div class="text-xs text-slate-500 mt-0.5">Consultations totales</div></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="bg-blue-100 rounded-lg p-2.5"><i data-lucide="users" class="w-5 h-5 text-blue-700"></i></div>
        <div><div class="text-2xl font-bold text-slate-900"><?= $patientsUniques ?></div><div class="text-xs text-slate-500 mt-0.5">Patients uniques</div></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="bg-violet-100 rounded-lg p-2.5"><i data-lucide="calendar-check" class="w-5 h-5 text-violet-700"></i></div>
        <div><div class="text-2xl font-bold text-slate-900"><?= $duMois ?></div><div class="text-xs text-slate-500 mt-0.5">Ce mois-ci</div></div>
    </div>
</div>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800 text-sm">Toutes les consultations</h2>
        <div class="relative w-full sm:w-64">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
            <input id="tableSearch" type="text" placeholder="Filtrer…" class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
    </div>
    <?php if (!empty($loadError)): ?>
    <div class="px-6 py-4 text-red-600 text-sm flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i><?= escape_html($loadError) ?></div>
    <?php elseif (empty($consultations)): ?>
    <div class="flex flex-col items-center justify-center py-20 text-slate-400">
        <i data-lucide="folder-open" class="w-12 h-12 mb-3 opacity-40"></i>
        <p class="text-sm font-medium">Aucune consultation enregistrée pour le moment.</p>
        <a href="<?= escape_html(url('/consultations/ajouter.php')) ?>" class="mt-4 text-sm text-emerald-600 hover:underline">Ajouter la première consultation →</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm" id="consultationsTable">
            <thead>
                <tr class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">
                    <th class="px-6 py-3 text-left font-medium">#</th>
                    <th class="px-6 py-3 text-left font-medium">Date</th>
                    <th class="px-6 py-3 text-left font-medium">Patient</th>
                    <th class="px-6 py-3 text-left font-medium">Médecin</th>
                    <th class="px-6 py-3 text-left font-medium">Diagnostic</th>
                    <th class="px-6 py-3 text-left font-medium">Poids / Taille</th>
                    <th class="px-6 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="tableBody">
                <?php foreach ($consultations as $c):
                    $dateF = '-';
                    if (!empty($c['Date_Consultation'])) {
                        try { $dateF = (new DateTime($c['Date_Consultation']))->format('d/m/Y'); }
                        catch (Exception $ex) { $dateF = escape_html($c['Date_Consultation']); }
                    }
                    $nomP = escape_html(trim($c['Nom'] . ' ' . $c['Prenom']));
                    $nomM = $c['Nom_Prenom_medecin'] ?? '—';
                    $prefixM = preg_match('/^dr\.?\s/i', $nomM) ? '' : 'Dr ';
                    $medecin = escape_html($prefixM . $nomM);
                    $diag = escape_html($c['Diagnostique'] ?? '—');
                    $diagCourt = mb_strlen($c['Diagnostique'] ?? '') > 50
                        ? escape_html(mb_substr($c['Diagnostique'], 0, 50)) . '…' : $diag;
                    $mesures = [];
                    if (!empty($c['Poids'])) $mesures[] = $c['Poids'] . ' kg';
                    if (!empty($c['Taille_cm'])) $mesures[] = $c['Taille_cm'] . ' cm';
                    $mes = $mesures ? implode(' / ', $mesures) : '—';
                    $no = escape_html($c['no_Consultation']);
                    $idP = (int) $c['Id_Patient'];
                ?>
                <tr class="hover:bg-emerald-50/40 transition table-row">
                    <td class="px-6 py-3"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-xs"><?= $no ?></span></td>
                    <td class="px-6 py-3 text-slate-600 whitespace-nowrap"><div class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i><?= $dateF ?></div></td>
                    <td class="px-6 py-3">
                        <a href="<?= escape_html(url('/patients/rechercher.php?code=' . $idP)) ?>" class="flex items-center gap-2 text-slate-800 font-medium hover:text-emerald-700 transition">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-600 font-semibold text-xs"><?= mb_strtoupper(mb_substr($c['Prenom'] ?? 'P', 0, 1) . mb_substr($c['Nom'] ?? '', 0, 1)) ?></span>
                            <?= $nomP ?>
                        </a>
                    </td>
                    <td class="px-6 py-3 text-slate-600"><div class="flex items-center gap-1.5"><i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400"></i><?= $medecin ?></div></td>
                    <td class="px-6 py-3 text-slate-600 max-w-xs" title="<?= $diag ?>"><?= $diagCourt ?></td>
                    <td class="px-6 py-3 text-slate-500 text-xs whitespace-nowrap"><?= escape_html($mes) ?></td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= escape_html(url('/consultations/modifier.php?no=' . $no)) ?>" title="Modifier" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                            <button type="button" title="Supprimer" onclick="confirmAction('Supprimer la consultation #<?= $no ?> ?', '<?= escape_html(url('/consultations/supprimer.php?no=' . $no)) ?>')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-400">
        <span id="rowCount"><?= $total ?> consultation<?= $total > 1 ? 's' : '' ?></span>
        <span>Trié par date décroissante</span>
    </div>
    <?php endif; ?>
</div>
<script>
(function(){
    const input=document.getElementById('tableSearch'),tbody=document.getElementById('tableBody'),counter=document.getElementById('rowCount');
    if(!input||!tbody)return;
    input.addEventListener('input',function(){
        const q=this.value.trim().toLowerCase();let v=0;
        tbody.querySelectorAll('tr.table-row').forEach(function(r){const s=!q||r.textContent.toLowerCase().includes(q);r.style.display=s?'':'none';if(s)v++;});
        if(counter)counter.textContent=v+' consultation'+(v>1?'s':'');
    });
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
