<?php 
    if(!isset($_SESSION["user"])){
        header("location:../auth/login.php");
        $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Échec de l\'authentification.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        exit(); 
    }
    if($_SESSION["user"]->etat !== "active"){
        header("location:../auth/login.php");
        $_SESSION["message"] = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                            Votre compte est bloqué. Veuillez contacter Zrour.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        exit();
    }
?>
<style>
  .sidebar {
      z-index: 100;
      height: calc(100% - 4vw);
      width: 300px;
      position: fixed;
      left: 2vw;
      top: 2vw;
      padding: 1rem;
      border: 2px solid #dee2e6;
      border-radius:30px;
      transition: transform 0.3s ease;
      border-radius: 16px;
      font-family: Verdana, Tahoma, sans-serif;
    }

    h5{
      font-size: 1.5rem;
    }
    ul li{
      margin:10px 0 ;
      padding:10px ;
      font-size:1.3rem ;
    }
  .sidebar-hidden {
      transform: translateX(-110%);
  }
  #toggleSidebarBtn{
    width: 100px;
    position:fixed ;
    top:20px;
    right:20px ;
  }
</style>

<button class="btn btn-dark toggle-btn fw-bold" id="toggleSidebarBtn">Menu</button>
<div class="sidebar shadow bg-body-tertiary sidebar-hidden" id="sidebar">
    <h5 class="my-4">
    <span class="fs-2 fa fa-circle-user align-middle mx-3"></span>
    <?= $_SESSION["user"] ? $_SESSION["user"]->nom ." ". $_SESSION["user"]->prenom : "" ?>
  </h5>
  <hr>
    <ul class="navbar-nav">
      <?php if($_SESSION["user"]->type == "manager"): ?>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="compte.php">Gestion Comptes</a>
      </li>
      <li class="nav-item btn btn-outline-primary ">
        <a class="nav-link" aria-current="page" href="suivi_reservation.php">Suivre Reservation</a>
      </li>
      <?php endif ?>
      <?php if($_SESSION["user"]->type == "receptionniste"): ?>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="client.php">Gestion des clients</a>
      </li>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="ajouter_ch.php">Gestion des chambres</a>
      </li>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="tarif.php">Gestion des tarifs</a>
      </li>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="type.php">Gestion des types</a>
      </li>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="capacite.php">Gestion des capacites</a>
      </li>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="reservation.php">Gestion des reservation</a>
      </li>
      <?php endif ?>
      <?php if($_SESSION["user"]->type == "caissier"): ?>
      <li class="nav-item btn btn-outline-primary">
        <a class="nav-link" aria-current="page" href="payment.php">Payment</a>
      </li>
      <?php endif ?>
      <hr>
      <?php if($_SESSION["user"]): ?>
      <li class="nav-item btn btn-outline-danger">
        <a class="nav-link" aria-current="page" href="../auth/logout.php">Logout</a>
      </li>
      <?php endif ?>
    </ul>
</div>
<script>
    document.getElementById('toggleSidebarBtn').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('sidebar-hidden');
    });
</script>
</body>
</html>
