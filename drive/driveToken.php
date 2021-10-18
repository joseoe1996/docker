<?php

require __DIR__ . '/vendor/autoload.php';

use League\OAuth2\Client\Provider\Google;

class driveToken {

    protected $provider;

    public function __construct(string $client_id, string $secreto, string $uri) {
        $this->provider = new Google([
            'clientId' => $client_id,
            'clientSecret' => $secreto,
            'redirectUri' => $uri,
        ]);
    }
    

    public function getToken() {
        if (!empty($_GET['error'])) {

            // Got an error, probably user denied access
            exit('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'));
        } elseif (empty($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $this->provider->getAuthorizationUrl([
                'prompt' => 'consent',
                'access_type' => 'offline',
                'scope' => ['https://www.googleapis.com/auth/drive']
            ]);
            $_SESSION['oauth2state'] = $this->provider->getState();
            header('Location: ' . $authUrl);
            exit;
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            // State is invalid, possible CSRF attack in progress
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'],
            ]);
            return $token;
        }
    }
    
    public function token($token){
        if($token!=NULL){
        $tiempo = new DateTime();
        $i = new DateInterval('PT1H');
        $tiempo->add($i);

        $youraccesstoken = $token->getToken();
        $yourrefreshtoken = $token->getRefreshToken();
        $type = $token->getValues()['token_type'];
        $expiry = $tiempo->format("Y-m-d\TH:i:s.uP");
        
        $res = '{' .
                '"access_token":' . '"' . $youraccesstoken . '"' .
                ',"token_type":' . '"' . $type . '"' .
                ',"refresh_token":' . '"' . $yourrefreshtoken . '"' .
                ',"expiry":' . '"' . $expiry . '"' .
                '}';
        return $res;
        } else {
            echo 'Token nullo <br>';
        }
    }

}

/*
session_start(); // Remove if session.auto_start=1 in php.ini
$clientId="673961889608-7bhejsqnglluor9prgrb03e13g3s18mg.apps.googleusercontent.com";
$secreto="tzXjmMQkz1qZ90FNNDtl2XKy";
$uri="http://localhost/drive/driveToken.php";
$objeto=new driveToken($clientId, $secreto, $uri);
$token=$objeto->getToken();
//print_r($token->getValues());
print_r($token->jsonSerialize());
//$jwt=$token->getValues()['id_token'];
//echo $jwt;
//echo $objeto->token($token);
*/