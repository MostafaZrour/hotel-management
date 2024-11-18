<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";
?>
<?php
echo "<div class='container mt-5'>";
$message = "";

$id_tarif = isset($_GET['modifier']) ? (int)$_GET['modifier'] : "";
$prix_base_nuit = "";
$prix_base_passage = "";
$n_prix_nuit = "";
$n_prix_passage = "";

if ($id_tarif) {
    $sql = "SELECT * FROM tarif_chambre WHERE id_tarif = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tarif]);
    $tarif = $stmt->fetch(PDO::FETCH_OBJ);

    if ($tarif) {
        $prix_base_nuit = $tarif->prix_base_nuit;
        $prix_base_passage = $tarif->prix_base_passage;
        $n_prix_nuit = $tarif->n_prix_nuit;
        $n_prix_passage = $tarif->n_prix_passage;
    }
}

if (isset($_POST["ajouter_tarif"])) {
    $prix_base_nuit = $_POST['prix_base_nuit'];
    $prix_base_passage = $_POST['prix_base_passage'];
    $n_prix_nuit = $_POST['n_prix_nuit'];
    $n_prix_passage = $_POST['n_prix_passage'];

    if (empty($prix_base_nuit) || empty($prix_base_passage)) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Tous les champs sont obligatoires.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        if ($id_tarif) {
            $sql = "UPDATE tarif_chambre SET prix_base_nuit = ?, prix_base_passage = ?, n_prix_nuit = ?, n_prix_passage = ? WHERE id_tarif = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prix_base_nuit, $prix_base_passage, $n_prix_nuit, $n_prix_passage, $id_tarif]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Tarif mis à jour avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de la mise à jour du tarif.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        } else {
            $sql = "INSERT INTO tarif_chambre (prix_base_nuit, prix_base_passage, n_prix_nuit, n_prix_passage) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prix_base_nuit, $prix_base_passage, $n_prix_nuit, $n_prix_passage]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Tarif ajouté avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de l\'ajout du tarif.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        }
        header("Location: tarif.php");
        exit();
    }
}

if (isset($_GET["supprimer"])) {
    $id_tarif = (int)$_GET["supprimer"];

    $sql_check_chambre = "SELECT * FROM chambre WHERE id_tarif = ?";
    $stmt_check_chambre = $pdo->prepare($sql_check_chambre);
    $stmt_check_chambre->execute([$id_tarif]);

    if ($stmt_check_chambre->rowCount() > 0) {
        
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Opération interdite : Tarif déjà appliqué à une chambre.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        
        $sql_delete = "DELETE FROM tarif_chambre WHERE id_tarif = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        if ($stmt_delete->execute([$id_tarif])) {
            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      Tarif supprimé avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                      Erreur lors de la suppression du tarif.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }
    header("Location: tarif.php");
    exit();
}

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<h1 class="my-4 text-center"><?= $id_tarif ? 'Modifier' : 'Ajouter' ?> un Tarif</h1>
<form method="post">
    <div class="mb-3">
        <label for="prix_base_nuit" class="form-label">Prix Base Nuit <span class="text-danger">*</span></label>
        <input type="number" step="any" class="form-control" id="prix_base_nuit" name="prix_base_nuit" value="<?= ($prix_base_nuit) ?>">
    </div>
    <div class="mb-3">
        <label for="prix_base_passage" class="form-label">Prix Base Passage <span class="text-danger">*</span></label>
        <input type="number" step="any" class="form-control" id="prix_base_passage" name="prix_base_passage" value="<?= ($prix_base_passage) ?>">
    </div>
    <div class="mb-3">
        <label for="n_prix_nuit" class="form-label">Nouveau Prix Nuit</label>
        <input type="number" step="any" class="form-control" id="n_prix_nuit" name="n_prix_nuit" value="<?= ($n_prix_nuit) ?>">
    </div>
    <div class="mb-3">
        <label for="n_prix_passage" class="form-label">Nouveau Prix Passage</label>
        <input type="number" step="any" class="form-control" id="n_prix_passage" name="n_prix_passage" value="<?= ($n_prix_passage) ?>">
    </div>
    <button type="submit" name="ajouter_tarif" class="btn btn-primary w-100"><?= $id_tarif ? 'Modifier' : 'Ajouter' ?></button>
</form>

<h1 class="m-5 w-100 text-center">Liste des Tarifs</h1>
<div class="table-responsive">
    <table class="table text-center">
        <thead>
            <tr>
                <th>ID de Tarif</th>
                <th>Prix Base Nuit</th>
                <th>Prix Base Passage</th>
                <th>Nouveau Prix Nuit</th>
                <th>Nouveau Prix Passage</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM tarif_chambre");
            $stmt->execute();
            $tarifs = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($tarifs as $tarif): ?>
            <tr>
                <td><?= ($tarif->id_tarif) ?></td>
                <td><?= ($tarif->prix_base_nuit) ?></td>
                <td><?= ($tarif->prix_base_passage) ?></td>
                <td><?= ($tarif->n_prix_nuit) ?></td>
                <td><?= ($tarif->n_prix_passage) ?></td>
                <td>
                <a href="?modifier=<?= $tarif->id_tarif ?>" style="color:black" class="mx-3">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <a href="?supprimer=<?= $tarif->id_tarif ?>" style="color:red">
                    <i class="fa-regular fa-trash-can"></i>
                </a>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>


<?php
include_once "../includes/footer.php";
?>