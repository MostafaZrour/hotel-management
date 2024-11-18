<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

if($_SESSION["user"]->type !== "receptionniste"){
    header("location:../index.php");
    $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Échec de l\'authentification.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    exit(); 
}
?>
<div class="container">
<?php 
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
if(isset($_POST["ajouter"])){
    $id_client = $_POST["id_client"];
    $nom_complet = $_POST["nom_complet"];
    $sexe = !empty($_POST["sexe"]) ? $_POST["sexe"] : "Vide";
    $date_naissance = !empty($_POST["date_naissance"]) ? $_POST["date_naissance"] : "Vide";
    $age = !empty($_POST["age"]) ? $_POST["age"] : "Vide";
    $pays = $_POST["pays"];
    $ville = $_POST["ville"];
    $adresse = !empty($_POST["adresse"]) ? $_POST["adresse"] : "Vide";
    $telephone = $_POST["telephone"];
    $email = !empty($_POST["email"]) ? $_POST["email"] : "Vide";
    $autres_details = !empty($_POST["autre"]) ? $_POST["autre"] : "Vide";

    if(!empty($nom_complet) && !empty($pays) && !empty($ville) && !empty($telephone)){
            if(empty($id_client)){
            $sql = $pdo->prepare("INSERT INTO client VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->execute([$nom_complet, $sexe, $date_naissance, $age, $pays, $ville, $adresse, $telephone, $email, $autres_details]);
        } else {
            $sql = $pdo->prepare("UPDATE client SET nom_complet = ?, sexe = ?, date_naissance = ?, age = ?, pays = ?, ville = ?, adresse = ?, telephone = ?, email = ?, autres_details = ? WHERE id_client = ?");
            $sql->execute([$nom_complet, $sexe, $date_naissance, $age, $pays, $ville, $adresse, $telephone, $email, $autres_details, $id_client]);
        }
        header("location:client.php#table");
    } else {
        $_SESSION['message'] =  '<div class="alert alert-warning alert-dismissible fade show mt-5" role="alert">
                Nom complet, pays, ville, et Téléphone sont obligatoires.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

$client = null;
if(isset($_GET["modifier"])){
    $id = $_GET["modifier"];
    $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
    $stmt->execute([$id]);
    $client = $stmt->fetch(PDO::FETCH_OBJ);
}

if(isset($_GET["supprimer"])){
    $id = $_GET["supprimer"];
    $stmt = $pdo->prepare("SELECT * FROM reservation WHERE id_client = ? ");
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $_SESSION['message'] =  '<div class="alert alert-warning alert-dismissible fade show mt-5" role="alert">
                Opération interdite : Client déjà effectué des réservations.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    } else {
        $stmt = $pdo->prepare("DELETE FROM client WHERE id_client = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] =  '<div class="alert alert-success alert-dismissible fade show mt-5" role="alert">
                Client supprimé avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    header("location:client.php");
}
?>
<?php 

if (isset($_POST["recherche"])) {
    $nom_complet = $_POST['nom_complet'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $ville = $_POST['ville'] ?? '';

    $query = "SELECT * FROM client WHERE 1=1";
    $params = [];

    if (!empty($nom_complet)) {
        $query .= " AND nom_complet LIKE ?";
        $params[] = "%$nom_complet%";
    }
    if (!empty($pays)) {
        $query .= " AND pays LIKE ?";
        $params[] = "%$pays%";
    }
    if (!empty($ville)) {
        $query .= " AND ville LIKE ?";
        $params[] = "%$ville%";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $info_clients = $stmt->fetchAll(PDO::FETCH_OBJ);

} else {
    $stmt = $pdo->prepare("SELECT * FROM client");
    $stmt->execute();
    $info_clients = $stmt->fetchAll(PDO::FETCH_OBJ);
}

?>
<form method="post" class="Myform mt-5">
        <input type="hidden" name="id_client" value="<?= $client ? $client->id_client : '' ?>">
        <h6 class="mt-1">Nom complet</h6>
        <input type="text" class="form-control" name="nom_complet" value="<?= $client ? $client->nom_complet : '' ?>">
        <div class="col-12">
            <h6 class="mt-2">sexe </h6>
            <div class="form-check form-check-inline">
                <input type="radio" name="sexe" value="homme" class="form-check-input" <?= $client && $client->sexe == 'homme' ? 'checked' : '' ?>>
                <label class="form-check-label">Homme</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="sexe" value="femme" class="form-check-input" <?= $client && $client->sexe == 'femme' ? 'checked' : '' ?>>
                <label class="form-check-label">Femme</label>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <h6 class="mt-2">Date de naissance</h6>
                <input type="date" class="form-control date-n" name="date_naissance" value="<?= $client ? $client->date_naissance : '' ?>">
            </div>
            <div class="col-4">
                <h6 class="mt-2">age</h6>
                <input type="number" class="form-control age" name="age" readonly value="<?= $client ? $client->age : '' ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <h6 class="mt-2">pays</h6>
                <input type="text" class="form-control" name="pays" value="<?= $client ? $client->pays : '' ?>">
            </div>
            <div class="col-3">
                <h6 class="mt-2">ville</h6>
                <input type="text" class="form-control" name="ville" value="<?= $client ? $client->ville : '' ?>">
            </div>
            <div class="col-6">
                <h6 class="mt-2">adresse</h6>
                <input type="text" class="form-control" name="adresse" value="<?= $client ? $client->adresse : '' ?>">
            </div>
        </div>
        <h6 class="mt-2">Télèphone</h6>
        <input type="text" class="form-control" name="telephone" value="<?= $client ? $client->telephone : '' ?>">
        <h6 class="mt-2">email</h6>
        <input type="email" class="form-control" name="email" value="<?= $client ? $client->email : '' ?>">
        <h6 class="mt-2">Autre</h6>
        <textarea name="autre" class="form-control"><?= $client ? $client->autres_details : '' ?></textarea>
        <button type="submit" class="btn btn-primary mt-3 w-100" name="ajouter"><?= $client ? 'Modifier' : 'Ajouter' ?></button>
    </form>
<h1 class="mt-4">Liste des clients</h1>
<form method="post" action="#table" style="border:none">
    <div class="row mb-3">
        <div class="col-3">
            <label for="nom_complet" class="form-label">Nom complet</label>
            <input type="text" id="nom_complet" name="nom_complet" class="form-control" value="<?= htmlspecialchars($nom_complet ?? '') ?>">
        </div>
        <div class="col-3">
            <label for="pays" class="form-label">Pays</label>
            <input type="text" id="pays" name="pays" class="form-control" value="<?= htmlspecialchars($pays ?? '') ?>">
        </div>
        <div class="col-3">
            <label for="ville" class="form-label">Ville</label>
            <input type="text" id="ville" name="ville" class="form-control" value="<?= htmlspecialchars($ville ?? '') ?>">
        </div>
        <div class="col-3 d-flex align-items-end">
            <button name="recherche" class="btn btn-primary w-100">Rechercher</button>
        </div>
    </div>
</form>
<a href="client_register.php">
    <button class="btn btn-primary w-100 mb-4">Register</button>
</a>
    <table class="table text-center mb-5" id="table">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>pays</th>
                <th>ville</th>
                <th>Télèphone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($info_clients as $client): ?>
            <tr>
                <td><?= $client->nom_complet ?></td>
                <td><?= $client->pays ?></td>
                <td><?= $client->ville ?></td >
                <td><?= $client->telephone ?></td>
                <td>
                    <a data-bs-toggle="modal" data-bs-target="#modalid<?= $client->id_client ?>" style="color:blue;">
                        <i class="fa-regular fa-eye"></i>
                    </a>
                    <a href="?modifier=<?= $client->id_client ?>" class="mx-2" style="color:black">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                    <a href="?supprimer=<?= $client->id_client ?>" style="color:red">
                        <i class="fa-regular fa-trash-can"></i>
                    </a>
                    
                    <div class="modal fade" id="modalid<?= $client->id_client ?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h2 class="w-100 text-center" id="clientModalLabel">Client Information</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Client ID</th>
                                                <td><?= $client->id_client ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nom complet</th>
                                                <td><?= $client->nom_complet ?></td>
                                            </tr>
                                            <tr>
                                                <th>sexe</th>
                                                <td><?= $client->sexe ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date Naissance</th>
                                                <td><?= $client->date_naissance ?></td>
                                            </tr>
                                            <tr>
                                                <th>age</th>
                                                <td><?= $client->age ?></td>
                                            </tr>
                                            <tr>
                                                <th>pays</th>
                                                <td><?= $client->pays ?></td>
                                            </tr>
                                            <tr>
                                                <th>ville</th>
                                                <td><?= $client->ville ?></td>
                                            </tr>
                                            <tr>
                                                <th>adresse</th>
                                                <td><?= $client->adresse ?></td>
                                            </tr>
                                            <tr>
                                                <th>email</th>
                                                <td><?= $client->email ?></td>
                                            </tr>
                                            <tr>
                                                <th>Autre Détails</th>
                                                <td><?= $client->autres_details ?></td>
                                            </tr>
                                        </table>
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
</div>
</div>
<br><br><br><br><br>
<?php include_once "../includes/footer.php";?>
