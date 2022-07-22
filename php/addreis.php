<?php
session_start();
require('database.php');
//bekijk of je bent ingelogd
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
// Als je je token niet bij je hebt wordt je niet geaccepteerd.
if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
    echo "Wrong Token";
    header("Location: ../user/createpost.php?error=token");
}
// Hier checken we of alle velden zijn ingevuld
if(isset($_POST["title"], $_POST["text"], $_FILES['image'], $_POST['begin'],$_POST['eind'],$_POST['max'],$_POST['best'])){
    //Voeg alle variabelen toe tot een geheel.
    $image = $_FILES['image'];
    $Tijdelijk = $image['tmp_name'];
    $imagenaam = $image['name'];
    $type = $image['type'];
    $map = 'uploads/';
    //toegestaande files
    $Toegestaan = array("image/jpg","image/jpeg","image/png","image/gif");
    //check voor special charchters je weet maar nooit
    $titel = strip_tags(htmlspecialchars($_POST['title']));
    $begindate = date('Y-m-d', strtotime($_POST['begin']));
    $einddate = date('Y-m-d', strtotime($_POST['eind']));


//function to give a a unique id
    function uuidv4(){
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    $randomid = uuidv4();

//Hier voegen we de afbeelding toe tot de uploads folder en to ander
    $afbeelding = $map.$imagenaam;
    $new_str = str_replace(' ', '', $afbeelding);
    $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $new_str = $map . uniqid() . "_" . uniqid() . "." . $fileExt;
    if (in_array($type,$Toegestaan)){
        move_uploaded_file($Tijdelijk, "../".$new_str);
    }else{
        header("Location: ../user/createreis.php?error=nietgeupload");
    }
    //Hier zorgen we ervoor dat de reis in de database wordt gezet.
    if ($database->insert("reizen", ["id" => $randomid, "titel"=> $titel, "bestemming" => $_POST['best'], "text"=> $_POST['text'], "image"=> $new_str, "begindatum" => $begindate, "einddatum" => $einddate, "max" => $_POST['max']])) {
        header("Location: ../user/dashboard.php");
    }
    else {
        exit();
        header('Location: ../user/createreis.php?error=mysql');
    }
} else {
    header('Location: ../user/createreis.php?error=fields');
}