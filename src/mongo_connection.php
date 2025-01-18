<?php

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

try {
    // Remplacez l'URL par la vôtre
    $mongoClient = new Client('mongodb+srv://admin:A9uQIgJsHMOCB2fW@cluster.mongodb.net/le_cavalier_rouge');
    $databases = $mongoClient->listDatabases();

    echo "Connexion réussie à MongoDB ! Voici les bases de données disponibles :\n";
    foreach ($databases as $database) {
        echo "- " . $database->getName() . "\n";
    }
} catch (\Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
