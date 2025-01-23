<?php

namespace App;



class DIConfigurator
{

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }


    public function getUrlForDbConnector(): string
    {
        return (string)$_ENV['DATABASE_URL'];
    }

    public  function getUrlForDbConnectorTest(): string
    {
        return (string)$_ENV['DATABASE_URL_TEST'];
    }
}
