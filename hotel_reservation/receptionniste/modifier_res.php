<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo "<div class='container mt-5'>";

if (isset($_GET['id'])) {
    $id_reservation = $_GET['id'];
    $stmt = $pdo->prepare("SELECT reservation.*, chambre.*, type_chambre.type_chambre 
                           FROM reservation 
                           INNER JOIN chambre ON reservation.id_chambre = chambre.id_chambre 
                           INNER JOIN type_chambre ON chambre.id_type_ch = type_chambre.id_type_ch 
                           WHERE id_reservation = ?");
    $stmt->execute([$id_reservation]);
    $reservation = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$reservation) {
        $_SESSION['message'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Réservation non trouvée.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: reservation_liste.php");
        exit;
    }
} else {
    header("Location: reservation_liste.php");
    exit;
}

if (isset($_POST['recherche'])) {
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $type_chambre = $_POST['type_chambre'] ?? "";
    $nbr_personnes = $_POST['nbr_personnes'];
    $user = $_POST['client'];

    if (!empty($type_chambre) && !empty($date_arrivee) && !empty($date_depart) && !empty($nbr_personnes) && !empty($user)) {
        $sql = "SELECT * FROM chambre 
        INNER JOIN type_chambre ON type_chambre.id_type_ch = chambre.id_type_ch 
        WHERE chambre.id_type_ch = ? 
        AND chambre.id_chambre NOT IN ( SELECT id_chambre FROM reservation WHERE (date_arrivee <= ? AND date_depart >= ?) AND id_reservation != ? ) 
        AND chambre.nombre_adultes_enfants_ch >= ?";
        $params = [$type_chambre, $date_depart, $date_arrivee, $id_reservation, $nbr_personnes];

        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $chambres = $statement->fetchAll(PDO::FETCH_OBJ);

        if ($statement->rowCount() == 0) {
            $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Cette Chambre n\'est pas disponible maintenant.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
    } else {
        $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Tous les champs sont obligatoires.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
    }
}

if (isset($_POST['modifier'])) {
    $date_arrivee = $_POST['date_arrivee'];
    $date_depart = $_POST['date_depart'];
    $nbr_personnes = $_POST['nbr_personnes'];
    $user = $_POST['client'];
    $chambre = $_POST['chambre'];

    if (!empty($date_arrivee) && !empty($date_depart) && !empty($nbr_personnes) && !empty($user) && !empty($chambre)) {
        $timestamp_arrivee = strtotime($date_arrivee);
        $timestamp_depart = strtotime($date_depart);

        $nbr_jours = floor(($timestamp_depart - $timestamp_arrivee) / (60 * 60 * 24));

        $s = $pdo->prepare('SELECT n_prix_nuit FROM chambre INNER JOIN tarif_chambre ON tarif_chambre.id_tarif = chambre.id_tarif WHERE id_chambre=?');
        $s->execute([$chambre]);
        $tarif = $s->fetch(PDO::FETCH_OBJ);

        $prix_nuit = $tarif->n_prix_nuit;

        $montant_total = $prix_nuit * $nbr_jours;

        $statement = $pdo->prepare("UPDATE reservation SET date_arrivee = ?, date_depart = ?, nbr_jours = ?, nbr_adultes_enfants = ?, montant_total = ?, id_client = ?, id_chambre = ? WHERE id_reservation = ?");
        $statement->execute([$date_arrivee, $date_depart, $nbr_jours, $nbr_personnes, $montant_total, $user, $chambre, $id_reservation]);

        $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        La réservation a été modifiée avec succès.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: reservation_liste.php");
        exit;
    } else {
        $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Tous les champs sont obligatoires.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
    }
}

$stmt1 = $pdo->prepare("SELECT * FROM client");
$stmt1->execute();
$clients = $stmt1->fetchAll(PDO::FETCH_OBJ);

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<legend>Modifier la Réservation :</legend>
<form action="" method="post">
    <h6 class="form-label mt-3">Client:</h6>
    <select name="client" class="form-control mt-2">
        <?php foreach ($clients as $client): ?>
            <option value="<?= $client->id_client ?>" <?php echo $reservation->id_client == $client->id_client ? "selected" : "" ?>>
                <?= $client->nom_complet ?>
            </option>
        <?php endforeach; ?>
    </select>
    <h6 class="form-label mt-3">Date d'arrivée:</h6>
    <input type="date" class="form-control mt-2" name="date_arrivee" value="<?= $reservation->date_arrivee ?>">
    <h6 class="form-label mt-3">Date de départ:</h6>
    <input type="date" class="form-control mt-2" name="date_depart" value="<?= $reservation->date_depart ?>">
    <h6 for="" class="form-label mt-3">Type Chambre :</h6>
    <select name="type_chambre" class="form-control mt-2">
        <option value="">Choisir...</option>
        <?php
        $stmtTypes = $pdo->prepare("SELECT * FROM type_chambre");
        $stmtTypes->execute();
        $types = $stmtTypes->fetchAll(PDO::FETCH_OBJ);
        foreach ($types as $type) {
            ?>
            <option value="<?= $type->id_type_ch ?>" <?php echo $reservation->id_type_ch == $type->id_type_ch ? "selected" : "" ?>>
                <?= $type->type_chambre ?>
            </option>
            <?php
        }
        ?>
    </select>
    <h6 for="" class="form-label mt-3">Nombre de personnes : </h6>
    <input type="number" class="form-control my-2 mx-1" id="roomQuantity" placeholder="Nombre des personnes" name="nbr_personnes" value="<?= $reservation->nbr_adultes_enfants ?>" min="1">
    <button class="btn btn-primary w-100 mt-2" name="recherche" type="submit">Rechercher une nouvelle chambre</button>
</form>
<a href="reservation_liste.php" class="btn btn-secondary w-100 my-3">Retour à la Liste des Réservations</a>

<?php if (isset($chambres) && !empty($chambres)): ?>
    <div class="row mt-5">
        <?php foreach ($chambres as $chambre): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?= $chambre->photo ?>" alt="chambre photo" class="card-img img-fluid">
                    <div class="card-body">
                        <h5 class="card-title">Chambre N°<?= $chambre->numero_chambre ?></h5>
                        <p class="card-text"><strong>Type:</strong> <?= $chambre->type_chambre ?></p>
                        <p class="card-text"><strong>Capacité:</strong> <?= $chambre->nombre_adultes_enfants_ch ?> personnes</p>
                        <p class="card-text"><strong>Etage :</strong> <?= $chambre->etage_chambre ?></p>
                        <form action="" method="post" style="border:none">
                            <button class="btn btn-primary w-100" type="submit" name="modifier">Choisir cette chambre</button>
                            <input type="hidden" name="chambre" value="<?= $chambre->id_chambre ?>">
                            <input type="hidden" name="date_arrivee" value="<?= $date_arrivee ?>">
                            <input type="hidden" name="date_depart" value="<?= $date_depart ?>">
                            <input type="hidden" name="nbr_personnes" value="<?= $nbr_personnes ?>">
                            <input type="hidden" name="client" value="<?= $user ?>">
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <h6 class="mt-3">Chambre actuelle :</h6>
    <div class="card mb-4 w-50">
        <img src="<?= $reservation->photo ?>" alt="chambre photo" class="card-img img-fluid">
        <div class="card-body">
            <h5 class="card-title">Chambre N°<?= $reservation->numero_chambre ?></h5>
            <p class="card-text"><strong>Type:</strong> <?= $reservation->type_chambre ?></p>
            <p class="card-text"><strong>Capacité:</strong> <?= $reservation->nombre_adultes_enfants_ch ?> personnes</p>
            <p class="card-text"><strong>Etage :</strong> <?= $reservation->etage_chambre ?></p>
            <form action="" method="post" style="border:none">
                <button class="btn btn-primary w-100" type="submit" name="modifier">Conserver cette chambre</button>
                <input type="hidden" name="chambre" value="<?= $reservation->id_chambre ?>">
                <input type="hidden" name="date_arrivee" value="<?= $reservation->date_arrivee ?>">
                <input type="hidden" name="date_depart" value="<?= $reservation->date_depart ?>">
                <input type="hidden" name="nbr_personnes" value="<?= $reservation->nbr_personnes ?>">
                <input type="hidden" name="client" value="<?= $reservation->id_client ?>">
            </form>
        </div>
    </div>
<?php endif; ?>

</div>
<?php include_once "../includes/footer.php" ?>
