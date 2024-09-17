<?php

namespace App\Config;

use App\Traits\Singleton;

class Config
{
    use Singleton;

    private array $dbSettings = [];

    public function __construct()
    {
        $this->init();
    }

    protected function init(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();

        $this->dbSettings = [
            "host" => $this->getEnv("DB_HOST"),
            "user" => $this->getEnv("DB_USER"),
            "password" => $this->getEnv("DB_PASSWORD"),
            "database" => $this->getEnv("DB_DATABASE"),
            "port" =>  $this->getEnv("DB_PORT"),
            "driver" =>  $this->getEnv("DB_DRIVER"),
        ];
    }

    public function getDBSettings(): array
    {
        return $this->dbSettings;
    }

    public function getEnv(string $env): string
    {
        return $_ENV[$env];
    }
}
