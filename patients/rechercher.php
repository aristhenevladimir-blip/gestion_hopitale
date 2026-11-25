<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/formatters.php";

function dossier_date($value) {
    if (!$value) return "—";
    $rawDate = substr((string) $value, 0, 10);
    if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $rawDate, $m)) return "—";
    if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) return "—";
    $ts = strtotime($rawDate);
    return $ts ? date("d/m/Y", $ts) : "—";
}

$pageTitle = "Dossiers patients";
$search = is_string($_GET["code"] ?? null) ? trim($_GET["code"]) : "";
$isSearching = $search !== "";
$showList = (isset($_GET["liste"]) && (string) $_GET["liste"] === "1") || $isSearching;
$page = max(1, (int) ($_GET["page"] ?? 1));
$perPage = 10;
$patient = null;
$patientId = 0;
$patientNotFound = false;
$patients = [];
$patientCount = 0;
$filterSql = "";
$filterParams = [];

if ($search !== "" && preg_match('/^#?\s*(?:PAT[\s-]*)?(\\d+)$/i', $search, $matches)) {
    $patientId = (int) $matches[1];
    $showList = false;
    if ($patientId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM patient WHERE Id_Patient = ?");
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    $patientNotFound = !$patient;
}

if (!$patient && $isSearching && !$patientNotFound) {
    $showList = true;
    $conditions = [];
    if (isset($matches[1])) {
        $conditions[] = "Id_Patient = ?";
        $filterParams[] = (int) $matches[1];
    } else {
        $conditions[] = "(Nom LIKE ? OR Prenom LIKE ? OR CONCAT(Nom, ' ', Prenom) LIKE ? OR CONCAT(Prenom, ' ', Nom) LIKE ?)";
        $term = "%" . $search . "%";
        array_push($filterParams, $term, $term, $term, $term);
    }
    if ($conditions) $filterSql = "WHERE " . implode(" AND ", $conditions);
}

if (!$patient && $showList) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM patient " . $filterSql);
    $countStmt->execute($filterParams);
    $patientCount = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($patientCount / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $listStmt = $pdo->prepare("SELECT p.Id_Patient, p.Nom, p.Prenom, p.Sexe, p.Tel, (SELECT MAX(c.Date_Consultation) FROM consultation c WHERE c.Id_Patient = p.Id_Patient) AS Derniere_consultation FROM patient p $filterSql ORDER BY p.Nom, p.Prenom, p.Id_Patient LIMIT ? OFFSET ?");
    foreach ($filterParams as $index => $value) {
        $listStmt->bindValue($index + 1, $value, PDO::PARAM_STR);
    }
    $listStmt->bindValue(count($filterParams) + 1, $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(count($filterParams) + 2, $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $patients = $listStmt->fetchAll(PDO::FETCH_ASSOC);
}

$consultationCount = 0;
$latestConsultation = null;
$consultations = [];
$historyPage = $page;
$historyTotalPages = 1;
if ($patient) {
    $patientId = (int) $patient["Id_Patient"];
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM consultation WHERE Id_Patient = ?");
    $countStmt->execute([$patientId]);
    $consultationCount = (int) $countStmt->fetchColumn();
    $latestStmt = $pdo->prepare("SELECT no_Consultation, Date_Consultation, Diagnostique, Nom_Prenom_medecin FROM consultation WHERE Id_Patient = ? ORDER BY Date_Consultation DESC, no_Consultation DESC LIMIT 1");
    $latestStmt->execute([$patientId]);
    $latestConsultation = $latestStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $historyTotalPages = max(1, (int) ceil($consultationCount / $perPage));
    $historyPage = min($page, $historyTotalPages);
    $offset = ($historyPage - 1) * $perPage;
    $historyStmt = $pdo->prepare("SELECT c.no_Consultation, c.Date_Consultation, c.Nom_Prenom_medecin, c.Telephone, c.Poids, c.Taille_cm, c.Diagnostique FROM consultation c WHERE c.Id_Patient = ? ORDER BY c.Date_Consultation DESC, c.no_Consultation DESC LIMIT ? OFFSET ?");
    $historyStmt->bindValue(1, $patientId, PDO::PARAM_INT);
    $historyStmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $historyStmt->bindValue(3, $offset, PDO::PARAM_INT);
    $historyStmt->execute();
    $consultations = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="mx-auto max-w-7xl space-y-4">
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                <i data-lucide="<?= $patient ? "folder-heart" : "folders" ?>" class="h-5 w-5"></i>
            </span>
            <div>
                <h1 class="text-xl font-bold text-slate-900"><?= $patient ? "Dossier médical" : "Dossiers patients" ?></h1>
                <p class="text-sm text-slate-600"><?= $patient ? "Informations personnelles et historique des soins" : "Recherchez un patient ou consultez les dossiers enregistrés" ?></p>
            </div>
        </div>
        <?php if (!$patient): ?>
        <a href="<?= escape_html(url("/patients/ajouter.php")) ?>" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            <i data-lucide="user-round-plus" class="h-4 w-4"></i> Ajouter un patient
        </a>
        <?php endif; ?>
    </section>

    <form method="get" action="<?= escape_html(url("/patients/rechercher.php")) ?>" role="search" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
        <label for="patientDirectorySearch" class="sr-only">Rechercher par code, nom ou prénom</label>
        <div class="relative min-w-0 flex-1">
            <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
            <input id="patientDirectorySearch" type="search" name="code" value="<?= escape_html($search) ?>" placeholder="Code patient, nom ou prénom…" class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            <i data-lucide="search" class="h-4 w-4"></i> Rechercher
        </button>
        <?php if ($isSearching): ?>
        <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Effacer</a>
        <?php endif; ?>
    </form>

    <?php if ($patient):
        require __DIR__ . "/_detail.php";
    elseif ($showList):
        require __DIR__ . "/_liste.php";
    elseif ($patientNotFound): ?>
    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5" role="status">
        <div class="flex items-start gap-3">
            <i data-lucide="circle-help" class="mt-0.5 h-5 w-5 shrink-0 text-amber-700"></i>
            <div>
                <h2 class="font-semibold text-amber-900">Aucun patient trouvé pour ce code</h2>
                <p class="mt-1 text-sm text-amber-800">Vérifiez le code ou recherchez le patient par son nom.</p>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
        <i data-lucide="search-check" class="mx-auto h-8 w-8 text-emerald-600"></i>
        <h2 class="mt-2 text-base font-semibold text-slate-900">Rechercher un dossier médical</h2>
        <p class="mt-1 text-sm text-slate-600">Saisissez un code patient, un nom ou un prénom.</p>
        <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <i data-lucide="users-round" class="h-4 w-4"></i> Consulter tous les dossiers
        </a>
    </section>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
