<?php
session_start();
require('database.php');
// Check of je bent ingelogd.
if (!isset($_SESSION["loggedin"])) {
    header("Location: ../user/index.php");
    exit();
}
//Hier moet een get die de id uit de url gaat halen
//Basic delete functie waarbij we een aanmelding gaan verwijderen.
$id = $_GET["id"];
$stmt = $database->select("register", ["id"], ["reisid" => $id]);
if(isset($stmt[0]["id"])){
    $database->delete("register", ["id" => $stmt[0]["id"]]);
}

header("location: ../user/dashboard.php");
?>