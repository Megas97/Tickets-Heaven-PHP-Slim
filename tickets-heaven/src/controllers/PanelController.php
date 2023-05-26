<?php

namespace controllers;

use controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PanelController extends Controller {

    public function getAdminPanel(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'admin/panel.twig');
    }

    public function getHostPanel(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'user/host/panel.twig');
    }

    public function getOwnerPanel(Request $request, Response $response) {

        return $this->c->get('view')->render($response, 'user/owner/panel.twig');
    }

    public function getArtistPanel(Request $request, Response $response) {
        
        return $this->c->get('view')->render($response, 'user/artist/panel.twig');
    }
}
