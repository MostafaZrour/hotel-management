<?php
session_start();
switch ($_SESSION["user"]->type) {
    case 'manager':
        header("location:manager/compte.php");
        break;
    case 'receptionniste':
        header("location:receptionniste/client.php");
        break;
    case 'caissier':
        header("location:caissier/payment.php");
        break;
    
    default:
        header("location:auth/login.php");
        break;
}