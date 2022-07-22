<?php
session_start();
require('database.php');
//Check of je bent ingelogd
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
//Hier moet een get die de id uit de url gaat halen
//basic delete functie voor het verwijderen van de reis afbeelding.
$id = $_GET["id"];
$unlink = "../";
$stmt = $database->select("reizen", ['image'], ['id' => $id]);
unlink($unlink.$stmt[0]['image']);

//Hier verwijderen we niet alleen de reis, maar ook alle reis bookingen.
if(isset($id)){
    $database->delete("reizen", ["id" => $id]);
    $database->delete("register", ["reisid" => $id]);
}

header("location: ../user/dashboard.php");
?>