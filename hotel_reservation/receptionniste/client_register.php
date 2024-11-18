<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

$nom_complet = '';
$date_debut = '';
$date_fin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = $_POST['nom_complet'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';

    $query = "
    SELECT
        client.id_client,
        client.nom_complet,
        client.sexe,
        client.age,
        reservation.date_arrivee,
        reservation.date_depart,
        chambre.numero_chambre,
        reservation.montant_total
    FROM
        client
    JOIN
        reservation ON client.id_client = reservation.id_client
    JOIN
        chambre ON reservation.id_chambre = chambre.id_chambre
    WHERE 1=1";

    $params = [];

    if (!empty($nom_complet)) {
        $query .= " AND client.nom_complet LIKE ?";
        $params[] = "%$nom_complet%";
    }

    if (!empty($date_debut)) {
        $query .= " AND reservation.date_arrivee >= ?";
        $params[] = $date_debut;
    }

    if (!empty($date_fin)) {
        $query .= " AND reservation.date_depart <= ?";
        $params[] = $date_fin;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
} else {
    $query = "
    SELECT
        client.id_client,
        client.nom_complet,
        client.sexe,
        client.age,
        reservation.date_arrivee,
        reservation.date_depart,
        chambre.numero_chambre,
        reservation.montant_total
    FROM
        client
    JOIN
        reservation ON client.id_client = reservation.id_client
    JOIN
        chambre ON reservation.id_chambre = chambre.id_chambre;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>
<div class="container mt-5">
    <h2 class="text-center mb-4">Registre des clients</h2>

    <form method="POST" style="border:none" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="nom_complet" class="form-label">Nom complet</label>
            <input type="text" id="nom_complet" name="nom_complet" class="form-control" value="<?= htmlspecialchars($nom_complet) ?>">
        </div>
        <div class="col-md-3">
            <label for="date_debut" class="form-label">Date de début</label>
            <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
        </div>
        <div class="col-md-3">
            <label for="date_fin" class="form-label">Date de fin</label>
            <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Rechercher</button>
        </div>
    </form>

    <button type="button" class="btn btn-secondary" onclick="printDiv('printableArea')">Imprimer le registre</button>
    <div id="printableArea">
        <div class="d-flex justify-content-end align-items-center mb-4">
        </div>

        <div class="print-header">
            <p>Date : le <?= date('d/m/Y') ?></p>
            <p>Période : Du <?= htmlspecialchars($date_debut) ?> au <?= htmlspecialchars($date_fin) ?></p>
        </div>

        <table class="table text-center" id="table">
            <thead>
                <tr>
                    <th>ID client</th>
                    <th>Nom complet</th>
                    <th>Sexe</th>
                    <th>Age</th>
                    <th>Date d'arrivée</th>
                    <th>Date de départ</th>
                    <th>Numéro de Chambre</th>
                    <th>Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client->id_client) ?></td>
                    <td><?= htmlspecialchars($client->nom_complet) ?></td>
                    <td><?= htmlspecialchars($client->sexe) ?></td>
                    <td><?= htmlspecialchars($client->age) ?></td>
                    <td><?= htmlspecialchars($client->date_arrivee) ?></td>
                    <td><?= htmlspecialchars($client->date_depart) ?></td>
                    <td><?= htmlspecialchars($client->numero_chambre) ?></td>
                    <td><?= htmlspecialchars($client->montant_total) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        .print-header {
            display: block;
            margin-bottom: 20px;
        }
        .btn, form, .d-flex {
            display: none !important;
        }
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            margin: 0;
        }
        @page {
            size: landscape;
            margin: 1cm;
        }
    }
</style>

<script>
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }
</script>

<?php include_once "../includes/footer.php"; ?>