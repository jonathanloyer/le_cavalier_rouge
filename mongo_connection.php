<?php

require 'vendor/autoload.php';
use MongoDB\Client;

try {
    // Utilisation du mot de passe correct
    $client = new Client("mongodb+srv://admin:C5ZQEve3GWP6aMDr@lecavalierrouge.d6jav.mongodb.net/?retryWrites=true&w=majority");
    $db = $client->selectDatabase('le_cavalier_rouge');
    echo "Connexion réussie à la base de données : " . $db->getDatabaseName() . PHP_EOL;
} catch (\Exception $e) {
    echo "Erreur : " . $e->getMessage() . PHP_EOL;
}
