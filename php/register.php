<?php
session_start();
require('database.php');

//function to give a a unique id
function uuidv4(){
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

//check of crsf token wordt meegenomen.
if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
    echo "Wrong Token";
    header("Location: ../user/register.php?error=token");
}
//check of de captcha wordt meegenomen.
if (!isset($_POST["g-recaptcha-response"])) {
    header("Location: ../user/register.php?catpcha=false");
}
$response = $_POST["g-recaptcha-response"];

$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => $_ENV["RECAPTCHA_SECRET_KEY"], //Enter Captcha_Key
    'response' => $response
);
$options = array(
    'http' => array (
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen(http_build_query($data))."\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);

//Captcha wordt hier gevalideerd
$context  = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captcha_success=json_decode($verify);

if ($captcha_success->success==false) {
    header("Location: ../user/register.php?catpcha=false");
} else if ($captcha_success->success==true) {

// Check of alle veldjes zijn ingevuld
    if(isset($_POST["username"],$_POST["password"],$_POST["openname"],$_POST["email"],$_FILES["image"], $_POST['card'])){

        $Afbeelding = $_FILES['image'];
        $Tijdelijk = $Afbeelding['tmp_name'];
        $Afbeeldingnaam = $Afbeelding['name'];
        $type = $Afbeelding['type'];
        $map = "uploads/simg/";
        $Toegestaan = array("image/jpg","image/jpeg", "image/png", "image/gif");

        //Een paar kleine scriptjes hieronderaan.
        //Pakt mogelijk studentnummer als id.
        $studentnummer = str_replace('@glr.nl', '', $_POST['email']);
        if (is_numeric($studentnummer)){
            $randomid = $studentnummer;
            $role = intval(0);
        }else {
            // Maak een compleet unique id als je geen student bent.
            $randomid = uuidv4();
            $role = intval(1);
        }
        //Check of de persoon nog opmerkingen heeft geregistreerd.
        if (empty($_POST['card'])){
            $op = "Geen";
        }else{
            $op = $_POST['card'];
        }

        // Voeg hun afbeelding toe tot de web server.
        $afbeelding = $map.$Afbeeldingnaam;
        $new_str = str_replace(' ', '', $afbeelding);
        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_str = $map . uniqid() . "_" . uniqid() . "." . $fileExt;
        move_uploaded_file($Tijdelijk, "../".$new_str);

        //Hier wordt de update query gemaakt
        if ($database->insert("users", ["id" => $randomid, "username" => $_POST["username"], "password" => password_hash($_POST["password"], PASSWORD_DEFAULT), "realname" => $_POST["openname"], "email" => $_POST["email"], "identity" => $_POST['card'], "opmerking" => $op, "profile" => $new_str, "role" => $role])) {
            header("Location: ../user/index.php");
        }
        else {
            header('Location: ../user/register.php?error=mysql');
        }

    }
    else {
        header('Location: ../user/register.php?error=fields');
    }
}
?>