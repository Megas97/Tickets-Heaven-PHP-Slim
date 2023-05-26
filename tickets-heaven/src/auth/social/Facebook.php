<?php

namespace social;

use social\Service;

class Facebook extends Service {

    public function getAuthorizeUrl() {

        $clientId = $this->c->get('settings')['auth']['facebook']['client_id'];

        $redirectUri = $this->c->get('settings')['auth']['facebook']['redirect_uri'];

        return 'https://www.facebook.com/dialog/oauth?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&scope=email,public_profile';
    }

    public function getUserByCode($code) {

        $token = $this->getAccessTokenFromCode($code);

        return $this->normalizeUser($this->getUserByToken($token));
    }

    protected function getUserByToken($token) {

        $response = $this->client->request('GET', 'https://graph.facebook.com/me', [
            'query' => [
                'access_token' => $token,
                'fields' => 'id,name,email,picture.type(large)',
            ]
        ])->getBody();

        return json_decode($response);
    }

    protected function getAccessTokenFromCode($code) {

        $response = $this->client->request('GET', 'https://graph.facebook.com/v2.3/oauth/access_token', [
            'query' => [
                'client_id' => $this->c->get('settings')['auth']['facebook']['client_id'],
                'client_secret' => $this->c->get('settings')['auth']['facebook']['client_secret'],
                'redirect_uri' => $this->c->get('settings')['auth']['facebook']['redirect_uri'],
                'code' => $code,
            ]
        ])->getBody();

        return json_decode($response)->access_token;
    }

    protected function normalizeUser($user) {
        
        return (object) [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'picture' => $user->picture->data->url,
        ];
    }
}
