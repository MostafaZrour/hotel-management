<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/nav.php";
include_once "../includes/db.php";

echo "<div class='container mt-5'>";

if($_SESSION["user"]->type !== "manager"){
    header("location:../index.php");
    $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
    Échec de l\'authentification.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    exit(); 
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action === 'bloquer') {
        $new_etat = 'bloque';
    } elseif ($action === 'activer') {
        $new_etat = 'active';
    } else {

        header("location:compte.php");
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users_app SET etat=? WHERE id_user=?");
    $stmt->execute([$new_etat, $id]);

    $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      État utilisateur mis à jour avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';

    header("location:compte.php");
    exit();
}


if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM users_app WHERE id_user=?");
    $stmt->execute([$id]);
    $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                      User supprimée avec succès.
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
    header("location:compte.php");
    exit();
}
$id_user = isset($_GET['modifier']) ? (int)$_GET['modifier'] : "";
$nom = "";
$prenom = "";
$username = "";
$password = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    
    if (!empty($nom) && !empty($prenom) && !empty($username) && !empty($password) && !empty($type)) {
        if($id_user){
            $statement = $pdo->prepare("UPDATE users_app SET nom=?, prenom=?, username=?, type=? WHERE id_user=?");
            $statement->execute([$nom, $prenom, $username, $type, $id_user]);

            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Utilisateur modifié avec succès.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            header("Location:compte.php");
            exit;
        }else{
            
            $statement = $pdo->prepare("INSERT INTO users_app (nom, prenom, username, password, type, etat) VALUES (?, ?, ?, ?, ?, ?)");
            $statement->execute([$nom, $prenom, $username, $password, $type, "active"]);

            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Utilisateur ajouté avec succès.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            header("Location: compte.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Tous les champs sont obligatoires.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}


if ($id_user) {
    $sql = "SELECT * FROM users_app WHERE id_user = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_user]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    $nom = $user->nom ;
    $prenom = $user->prenom ;
    $username = $user->username ;
    $password = $user->password ;
    $type = $user->type ;

}


$stmt = $pdo->prepare("SELECT * FROM users_app");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_OBJ);


if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

?>
 <h2 class="w-100 text-center my-4">Ajouter un nouvel utilisateur</h2>
    <form action="" method="post">
        <div class="mb-3">
            <input type="hidden" name="id_user" value="<?= $id_user ?>">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?= $nom ?>">
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= $prenom ?>">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= $username ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" value="<?= $password ?>">
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-control" id="type" name="type">
                <option value="">Choisir...</option>
                <option value="manager">Manager</option>
                <option value="receptionniste">Receptionniste</option>
                <option value="caissier">Caissier</option>
            </select>
        </div>
        <button type="submit" name="ajouter" class="btn btn-primary w-100"><?= $id_user ? 'Modifier' : 'Ajouter' ?></button>
    </form>
    <table class="table text-center mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom Complet</th>
            <th>Nom d'utilisateur</th>
            <th>État</th>
            <th>Type</th>
            <th>Etat </th>
            <th>Action</th>
            <th>Gestion</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id_user ?></td>
                <td><?= $user->nom ?></td>
                <td><?= $user->prenom ?></td>
                <td><?= $user->username ?></td>
                <td><?= $user->type ?></td>
                <td>
                    <?php if ($user->etat === 'active'): ?>
                        <span class="p-2 badge bg-success "><?= $user->etat ?></span>
                    <?php else: ?>
                        <span class="p-2 badge bg-danger"><?= $user->etat ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a data-bs-toggle="modal" data-bs-target="#modalid<?= $user->id_user ?>" style="color:blue;">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                        <a href="?modifier=<?= $user->id_user ?>" style="color:black">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                        <a href="?supprimer=<?= $user->id_user ?>" style="color:red">
                            <i class="fa-regular fa-trash-can"></i>
                        </a>
                    </div>
                    <div class="modal fade" id="modalid<?= $user->id_user ?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="w-100 text-center" id="clientModalLabel">Informations Utilisateur</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table text-start">
                                        <tr>
                                            <th>User ID</th>
                                            <td><?= $user->id_user ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nom Complet</th>
                                            <td><?= $user->nom . " " . $user->prenom ?></td>
                                        </tr>
                                        <tr>
                                            <th>Username</th>
                                            <td><?= $user->username ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mot de passe</th>
                                            <td><?= $user->password ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td><?= $user->type ?></td>
                                        </tr>
                                        <tr>
                                            <th>État</th>
                                            <td><?= $user->etat ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <?php if ($user->etat === 'active'): ?>
                        <a href="?action=bloquer&id=<?= $user->id_user ?>" style="color:red"><i class="fs-5 fa-solid fa-ban"></i></a>
                    <?php else: ?>
                        <a href="?action=activer&id=<?= $user->id_user ?>" style="color:green"><i class="fs-5 fa-regular fa-circle-check"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include_once "../includes/footer.php" ?>
