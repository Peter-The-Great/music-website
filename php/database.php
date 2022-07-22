<?php
//Basis database setup, eerst zetten we alle error_reporting aan.
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

//Hier laden we onze .env bestand, zodat we gebruik kunnen maken van alle omgevingsvariabelen.
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Gebruik de Medoo namespace.
use Medoo\Medoo;
global $database;
$database = new Medoo([
    // Alle variabelen die nodig zijn om te verbinden met de database.
    'type' => $_ENV['DB_DRIVER'],
    'host' => $_ENV['DB_SERVER'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD']
]);
?>