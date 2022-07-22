<?php
session_start();
require('database.php');
//Check of je bent ingelogd.
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
//Hier moet een get die de id uit de url gaat halen
//basis delete functie waar we het profiel en de profiel foto verwijderen.
$id = $_GET["id"];
$role = $_SESSION['role'];
$unlink = "../";
$stmt = $database->select("users", ['image'], ['id' => $id]);
unlink($unlink.$stmt[0]['image']);

// En natuurlijk verwijderen we alle referenties naar de student in het register als de gebruiker een student is.
if ($role == intval(0)){
    $database->delete("register", ["studentid" => $id]);
}

if(isset($id)){
    $database->delete("users", ["id" => $id]);
}

header("location: login/logout.php");
?>