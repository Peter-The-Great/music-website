<?php
session_start();
require('database.php');
// Check of je ingelogd bent.
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
}
// Check of je de token bij je hebt zo niet wordt je niet geaccepteerd.
if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
    echo "Wrong Token";
    header("Location: ../user/changereis.php?error=token");
}
// Insert into DATABASE
if(isset($_POST["title"], $_POST["text"], $_POST['Huidige_Afbeelding'],  $_POST['begin'],$_POST['eind'],$_POST['max'],$_POST['best'])){
    $Huidig = $_POST['Huidige_Afbeelding'];
    $Afbeelding = $_FILES['image'];
    $Tijdelijk = $Afbeelding['tmp_name'];
    $Afbeeldingnaam = $Afbeelding['name'];
    $type = $Afbeelding['type'];
    $map = "uploads/";
    $unlink = "../";
    $Toegestaan = array("image/jpg","image/jpeg", "image/png", "image/gif");
    $begindate = date('Y-m-d', strtotime($_POST['begin']));
    $einddate = date('Y-m-d', strtotime($_POST['eind']));

    //Als er geen nieuwe afbeelding is geupload.
    if (empty($Afbeelding) || $Afbeelding['size'] == 0) {
        if ($database->update("reizen", ["titel" => $_POST['title'], "bestemming" => $_POST['best'], "text" => $_POST['text'], "begindatum" => $begindate, "einddatum" => $einddate, "max" => $_POST['max']], ["id" => $_GET['id']])) {
            header("Location: ../user/dashboard.php");
        }
        else {
            header('Location: ../user/changereis.php?error=mysql');
        }
    }//Als we een afbeelding hebben moeten we de originele verwijderen en de nieuwe toevoegen.
    elseif ($Afbeeldingnaam != $Huidig && in_array($type, $Toegestaan)) {
        unlink($unlink.$Huidig);
        $imagenew = $map.$Afbeeldingnaam;
        $new_str = str_replace(' ', '', $imagenew);
        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_str = $map . uniqid() . "_" . uniqid() . "." . $fileExt;
        move_uploaded_file($Tijdelijk, "../".$new_str);

        //Hier laten we medoo een update maken.
        if ($database->update("reizen", ["titel" => $_POST['title'], "bestemming" => $_POST['best'], "text" => $_POST['text'], "image" => $new_str, "begindatum" => $begindate, "einddatum" => $einddate, "max" => $_POST['max']], ["id" => $_GET['id']])) {
            header("Location: ../user/dashboard.php");
        }
        else {
            header('Location: ../user/changereis.php?error=mysql');
        }
    }else {
        header('Location: ../user/changereis.php?error=image_niet_geupload');
    }
}else {
    header('Location: ../user/changereis.php?error=fields');
}
?>