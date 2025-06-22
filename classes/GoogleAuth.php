<?php

// require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;

class GoogleAuth
{
    private $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    public function getLoginUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback()
    {
        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (!isset($token['error'])) {
                $this->client->setAccessToken($token['access_token']);

                $oauth2 = new Google_Service_Oauth2($this->client);
                $userInfo = $oauth2->userinfo->get();
                return [
                    'google_id' => $userInfo->id,
                    'name' => $userInfo->name,
                    'email' => $userInfo->email,
                    'avatar' => $userInfo->picture,
                    'verified_email' => $userInfo->verifiedEmail
                ];
            }
        }

        return false;
    }
}
