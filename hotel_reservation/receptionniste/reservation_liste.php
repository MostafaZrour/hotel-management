<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation=?");
    $stmt->execute([$id]);
    $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      Réservation supprimée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
    header("location:reservation_liste.php");
    exit();
}

$tab = [];
$sql = "SELECT * FROM reservation
        INNER JOIN client ON reservation.id_client=client.id_client
        INNER JOIN chambre ON reservation.id_chambre=chambre.id_chambre WHERE 1=1";

if (isset($_GET["recherche"])) {
    if (!empty($_GET['etat'])) {
        $etat = $_GET['etat'];
        $sql .= ' AND etat=?';
        $tab[] = $etat;
    }

    if (!empty($_GET['client'])) {
        $client = $_GET['client'];
        $sql .= ' AND reservation.id_client=?';
        $tab[] = $client;
    }

    if (!empty($_GET['chambre'])) {
        $chambre = $_GET['chambre'];
        $sql .= ' AND reservation.id_chambre=?';
        $tab[] = $chambre;
    }

    if (!empty($_GET['data_arrive']) && !empty($_GET['date_depart'])) {
        $data_arrive = $_GET['data_arrive'];
        $date_depart = $_GET['date_depart'];
        $sql .= ' AND date_arrivee >= ? AND date_depart <= ?';
        $tab[] = $data_arrive;
        $tab[] = $date_depart;
    }
}

$sqlState = $pdo->prepare($sql);
$sqlState->execute($tab);
$reservations = $sqlState->fetchAll(PDO::FETCH_OBJ);
?>
<div class="container mt-5">
    <?php 
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>
    <h2 class="w-100 text-center my-4">Liste de Réservation</h2>
    <div class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modal"><i class="fa fa-search"></i></div>
    <div class="modal fade" id="modal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <form action="" method="get">
                        <h6 class="mb-2">Date d'arrivéé :</h6>
                        <input type="date" name="data_arrive" class="form-control my-2 text-center"
                                value="<?= isset($_GET['data_arrive']) ? $_GET['data_arrive'] : '' ?>" placeholder="Du">
                        <h6 class="mb-2">Date départ :</h6>
                        <input type="date" name="date_depart" class="form-control my-2 text-center"
                            value="<?= isset($_GET['date_depart']) ? $_GET['date_depart'] : '' ?>" placeholder="Au">
                        <h6 class="mb-2">Etat :</h6>
                        <select name="etat" class="form-control my-2 text-center">
                            <option value="">Choissir...</option>
                            <option value="Planifiée" <?= (isset($_GET['etat']) && $_GET['etat'] == 'Planifiée') ? "selected" : "" ?>>Planifiée</option>
                            <option value="En cours" <?= (isset($_GET['etat']) && $_GET['etat'] == 'En cours') ? "selected" : "" ?>>En cours</option>
                            <option value="Terminée" <?= (isset($_GET['etat']) && $_GET['etat'] == 'Terminée') ? "selected" : "" ?>>Terminée</option>
                        </select>
                        <?php
                        $stmt1 = $pdo->prepare("SELECT * FROM client");
                        $stmt1->execute();
                        $clients = $stmt1->fetchAll(PDO::FETCH_OBJ);
                        ?>
                        <h6 class="mb-2">Client :</h6>
                        <select name="client" class="form-control my-2 text-center">
                            <option value="">Choissir...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client->id_client ?>" <?= (isset($_GET['client']) && $_GET['client'] == $client->id_client) ? "selected" : "" ?>>
                                    <?= $client->nom_complet ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        $stmt2 = $pdo->prepare("SELECT * FROM chambre");
                        $stmt2->execute();
                        $chambres = $stmt2->fetchAll(PDO::FETCH_OBJ);
                        ?>
                        <h6 class="mb-2">Nombre Chambre :</h6>
                        <select name="chambre" class="form-control my-2 text-center">
                            <option value="">Choissir...</option>
                            <?php foreach ($chambres as $chambre): ?>
                                <option value="<?= $chambre->id_chambre ?>" <?= (isset($_GET['chambre']) && $_GET['chambre'] == $chambre->id_chambre) ? "selected" : "" ?>>
                                    <?= $chambre->numero_chambre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary w-100" name="recherche">Rechercher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <table class="table text-center mt-5">
        <thead>
            <tr>
                <th>Code réservation</th>
                <th>Date réservation</th>
                <th>Date d'arrivée</th>
                <th>Date de départ</th>
                <th>Nbr jours</th>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Prix Total</th>
                <th>Chambre</th>
                <th>Etat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= $reservation->code_reservation ?></td>
                    <td><?= $reservation->date_heure_reservation ?></td>
                    <td><?= $reservation->date_arrivee ?></td>
                    <td><?= $reservation->date_depart ?></td>
                    <td><?= $reservation->nbr_jours ?></td>
                    <td><?= $reservation->nom_complet ?></td>
                    <td><?= $reservation->telephone ?></td>
                    <td><?= $reservation->montant_total ?></td>
                    <td><?= $reservation->numero_chambre ?></td>
                    <td><?= $reservation->etat ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a data-bs-toggle="modal" href="#modalid<?= $reservation->id_reservation ?>" style="color:blue"><i class="fa fa-eye"></i></a>
                            <a href="modifier_res.php?id=<?= $reservation->id_reservation ?>" style="color:black "><i class="fa fa-edit"></i></a>
                            <a href="?supprimer=<?= $reservation->id_reservation ?>" style="color:red"><i class="fa fa-trash"></i></a>
                        </div>
                        <div class="modal fade" id="modalid<?= $reservation->id_reservation ?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="clientModalLabel">Reservation Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table text-start table-bordered">
                                            <tr>
                                                <th>Reservation ID</th>
                                                <td><?= $reservation->id_reservation ?></td>
                                            </tr>
                                            <tr>
                                                <th>Code Reservation</th>
                                                <td><?= $reservation->code_reservation ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date Heure</th>
                                                <td><?= $reservation->date_heure_reservation ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date Arrivée</th>
                                                <td><?= $reservation->date_arrivee ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date Départ</th>
                                                <td><?= $reservation->date_depart ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nombre Jours</th>
                                                <td><?= $reservation->nbr_jours ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nombre Adultes Enfants</th>
                                                <td><?= $reservation->nbr_adultes_enfants ?></td>
                                            </tr>
                                            <tr>
                                                <th>Montant Total</th>
                                                <td><?= $reservation->montant_total ?></td>
                                            </tr>
                                            <tr>
                                                <th>Etat</th>
                                                <td><?= $reservation->etat ?></td>
                                            </tr>
                                            <tr>
                                                <th>Id Client</th>
                                                <td><?= $reservation->id_client ?></td>
                                            </tr>
                                            <tr>
                                                <th>Id Chambre</th>
                                                <td><?= $reservation->id_chambre ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- /#page-content-wrapper -->
<?php require_once "../includes/footer.php" ?>