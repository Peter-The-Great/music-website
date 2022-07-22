<?php
session_start();
require("../database.php");

//Hier bekijken we of alle informatie juist wordt meegeleverd
if(!isset($_POST["username"], $_POST["password"], $_POST["g-recaptcha-response"]) ) {
    session_destroy();
    header("Location: ../../user/index.php?error=veld");
    return false;
}
//Check of je nog op hetzelfde ip zit.
if($_SESSION['ip'] !==  $_SERVER['REMOTE_ADDR']){
    header('Location: ../../user/index.php?error=notthesameipadress');
}

/* Recaptcha Script  */

//Hier zorgen we ervoor dat de recapthca goed wordt gekeurd.
$response = $_POST["g-recaptcha-response"];
/*  */
$url = 'https://www.google.com/recaptcha/api/siteverify';
//Pak de data mee en voeg het samen met de secret key toe.
$data = array(
    'secret' => $_ENV["RECAPTCHA_SECRET_KEY"], //Enter Captcha_Key
    'response' => $response
);
//Zorg ervoor dat we de juiste http header krijgen waardoor we een stream kunnen maken en de json kunnen ophalen.
$options = array(
    'http' => array (
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen(http_build_query($data))."\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);
//Hier halen we en verifieren we of de captcha een success was via een json bestand.
$context  = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captcha_success=json_decode($verify);

/* Einde Recaptcha Script  */


/* Inlog Script  */
//Zodra het geen een success is geweest. Ga terug naar de login.
if ($captcha_success->success==false) {
    header("Location: ../../user/index.php?catpcha=false");
    //Zodra het een success is geweest. Ga door met de inlog
} else if ($captcha_success->success==true) {
    if(!isset($_SESSION["token"]) || $_SESSION["token"] !== $_POST["token"]){
        echo "Wrong Token";
        header("Location: ../../user/index.php?error=token");
    }

    //Hier paken we de data op van de specifieke gebruiker, ik gebruik nu username, niet enorm logisch maar het doet wat het moet doen.
    if ($stmt = $database->select("users", ["id","username","password", "role", "identity", "opmerking"], ["username" => $_POST['username']])) {

        //Hier gaan we het wachtwoord checken en kijken we of we uberhaupt een gebruiker hebben gevonden.
        if (count($stmt) == 1) {
            //Zorg voor een wachtwoord vergelijking.
            $pswrd = $_POST["password"];
            //Dit hier onder is voor het verifieren van het wachtwoord.
            if (password_verify($pswrd, $stmt[0]["password"])) {
                //Hier zetten we alles neer wat betreft de sessie variabelen.
                session_regenerate_id();
                $_SESSION['wachtwoord'] = $pswrd;
                $_SESSION["loggedin"] = TRUE;
                $_SESSION['role'] = $stmt[0]['role'];
                $_SESSION["name"] = $stmt[0]["username"];
                $_SESSION["id"] = $stmt[0]["id"];
                $_SESSION["identity"] = $stmt[0]["identity"];
                $_SESSION["op"] = $stmt[0]["opmerking"];
                header("Location: ../../user/dashboard.php");
                //anders vernietigd je maar de gehele session als het moet.
            } else {
                session_start();
                session_destroy();
                header("Location: ../../user/index.php?error=pass");
            }
        } else {
            session_start();
            session_destroy();
            header("Location: ../../user/index.php?error=sql");
        }
    }else{
        session_start();
        session_destroy();
        header("Location: ../../user/index.php?error=db");
    }
}
/* Einde Inlog Script  */
?>
