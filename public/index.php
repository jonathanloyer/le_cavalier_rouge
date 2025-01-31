<?php

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Charger le fichier .env explicitement
$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__).'/.env');

// Retourner une nouvelle instance du Kernel Symfony
return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
