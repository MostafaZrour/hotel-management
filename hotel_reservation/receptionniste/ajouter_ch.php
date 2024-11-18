<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo '<div class="container">';

if (isset($_GET["supprimer"])) {
    $id = (int)$_GET["supprimer"];


    $stmt = $pdo->prepare("SELECT * FROM reservation WHERE id_chambre = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {

        $_SESSION['message'] = '<div class="alert alert-warning alert-dismissible fade show mt-5" role="alert">
                                  Opération interdite : chambre déjà un objet de réservation.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {

        $stmt = $pdo->prepare("DELETE FROM chambre WHERE id_chambre = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show mt-5" role="alert">
                                      Chambre supprimée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                      Erreur lors de la suppression de la chambre.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }
    header("Location: ajouter_ch.php");
    exit();
}

if (isset($_POST['ajouter'])) {

    $numero_chambre = $_POST['numero_chambre'];
    $nombre_adultes_enfants_ch = $_POST['nombre_adultes_enfants_ch'];
    $renfort_chambre = isset($_POST['renfort_chambre']) && $_POST['renfort_chambre'] === 'oui' ? 1 : 0;
    $etage_chambre = $_POST['etage_chambre'];
    $nbr_lits_chambre = $_POST['nbr_lits_chambre'];
    $type = $_POST['type'];
    $capacite = $_POST['capacite'];
    $tarif = $_POST['tarif'];


    if (!empty($numero_chambre) && !empty($nombre_adultes_enfants_ch) && !empty($etage_chambre) && !empty($nbr_lits_chambre) && !empty($type) && !empty($capacite) && !empty($tarif) && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {


        $photo_nom = $_FILES['photo']['name'];
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $upload_path = "../image_ch/";
        $upload_photo = $upload_path . $photo_nom;


        if (move_uploaded_file($photo_tmp, $upload_photo)) {

            $sql = $pdo->prepare('INSERT INTO chambre VALUES(NULL,?,?,?,?,?,?,?,?,?)');
            $sql->execute([$numero_chambre, $nombre_adultes_enfants_ch, $renfort_chambre, $etage_chambre, $nbr_lits_chambre, $upload_photo, $type, $capacite, $tarif]);

            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show mt-5" role="alert">
                                      Chambre ajoutée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            header('location:ajouter_ch.php');
            exit();
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                      Erreur lors de l\'upload du fichier.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                      Tous les champs sont obligatoires.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
    }


    header('location:ajouter_ch.php');
    exit();
}
?>

<?php
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<h2 class="text-center m-5">Ajouter Chambre</h2>
<div>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <h6 class="mt-2" class="form-label">Numero:</h6>
            <input type="number" name="numero_chambre" class="form-control">
        </div>
        <div class="form-group">
            <h6 class="mt-2" class="form-label">Nombre personne</h6>
            <input type="number" name="nombre_adultes_enfants_ch" class="form-control">
        </div>
        <div class="form-group">
            <h6 class="mt-2" class="form-label">Etage</h6>
            <input type="number" name="etage_chambre" class="form-control">
        </div>
        <div class="form-group">
            <h6 class="mt-2" class="form-label">Nombre Lits</h6>
            <input type="number" name="nbr_lits_chambre" class="form-control">
        </div>
        <div class="form-group">
            <?php
            $s1 = $pdo->prepare("SELECT * FROM type_chambre");
            $s1->execute();
            $types = $s1->fetchAll(PDO::FETCH_OBJ);
            ?>
            <div class="row">
                <div class="col-9">
                    <h6 class="mt-2" class="form-label">Type:</h6>
                    <select name="type" class="form-control">
                        <option value="">Choissiser...</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type->id_type_ch ?>"><?= $type->type_chambre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-3">
                    <h6 class="mt-3">Renfort</h6>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="renfort_chambre" value="oui" class="form-check-input">
                        <label class="form-check-label">Oui</label><br>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="renfort_chambre" value="non" class="form-check-input">
                        <label for="renfort_chambre_non" class="form-check-label">Non</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <?php
            $s2 = $pdo->prepare("SELECT * FROM capacite_chambre");
            $s2->execute();
            $capacities = $s2->fetchAll(PDO::FETCH_OBJ);
            ?>
            <h6 class="mt-2" class="form-label">Capacite:</h6>
            <select name="capacite" class="form-control">
                <option value="">Choissiser...</option>
                <?php foreach ($capacities as $capacitie): ?>
                    <option value="<?= $capacitie->id_capacite ?>"><?= $capacitie->titre_capacite ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <?php
            $s3 = $pdo->prepare("SELECT * FROM tarif_chambre");
            $s3->execute();
            $tarifs = $s3->fetchAll(PDO::FETCH_OBJ);
            ?>
            <h6 class="mt-2" class="form-label">Tarif:</h6>
            <select name="tarif" class="form-control">
                <option value="">Choissiser...</option>
                <?php foreach ($tarifs as $tarif): ?>
                    <option value="<?= $tarif->id_tarif ?>"><?= $tarif->n_prix_nuit ?> DH </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row my-2">
            <div class="col-12">
                <h6 class="mt-2" class="form-label">Photo</h6>
                <input type="file" name="photo" class="form-control">
            </div>
        </div>
        <div class="form-group text-center mt-3">
            <button type="submit" name="ajouter" class="btn btn-primary w-100">Ajouter</button>
        </div>
    </form>
</div>
</div>
<?php
$sql = "
                   SELECT *
                   FROM 
                       chambre ch
                   JOIN 
                       type_chambre tc ON ch.id_type_ch = tc.id_type_ch
                   JOIN 
                       tarif_chambre tf ON ch.id_tarif = tf.id_tarif
                   JOIN 
                       capacite_chambre cc ON ch.id_capacite = cc.id_capacite WHERE 1=1";
$params = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (!empty($_GET['room_number'])) {
        $sql .= " AND ch.numero_chambre LIKE ?";
        $params[] = '%' . $_GET['room_number'] . '%';
    }

    if (!empty($_GET['room_type'])) {
        $sql .= " AND tc.type_chambre LIKE ?";
        $params[] = '%' . $_GET['room_type'] . '%';
    }


    if (!empty($_GET['capacity'])) {
        $sql .= " AND cc.titre_capacite = ?";
        $params[] = $_GET['capacity'];
    }


    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $sql .= " AND ch.id_chambre NOT IN (
                    SELECT id_chambre FROM reservation
                    WHERE date_arrivee <= ?
                    AND date_depart >= ?
                )";
        $params[] = $_GET['end_date'];
        $params[] = $_GET['start_date'];
    }


    if (!empty($_GET['min_price'])) {
        $sql .= " AND tf.n_prix_nuit >= ?";
        $params[] = $_GET['min_price'];
    }
    if (!empty($_GET['max_price'])) {
        $sql .= " AND tf.n_prix_nuit <= ?";
        $params[] = $_GET['max_price'];
    }
}

$sqlState = $pdo->prepare($sql);
$sqlState->execute($params);
$chambres = $sqlState->fetchAll(PDO::FETCH_OBJ);
?>

<div class="container">
    <h1 class="my-4 text-center">Liste Des Chambres</h1>
    <button data-bs-toggle="modal" data-bs-target="#recherche" class="my-3 btn btn-primary w-100"><i class="fa fa-search"></i></button>
    <table class="table text-center" id="table">
        <thead>
            <tr>
                <th>Numéro chambre</th>
                <th>Type</th>
                <th>Prix Nuit</th>
                <th>Prix passage</th>
                <th>Capacité</th>
                <th>Nombre lits</th>
                <th>Etage</th>
                <th>Nbr personnes</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chambres as $chambre): ?>
                <tr>
                    <td class="align-middle"><?= $chambre->numero_chambre ?></td>
                    <td class="align-middle"><?= $chambre->type_chambre ?></td>
                    <td class="align-middle"><?= $chambre->prix_base_nuit ?></td>
                    <td class="align-middle"><?= $chambre->prix_base_passage ?></td>
                    <td class="align-middle"><?= $chambre->titre_capacite ?></td>
                    <td class="align-middle"><?= $chambre->nbr_lits_chambre ?></td>
                    <td class="align-middle"><?= $chambre->etage_chambre ?></td>
                    <td class="align-middle"><?= $chambre->nombre_adultes_enfants_ch ?></td>
                    <td class="align-middle"><img src="<?= $chambre->photo ?>" alt="Photo de <?= $chambre->photo ?>" class="img-fluid" width="100"></td>
                    <td class="align-middle">
                        <div class="d-flex gap-1">
                            <a href="historique_ch.php?id=<?= $chambre->id_chambre ?>" class="mx-2" style="color:green">
                                <i class="fa fa-history"></i>
                            </a>
                            <a data-bs-toggle="modal" data-bs-target="#modalid<?= $chambre->id_chambre ?>" style="color:blue;">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="modifier_ch.php?id=<?= $chambre->id_chambre ?>" class="mx-2" style="color:black">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <a href="?supprimer=<?= $chambre->id_chambre ?>" style="color:red">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>
                        <div class="modal fade" id="modalid<?= $chambre->id_chambre ?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="w-100 text-center" id="clientModalLabel">Chambre Information</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="<?= $chambre->photo ?>" alt="<?= $chambre->photo ?>" class="img-thumbnail img-fluid" width="100%">
                                        <table class="table table-bordered text-start">
                                            <tr>
                                                <th>Chambre ID</th>
                                                <td><?= $chambre->id_chambre ?></td>
                                            </tr>
                                            <tr>
                                                <th>Numéro de la Chambre</th>
                                                <td><?= $chambre->numero_chambre ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nombre d'Adultes et Enfants</th>
                                                <td><?= $chambre->nombre_adultes_enfants_ch ?></td>
                                            </tr>
                                            <tr>
                                                <th>Renfort Chambre</th>
                                                <td><?= $chambre->renfort_chambre ? 'Oui' : 'Non' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Étage de la Chambre</th>
                                                <td><?= $chambre->etage_chambre ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nombre de Lits</th>
                                                <td><?= $chambre->nbr_lits_chambre ?></td>
                                            </tr>
                                            <tr>
                                                <th>Type de Chambre</th>
                                                <td><?= $chambre->type_chambre ?> (<?= $chambre->description_type ?>)</td>
                                            </tr>
                                            <tr>
                                                <th>Capacité</th>
                                                <td><?= $chambre->titre_capacite ?> (Numéro: <?= $chambre->numero_capacite ?>)</td>
                                            </tr>
                                            <tr>
                                                <th>Prix de Nuit</th>
                                                <td><?= $chambre->prix_base_nuit ?> EUR</td>
                                            </tr>
                                            <tr>
                                                <th>Prix de Passage</th>
                                                <td><?= $chambre->prix_base_passage ?> EUR</td>
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
</div>
</div>
<br><br><br><br><br>
<?php require_once "../includes/footer.php" ?>
<div class="modal fade" id="recherche" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="w-100 text-center" id="clientModalLabel">Client Information</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form my-4">
                    <h6 class="mt-2" for="room_number">Numéro de chambre:</h6>
                    <input type="text" class="form-control" id="room_number" name="room_number"
                        value="<?= isset($_GET['room_number']) ? $_GET['room_number'] : '' ?>">
                    <h6 class="mt-2" for="room_type">Type de chambre:</h6>
                    <select class="form-control" id="room_type" name="room_type">
                        <option value="">Choissiser...</option>
                        <?php

                        $sqlType = "SELECT * FROM type_chambre";
                        $stmtType = $pdo->prepare($sqlType);
                        $stmtType->execute();
                        $types = $stmtType->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($types as $type) {
                            echo '<option value="' . $type['type_chambre'] . '"' . (isset($_GET['type']) && $_GET['type'] == $type['type_chambre'] ? ' selected' : '') . '>' . $type['type_chambre'] . '</option>';
                        }
                        ?>
                    </select>
                    <h6 class="mt-2" for="capacity">Capacité:</h6>
                    <select class="form-control" id="capacity" name="capacity">
                        <option value="">Choissiser...</option>
                        <?php

                        $sqlCapacity = "SELECT * FROM capacite_chambre";
                        $stmtCapacity = $pdo->prepare($sqlCapacity);
                        $stmtCapacity->execute();
                        $capacities = $stmtCapacity->fetchAll(PDO::FETCH_OBJ);
                        foreach ($capacities as $capacity) {
                            echo '<option value="' . $capacity->titre_capacite . '"' . (isset($_GET['capacity']) && $_GET['capacity'] == $capacity->titre_capacite ? ' selected' : '') . '>' . $capacity->titre_capacite . '</option>';
                        }
                        ?>
                    </select>
                    <h6 class="mt-2" for="start_date">Du:</h6>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                        value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>">

                    <h6 class="mt-2" for="end_date">Au:</h6>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                        value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>">

                    <h6 class="mt-2" for="min_price">Prix Min:</h6>
                    <input type="number" class="form-control" id="min_price" name="min_price"
                        value="<?= isset($_GET['min_price']) ? $_GET['min_price'] : '' ?>">
                    <h6 class="mt-2" for="max_price">Prix Max:</h6>
                    <input type="number" class="form-control" id="max_price" name="max_price"
                        value="<?= isset($_GET['max_price']) ? $_GET['max_price'] : '' ?>">
                    <button class="btn btn-primary mt-2 mt-md-0 w-100" type="submit">Rechercher</button>
            </div>
            </form>
        </div>
    </div>
</div>
</div>