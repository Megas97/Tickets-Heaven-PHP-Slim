<?php

namespace social;

use social\Service;

class GitHub extends Service {

    public function getAuthorizeUrl() {

        $clientId = $this->c->get('settings')['auth']['github']['client_id'];

        $redirectUri = $this->c->get('settings')['auth']['github']['redirect_uri'];

        $_SESSION['social_state'] = $this->c->get('randomlib')->generateString(128);

        return 'https://github.com/login/oauth/authorize?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&scopes=user,user:email&state=' . $_SESSION['social_state'];
    }

    public function getUserByCode($code) {

        $token = $this->getAccessTokenFromCode($code);

        return $this->normalizeUser($this->getUserByToken($token));
    }

    protected function getUserByToken($token) {

        $response = $this->client->request('GET', 'https://api.github.com/user', [
            'headers' => [
                'authorization' => 'token ' . $token,
            ]
        ])->getBody();

        return json_decode($response);
    }

    protected function getAccessTokenFromCode($code) {

        $response = $this->client->request('GET', 'https://github.com/login/oauth/access_token', [
            'query' => [
                'client_id' => $this->c->get('settings')['auth']['github']['client_id'],
                'client_secret' => $this->c->get('settings')['auth']['github']['client_secret'],
                'redirect_uri' => $this->c->get('settings')['auth']['github']['redirect_uri'],
                'code' => $code,
                'state' => $_SESSION['social_state'],
            ],
            'headers' => [
                'accept' => 'application/json',
            ]
        ])->getBody();
        
        if (property_exists(json_decode($response), "access_token")) {

            return json_decode($response)->access_token;
        }
    }

    protected function normalizeUser($user) {
        
        return (object) [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'picture' => $user->avatar_url,
        ];
    }
}
