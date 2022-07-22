<?php
session_start();
require('database.php');
//Check of je bent ingelogd
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
//Check of de CRSF token is meegestuurd.
if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
    echo "Wrong Token";
    header("Location: ../user/reizen.php?error=token");
}
//Hier checken we of er niet al eerder een registratie is gedaan door de zelfde student dat hoort bij hetzelfde reisid
$stmt = $database->select("register", ["studentid"], ["studentid" => intval($_SESSION['id']), "reisid" => $_POST['reisid']]);
if (count($stmt[0]["studentid"]) == 1){
    header("Location: ../user/dashboard.php?result=allingeschreven");
    exit();
}

// Insert Nieuwe aanmelding tot de database.
if(isset($_POST["id"], $_POST['reisid'])){

//function to give a a unique id
    function uuidv4(){
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    $randomid = uuidv4();
    //Hier wordt een nieuwe aanmelding toegevoegd.
    if ($database->insert("register", ["id" => $randomid, "reisid"=> $_POST['reisid'], "studentid" => $_POST['id'], "identity" => $_SESSION['identity'], "opmerking" => $_SESSION['op']])) {
        header("Location: ../user/dashboard.php?result=ingeschreven");
    }
    else {
        header('Location: ../user/dashboard.php?error=mysql');
    }
} else {
    header('Location: ../user/reizen.php?error=fields');
}