<?php

namespace middleware;

class Middleware {

    protected $c;

    public function __construct($container) {
        
        $this->c = $container;
    }
}
