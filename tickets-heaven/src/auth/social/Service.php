<?php

namespace social;

use GuzzleHttp\Client;

abstract class Service {

    protected $c;

    protected $client;

    public function __construct($container, Client $client) {

        $this->c = $container;

        $this->client = $client;
    }

    abstract public function getAuthorizeUrl();

    abstract public function getUserByCode($code);

    public function authorizeUrl() {

        return $this->getAuthorizeUrl();
    }

    public function getUser($code) {
        
        return $this->getUserByCode($code);
    }
}
