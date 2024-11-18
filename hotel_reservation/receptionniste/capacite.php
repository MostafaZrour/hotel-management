<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";
?>
<?php
echo "<div class='container mt-5'>";
$message = "";

$id_capacite = isset($_GET['modifier']) ? (int)$_GET['modifier'] : "";
$titre_capacite = "";
$numero_capacite = "";

if ($id_capacite) {
    $sql = "SELECT * FROM capacite_chambre WHERE id_capacite = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_capacite]);
    $capacite = $stmt->fetch(PDO::FETCH_OBJ);

    if ($capacite) {
        $titre_capacite = $capacite->titre_capacite;
        $numero_capacite = $capacite->numero_capacite;
    }
}

if (isset($_POST["ajouter_capacite"])) {
    $titre_capacite = $_POST['titre_capacite'];
    $numero_capacite = $_POST['numero_capacite'];

    if (empty($titre_capacite) || empty($numero_capacite)) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Tous les champs sont obligatoires.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        if ($id_capacite) {
            $sql = "UPDATE capacite_chambre SET titre_capacite = ?, numero_capacite = ? WHERE id_capacite = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titre_capacite, $numero_capacite, $id_capacite]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Capacité mise à jour avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de la mise à jour de la capacité.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        } else {
            $sql = "INSERT INTO capacite_chambre (titre_capacite, numero_capacite) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titre_capacite, $numero_capacite]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                          Capacité ajoutée avec succès.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                          Erreur lors de l\'ajout de la capacité.
                                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
        }
        header("Location: capacite.php");
        exit();
    }
}

if (isset($_GET["supprimer"])) {
    $id_capacite = (int)$_GET["supprimer"];

    $sql_check = "SELECT COUNT(*) FROM chambre WHERE id_capacite = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_capacite]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  Opération interdite : Capacité déjà liée à une chambre.
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>';
    } else {
        $sql_delete = "DELETE FROM capacite_chambre WHERE id_capacite = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        if ($stmt_delete->execute([$id_capacite])) {
            $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      Capacité supprimée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                      Erreur lors de la suppression de la capacité.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }
    header("Location: capacite.php");
    exit();
}

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<h1 class="my-4 text-center"><?= $id_capacite ? 'Modifier' : 'Ajouter' ?> une Capacité</h1>
<form method="post">
    <div class="mb-3">
        <label for="titre_capacite" class="form-label">Titre de la capacité <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="titre_capacite" name="titre_capacite" value="<?= htmlspecialchars($titre_capacite) ?>">
    </div>
    <div class="mb-3">
        <label for="numero_capacite" class="form-label">Numéro de la capacité <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="numero_capacite" name="numero_capacite" value="<?= htmlspecialchars($numero_capacite) ?>">
    </div>
    <button type="submit" name="ajouter_capacite" class="btn btn-primary w-100"><?= $id_capacite ? 'Modifier' : 'Ajouter' ?></button>
</form>

<h1 class="m-5 w-100 text-center">Liste des Capacités</h1>
<table class="table text-center">
    <thead>
        <tr>
            <th>ID de Capacité</th>
            <th>Titre de Capacité</th>
            <th>Numéro de Capacité</th>
            <th>Action </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM capacite_chambre");
        $stmt->execute();
        $capacites = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($capacites as $capacite): ?>
        <tr>
            <td><?= htmlspecialchars($capacite->id_capacite) ?></td>
            <td><?= htmlspecialchars($capacite->titre_capacite) ?></td>
            <td><?= htmlspecialchars($capacite->numero_capacite) ?></td>
            <td>
                <div class="d-flex gap-2 justify-content-center">
                <a href="?modifier=<?= htmlspecialchars($capacite->id_capacite) ?>" style="color:black">
                    <i class="fa-regular fa-pen-to-square"></i>
                </a>
                <a href="?supprimer=<?= htmlspecialchars($capacite->id_capacite) ?>" style="color:red">
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
