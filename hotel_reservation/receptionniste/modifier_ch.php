<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

if (isset($_GET['id'])) {
    $id_chambre = $_GET['id'];
    
    $sql_select = $pdo->prepare('SELECT * FROM chambre WHERE id_chambre = ?');
    $sql_select->execute([$id_chambre]);
    $chambre = $sql_select->fetch(PDO::FETCH_OBJ);

    if (isset($_POST['modifier'])) {
        $numero_chambre = $_POST['numero_chambre'];
        $nombre_adultes_enfants_ch = $_POST['nombre_adultes_enfants_ch'];
        $renfort_chambre = isset($_POST['renfort_chambre']) && $_POST['renfort_chambre'] === 'oui' ? 1 : 0;
        $etage_chambre = $_POST['etage_chambre'];
        $nbr_lits_chambre = $_POST['nbr_lits_chambre'];
        $type = $_POST['type'];
        $capacite = $_POST['capacite'];
        $tarif = $_POST['tarif'];
        $photo = $chambre->photo;

        if($_FILES['photo']['error'] !== UPLOAD_ERR_OK){
            $photo_path = $photo ;
        }else{
            $photo_tmp = $_FILES['photo']['tmp_name'];
            $photo_name = $_FILES['photo']['name'];
            $photo_path = "../image_ch/" . $photo_name;
        }

        if (!empty($numero_chambre) && !empty($nombre_adultes_enfants_ch) && !empty($etage_chambre) && !empty($nbr_lits_chambre) && !empty($type) && !empty($capacite) && !empty($tarif)) {

                if (move_uploaded_file($photo_tmp, $photo_path)) {
                    $photo = $photo_path;
                }

            $sql_update = $pdo->prepare('UPDATE chambre SET numero_chambre=?, nombre_adultes_enfants_ch=?, renfort_chambre=?, etage_chambre=?, nbr_lits_chambre=?, id_type_ch=?, id_capacite=?, id_tarif=?, photo=? WHERE id_chambre=?');
            $sql_update->execute([$numero_chambre, $nombre_adultes_enfants_ch, $renfort_chambre, $etage_chambre, $nbr_lits_chambre, $type, $capacite, $tarif, $photo, $id_chambre]);

            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show mt-5" role="alert">
                                      Chambre modifiée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
            header('location:ajouter_ch.php');
            exit();
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
                                      Tous les champs sont obligatoires.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }


}
$sql_select = $pdo->prepare('SELECT * FROM chambre WHERE id_chambre = ?');
$sql_select->execute([$id_chambre]);
$chambre = $sql_select->fetch(PDO::FETCH_OBJ);
?>

<h2 class="text-center m-5">Modifier Chambre</h2>
<div class="container">
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>
    <div>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <h6 class="mt-2" class="form-label">Numero:</h6>
                <input type="number" name="numero_chambre" class="form-control" value="<?= $chambre->numero_chambre ?>">
            </div>
            <div class="form-group">
                <h6 class="mt-2" class="form-label">Nombre personne</h6>
                <input type="number" name="nombre_adultes_enfants_ch" class="form-control" value="<?= $chambre->nombre_adultes_enfants_ch ?>">
            </div>
            <div class="form-group">
                <h6 class="mt-2" class="form-label">Etage</h6>
                <input type="number" name="etage_chambre" class="form-control" value="<?= $chambre->etage_chambre ?>">
            </div>
            <div class="form-group">
                <h6 class="mt-2" class="form-label">Nombre Lits</h6>
                <input type="number" name="nbr_lits_chambre" class="form-control" value="<?= $chambre->nbr_lits_chambre ?>">
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
                            <option value="">Choisir...</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type->id_type_ch ?>" <?= ($type->id_type_ch == $chambre->id_type_ch) ? 'selected' : '' ?>>
                                    <?= $type->type_chambre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <h6 class="mt-3">Renfort</h6>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="renfort_chambre" value="oui" class="form-check-input" <?= ($chambre->renfort_chambre == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label">Oui</label><br>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="renfort_chambre" value="non" class="form-check-input" <?= ($chambre->renfort_chambre == 0) ? 'checked' : '' ?>>
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
                    <option value="">Choisir...</option>
                    <?php foreach ($capacities as $capacitie): ?>
                        <option value="<?= $capacitie->id_capacite ?>" <?= ($capacitie->id_capacite == $chambre->id_capacite) ? 'selected' : '' ?>><?= $capacitie->titre_capacite ?></option>
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
                    <option value="">Choisir...</option>
                    <?php foreach ($tarifs as $tarif): ?>
                        <option value="<?= $tarif->id_tarif ?>" <?= ($tarif->id_tarif == $chambre->id_tarif) ? 'selected' : '' ?>><?= $tarif->n_prix_nuit ?> DH </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <h6 class="mt-2" class="form-label">Photo</h6>
                <input type="file" name="photo" class="form-control">
            </div>
            <img src="../image_ch/<?= $chambre->photo ?>" alt="<?= $chambre->photo ?>" class="img-thumbnail mt-2" width="200">
            <div class="form-group text-center mt-3">
                <button type="submit" name="modifier" class="btn btn-primary w-100">Modifier</button>
            </div>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
