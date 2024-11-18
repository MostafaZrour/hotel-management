<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo "<div class='container mt-5'>";

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
            AND chambre.id_chambre NOT IN ( SELECT id_chambre FROM reservation WHERE (date_arrivee <= ? AND date_depart >= ?) ) 
            AND chambre.nombre_adultes_enfants_ch >= ?";
            $params = [$type_chambre, $date_depart, $date_arrivee, $nbr_personnes];

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

    if (isset($_POST['ajouter'])) {
        
        $user = $_POST['client1'];
        $chambre = $_POST['chambre'];
        $date_arrivee = $_POST['date_arrivee'];
        $date_depart = $_POST['date_depart'];
        $nbr_personnes = $_POST['nbr_personnes'];


        $stmtCode = $pdo->prepare("SELECT MAX(id_reservation) AS max_id FROM reservation");
        $stmtCode->execute();
        $result = $stmtCode->fetch(PDO::FETCH_OBJ);
        $next_id = $result->max_id + 1;
        $code_reservation = "RES" . str_pad($next_id, 3, "0", STR_PAD_LEFT);

        $date_heure_reservation = date("Y-m-d H:i:s");

        $timestamp_arrivee = strtotime($date_arrivee);
        $timestamp_depart = strtotime($date_depart);

        $nbr_jours = floor(($timestamp_depart - $timestamp_arrivee) / (60 * 60 * 24));

        $s = $pdo->prepare('SELECT prix_base_nuit FROM chambre INNER JOIN tarif_chambre ON tarif_chambre.id_tarif = chambre.id_tarif WHERE id_chambre=?');
        $s->execute([$chambre]);
        $tarif = $s->fetch(PDO::FETCH_OBJ);

        $prix_nuit = $tarif->prix_base_nuit;

        $montant_total = $prix_nuit * $nbr_jours;


        $statement = $pdo->prepare("INSERT INTO reservation VALUES(NULL,?,?,?,?,?,?,?, NULL,?,?)");
        $statement->execute([$code_reservation, $date_heure_reservation, $date_arrivee, $date_depart, $nbr_jours, $nbr_personnes, $montant_total, 
        $user, $chambre]);

        $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        L'insertion a été effectuée avec succès.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: reservation_liste.php");
        exit;
    }

$stmt1 = $pdo->prepare("SELECT * FROM client");
$stmt1->execute();
$clients = $stmt1->fetchAll(PDO::FETCH_OBJ);

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<legend>Ajouter un Réservation :</legend>
<form action="" method="post">
    <h6 class="form-label mt-3">Client:</h6>
    <select name="client" class="form-control mt-2">
        <option value="">Choisir...</option>
        <?php foreach ($clients as $client): ?>
            <option value="<?= $client->id_client ?>" <?php echo isset($_POST['client']) && $_POST['client'] == $client->id_client ? "selected" : "" ?>>
                <?= $client->nom_complet ?>
            </option>
        <?php endforeach; ?>
    </select>
    <h6 class="form-label mt-3">Date d’arrivée:</h6>
    <input type="date" class="form-control mt-2" name="date_arrivee" value="<?php echo isset($date_arrivee) ? $date_arrivee : "" ?>">
    <h6 class="form-label mt-3">Date de départ:</h6>
    <input type="date" class="form-control mt-2" name="date_depart" value="<?php echo isset($date_depart) ? $date_depart : "" ?>">
    <h6 for="" class="form-label mt-3">Type Chambre :</h6>
    <select name="type_chambre" class="form-control mt-2">
        <option value="">Choisir...</option>
        <?php
        $stmtTypes = $pdo->prepare("SELECT * FROM type_chambre");
        $stmtTypes->execute();
        $types = $stmtTypes->fetchAll(PDO::FETCH_OBJ);
        foreach ($types as $type) {
            ?>
            <option value="<?= $type->id_type_ch ?>" <?php echo isset($_POST['type_chambre']) && $_POST['type_chambre'] == $type->id_type_ch ? "selected" : "" ?>>
                <?= $type->type_chambre ?>
            </option>
            <?php
        }
        ?>
    </select>
    <h6 for="" class="form-label mt-3">Nombre de personnes : </h6>
    <input type="number" class="form-control my-2 mx-1" id="roomQuantity" placeholder="Nombre des personnes" name="nbr_personnes" value="<?php echo isset($nbr_personnes) ? $nbr_personnes : "" ?>" min="1">
    <button class="btn btn-success w-100 mt-2" name="recherche" type="submit">Rechercher</button>
</form>
<a href="reservation_liste.php" class="btn btn-primary w-100 my-3">Liste de Reservation</a>
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
                            <button class="btn btn-primary w-100" type="submit" name="ajouter">Réserver</button>
                            <input type="hidden" name="chambre" value="<?= $chambre->id_chambre ?>">
                            <input type="hidden" name="date_arrivee" value="<?= $date_arrivee ?>">
                            <input type="hidden" name="date_depart" value="<?= $date_depart ?>">
                            <input type="hidden" name="nbr_personnes" value="<?= $nbr_personnes ?>">
                            <input type="hidden" name="client1" value="<?= $user ?>">
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>
<?php include_once "../includes/footer.php" ?>
