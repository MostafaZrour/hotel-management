<?php
session_start();
include_once "../includes/header.php";
include_once "../includes/db.php";
    if(isset($_POST["login"])){
        $username = $_POST["username"];
        $password = $_POST["password"];

        if(!empty($username) || !empty($password)){

            $prepare = $pdo->prepare("SELECT * FROM users_app WHERE username=? AND password=? ");
            $prepare->execute([$username, $password]);
            $user = $prepare->fetch(PDO::FETCH_OBJ);

            if($prepare->rowCount() > 0 ){
                $_SESSION["user"] = $user;
                header("location:../index.php");
            }else {
                $_SESSION["message"] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            Nom d\'utilisateur ou mot de passe incorrect.
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>';
            }
            
        }else {
            $_SESSION["message"] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        Nom d\'utilisateur et mot de passe sont obligatoire.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
        }
    }
?>
<style>
    body{
        height:100vh;
        display: flex ;
        align-items:center ;
    }
    .myForm {
	width:600px;
	max-width:100%;
	border:solid 2px #dee2e6;
	border-radius:10px;
	padding:10px;
}
</style>
<div class="container">

<form class="myForm shadow bg-light mt-5" method="post">

		<div class="card">

            <div class="card-header w-100 text-center">Login</div>
            <div class="card-body">
            <?php 
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
        ?>
            <div class="row g-3">
			<div class="col-12">
				<label for="username" class="form-label">Login</label>
				<div class="input-group">
					<span class="input-group-text">@</span>
					<input type="text" class="form-control" id="username" name="username" placeholder="Login">
				</div>
			</div>					
			<div class="col-12">
                <label for="username" class="form-label">Password</label>
				<input type="password" class="form-control" id="password" name="password" placeholder="Password">
			</div>					
		</div>
		    <div class="row justify-content-center mt-3 ">
			    <div class="col-sm-6">
				    <button name="login" class="btn btn-outline-primary w-100 btn-block">Login</button>
                </div>
            </div>
		</div>
            </div>
        </div>
	</form>
</div>	
<?php include_once "../includes/footer.php"; ?>
