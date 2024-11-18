<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo "<div class='container'>";

$params = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM reservation
        INNER JOIN chambre ON chambre.id_chambre = reservation.id_chambre
        INNER JOIN client ON client.id_client = reservation.id_client WHERE chambre.id_chambre = ?";
    $params[] = $id;
} else {
    header("location:ajouter_ch.php");
    exit();
}

if (isset($_POST["recherche"])) {

    if (!empty($_POST['date_arrivee']) && !empty($_POST['date_depart'])) {
    
        $sql .= " AND ((reservation.date_arrivee BETWEEN ? AND ?) OR (reservation.date_depart BETWEEN ? AND ?))";
        $params[] = $_POST['date_arrivee'];
        $params[] = $_POST['date_depart'];
        $params[] = $_POST['date_arrivee'];
        $params[] = $_POST['date_depart'];
    }
}

$sqlStmt = $pdo->prepare($sql);
$sqlStmt->execute($params);
$histories = $sqlStmt->fetchAll(PDO::FETCH_OBJ);
?>

<form method="post" class="mt-5">
    <input type="hidden" name="id" value="<?= $_GET['id'] ?? "" ?>">
        <div class="row">
            <div class="col-6">
                <label>Du:</label>
                <input type="date" class="form-control" name="date_arrivee" value="<?= $_POST['date_arrivee'] ?? '' ?>">
            </div>
            <div class="col-6">
                <label>Au:</label>
                <input type="date" class="form-control" name="date_depart" value="<?= $_POST['date_depart'] ?? '' ?>">
            </div>
        </div>
        <button name="recherche" class="btn btn-primary w-100 mt-3">Recherche</button>
    </form>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Date de réservation</th>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Date d’arrivée</th>
                <th>Date de départ</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($histories as $history): ?>
                <tr>
                    <td><?= $history->date_heure_reservation ?></td>
                    <td><?= $history->nom_complet ?></td>
                    <td><?= $history->telephone ?></td>
                    <td><?= $history->date_arrivee ?></td>
                    <td><?= $history->date_depart ?></td>
                    <td><?= $history->montant_total ?> DH</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<?php require_once "../includes/footer.php"; ?>
