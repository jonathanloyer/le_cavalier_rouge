<?php

namespace App\Service;

use MongoDB\Client;

class MongoDBClient
{
    private Client $client;
    private $database;

    public function __construct(string $mongodbUrl, string $mongodbDb)
    {
        $this->client = new Client($mongodbUrl);
        $this->database = $this->client->selectDatabase($mongodbDb);
    }

    public function getDatabase()
    {
        return $this->database;
    }
}
