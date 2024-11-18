<?php 
session_start();
session_destroy();
session_start();
$_SESSION["message"] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Vous vous êtes déconnecté avec succès.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';

header("location:login.php");
exit();
?>
