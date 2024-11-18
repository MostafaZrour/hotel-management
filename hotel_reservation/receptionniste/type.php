<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo "<div class='container mt-5'>";

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

$id_type = isset($_GET['modifier']) ? (int)$_GET['modifier'] : "";
$type_chambre = "";
$description_type = "";
$photo = "";

if ($id_type) {
    $sql = "SELECT * FROM type_chambre WHERE id_type_ch = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_type]);
    $type = $stmt->fetch(PDO::FETCH_OBJ);

    if ($type) {
        $type_chambre = $type->type_chambre;
        $description_type = $type->description_type;
        $photo = $type->photo_type;
    }
}

if (isset($_POST["ajouter_type"])) {
    $type_chambre = $_POST['type_chambre'];
    $description_type = !empty($_POST['description_type']) ? $_POST["description_type"] : "Chambre $type_chambre";
    $photo_path = $photo;

    if (empty($type_chambre) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Les champs "Type de la chambre et Photo" sont obligatoire.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
        $id_type ? header("Location:type.php?modifier=$id_type") : header("location:type.php") ;
        exit;
    } else {
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_destination = '../image/' . $photo_name;
        move_uploaded_file($photo_tmp, $photo_destination);
        $photo_path = $photo_destination;

        if ($id_type) {
            $sql = "UPDATE type_chambre SET type_chambre = ?, description_type = ?, photo_type = ? WHERE id_type_ch = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$type_chambre, $description_type, $photo_path, $id_type]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Type de chambre mis à jour avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de la mise à jour du type de chambre.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        } else {
            $sql = "INSERT INTO type_chambre (type_chambre, description_type, photo_type) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$type_chambre, $description_type, $photo_path]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Type de chambre ajouté avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de l\'ajout du type de chambre.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        }
    }
    header("Location:type.php");
    exit;
}

if (isset($_GET["supprimer"])) {
    $id_type_ch = (int)$_GET["supprimer"];

    $sql_check = "SELECT COUNT(*) FROM chambre WHERE id_type_ch = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_type_ch]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Opération interdite : Type déjà lié à une chambre.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        $sql_delete = "DELETE FROM type_chambre WHERE id_type_ch = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        if ($stmt_delete->execute([$id_type_ch])) {
            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      Type de chambre supprimé avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                      Erreur lors de la suppression du type de chambre.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }
    header("Location:type.php");
    exit;
}
?>

    <h1 class="my-4 text-center"><?= $id_type ? 'Modifier' : 'Ajouter' ?> un Type de Chambre</h1>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="type_chambre" class="form-label">Type de la chambre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="type_chambre" name="type_chambre" value="<?= $type_chambre ?>">
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Photo de la chambre</label>
            <input type="file" class="form-control" id="photo" name="photo" accept=".jpg, .jpeg, .png">
            <?php if ($photo): ?>
                <img src="<?= $photo ?>" alt="Photo de <?= $type_chambre ?>" class="img-thumbnail mt-2" width="200">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="description_type" class="form-label">Description</label>
            <textarea class="form-control" id="description_type" name="description_type" rows="4"><?= $description_type ?></textarea>
        </div>
        <button type="submit" name="ajouter_type" class="btn btn-primary w-100"><?= $id_type ? 'Modifier' : 'Ajouter' ?></button>
    </form>
<h1 class="m-5 w-100 text-center">Liste des Types de Chambre</h1>
<table class="table text-center">
    <thead>
        <tr>
            <th>ID de Type de Chambre</th>
            <th>Type de Chambre</th>
            <th>Description</th>
            <th>Photo</th>
            <th>Action </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM type_chambre");
        $stmt->execute();
        $types_chambre = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($types_chambre as $type): ?>
        <tr>
            <td class="align-middle"><?= $type->id_type_ch ?></td>
            <td  class="align-middle"><?= $type->type_chambre ?></td>
            <td  class="align-middle"><?= $type->description_type ?></td>
            <td  class="align-middle"><img src="<?= $type->photo_type ?>" alt="Photo de <?= $type->type_chambre ?>" class="img-fluid" width="100"></td>
            <td  class="align-middle">
                <div class="d-flex gap-2">
                    <a data-bs-toggle="modal" data-bs-target="#modalid<?= $type->id_type_ch ?>" style="color:blue;">
                        <i class="fa-regular fa-eye"></i>
                    </a>
                <a href="?modifier=<?= $type->id_type_ch ?>" style="color:black">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <a href="?supprimer=<?= $type->id_type_ch ?>" style="color:red">
                    <i class="fa-regular fa-trash-can"></i>
                </a>            
                </div>
                <div class="modal fade" id="modalid<?= $type->id_type_ch ?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="w-100 text-center" id="clientModalLabel">Type Information</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-start">
                                        <div class="row ">
                                            <div class="col-6">
                                                <img src="<?= $type->photo_type ?>" alt="Photo de <?= $type->type_chambre ?>" class="img-thumbnail img-fluid h-100">
                                            </div>
                                            <div class="col-6">
                                                    <div class="mt-4">
                                                        <h5>Type ID: <?= $type->id_type_ch ?></h5>
                                                        <p><strong>Type Chambre :</strong> <?= $type->type_chambre ?> </p>
                                                        <p><strong>Numero Chambres :</strong><?php
                                                            $sql_chambres = "SELECT numero_chambre FROM chambre WHERE id_type_ch = ?";
                                                            $stmt_chambres = $pdo->prepare($sql_chambres);
                                                            $stmt_chambres->execute([$type->id_type_ch]);
                                                            $chambres = $stmt_chambres->fetchAll(PDO::FETCH_OBJ);

                                                            if ($chambres) {
                                                                foreach ($chambres as $chambre) {
                                                                    echo $chambre->numero_chambre." ";
                                                                }
                                                            } else {
                                                                echo 'Aucune chambre associée';
                                                            }
                                                            ?> </p>
                                                            <p><strong>Description :</strong> <?= $type->description_type ?> </p>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
</div>
<?php include_once "../includes/footer.php";?>