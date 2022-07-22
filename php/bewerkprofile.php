<?php
session_start();
require('database.php');
//Check of je bent ingelogd.
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
    echo "Wrong Token";
    header("Location: ../user/profile.php?error=token");
}
// Check of alle velden zijn ingevuld.
if(isset($_POST["username"],$_POST["password"],$_POST["openname"],$_POST["email"],$_POST["Huidige_Afbeelding"])){

    $Huidig = $_POST['Huidige_Afbeelding'];
    $Afbeelding = $_FILES['image'];
    $Tijdelijk = $Afbeelding['tmp_name'];
    $Afbeeldingnaam = $Afbeelding['name'];
    $type = $Afbeelding['type'];
    $map = "uploads/simg/";
    $unlink = "../";
    $Toegestaan = array("image/jpg","image/jpeg", "image/png", "image/gif");

    if ($Afbeelding['size'] == 0) {
        if ($database->update("users", ["username" => $_POST["username"], "password" => password_hash($_POST["password"], PASSWORD_DEFAULT), "realname" => $_POST["openname"], "email" => $_POST["email"]], ["id" => $_SESSION["id"]])) {
            $_SESSION['name'] = $_POST["username"];
            header("Location: ../user/dashboard.php");
        }
        else {
            header('Location: ../user/profile.php?error=mysql');
        }
    }
//hier wordt gekeken naar de huidige afbeeldingen en of het type van de afbeelding is toegestaan
    elseif ($Afbeeldingnaam != $Huidig && in_array($type, $Toegestaan)) {
        unlink($unlink.$Huidig);
        $afbeelding = $map.$Afbeeldingnaam;
        $new_str = str_replace(' ', '', $afbeelding);
        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_str = $map . uniqid() . "_" . uniqid() . "." . $fileExt;
        move_uploaded_file($Tijdelijk, "../".$new_str);

        //Hier wordt de update query gemaakt
        if ($database->update("users", ["username" => $_POST["username"], "password" => password_hash($_POST["password"], PASSWORD_DEFAULT), "realname" => $_POST["openname"], "email" => $_POST["email"], "profile" => $new_str], ["id" => $_SESSION["id"]])) {
            header("Location: ../user/dashboard.php");
        }
        else {
            header('Location: ../user/profile.php?error=mysql');
        }
    }else{
        header('Location: ../user/dashboard.php?error=image_niet_geupload');
    }

}
else {
    header('Location: ../user/profile.php?error=fields');
}
?>