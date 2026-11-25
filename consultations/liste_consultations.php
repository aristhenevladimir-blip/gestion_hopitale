<?php
require_once __DIR__ . '/../includes/consultation_schema.php';
require_once __DIR__ . '/../includes/patient_schema.php';
$host = 'localhost'; $dbname = 'gestion_hopital'; $username = 'root'; $password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
$stmt = $pdo->query("SELECT c.id_consultation, c.date_consultation, c.diagnostic,
       p.id_patient, p.nom AS patient_nom, p.prenom AS patient_prenom,
       m.nom AS medecin_nom, m.prenom AS medecin_prenom
FROM consultation c JOIN patient p ON c.id_patient = p.id_patient
JOIN medecin m ON c.id_medecin = m.id_medecin ORDER BY c.date_consultation DESC");
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Hôpital – Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .hero { background: url('/assets/img/medecin_pose.png') center/cover no-repeat; color: #fff; padding: 4rem 0; }
        .hero h1 { font-weight: 300; }
        .card-table { border-radius: .75rem; overflow:hidden; }
        .table th, .table td { vertical-align: middle; }
        .action-btn { min-width: 80px; }
    </style>
</head>
<body>
    <section class="hero text-center">
        <div class="container">
            <h1 class="display-4">Consultations médicales</h1>
            <p class="lead">Gestion des consultations enregistrées dans le système.</p>
            <a href="ajouter_consultation.php" class="btn btn-primary btn-lg">+ Nouvelle consultation</a>
        </div>
    </section>
    <main class="container my-5">
        <div class="mb-4">
            <h2 class="h4">Toutes les consultations</h2>
            <p class="text-muted">Liste complète des consultations, triée par date décroissante.</p>
        </div>
        <?php if (count($consultations) > 0): ?>
        <div class="card card-table shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th>#ID</th><th>Date &amp; Heure</th><th>Patient</th><th>Médecin</th><th>Diagnostic</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultations as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_consultation']) ?></td>
                            <td><?= htmlspecialchars($row['date_consultation']) ?></td>
                            <td><?= htmlspecialchars($row['patient_prenom'] . ' ' . $row['patient_nom']) ?></td>
                            <td>Dr. <?= htmlspecialchars($row['medecin_prenom'] . ' ' . $row['medecin_nom']) ?></td>
                            <td><?= htmlspecialchars(substr($row['diagnostic'], 0, 60)) ?>...</td>
                            <td class="text-center">
                                <a href="voir_consultation.php?id=<?= $row['id_consultation'] ?>" class="btn btn-sm btn-outline-info action-btn" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="modifier_consultation.php?id=<?= $row['id_consultation'] ?>" class="btn btn-sm btn-outline-warning action-btn" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <a href="supprimer_consultation.php?id=<?= $row['id_consultation'] ?>" class="btn btn-sm btn-outline-danger action-btn" onclick="return confirm('Êtes‑vous sûr de vouloir supprimer cette consultation ?');" title="Supprimer"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center" role="alert">Aucune consultation enregistrée pour le moment.</div>
        <?php endif; ?>
    </main>
    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center"><small>&copy; <?= date('Y') ?> Gestion Hôpital – Tous droits réservés.</small></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
