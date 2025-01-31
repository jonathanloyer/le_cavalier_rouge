<?php

namespace App\Service;

use MongoDB\Client;

class MongoDBClient
{
    private Client $client;
    private string $databaseName;

    public function __construct(string $mongodbUrl, string $mongodbDb)
    {
        $this->client = new Client($mongodbUrl);
        $this->databaseName = $mongodbDb;
    }

    public function getDatabase()
    {
        return $this->client->selectDatabase($this->databaseName);
    }
}
