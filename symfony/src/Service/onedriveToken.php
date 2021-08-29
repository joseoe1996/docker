<?php

namespace App\Service;

//require '/var/www/onedrive/vendor/autoload.php';
require 'C:\xampp\htdocs\onedrive\vendor\autoload.php';

use TheNetworg\OAuth2\Client\Provider as Provider;
use Symfony\Component\HttpFoundation\RedirectResponse;

class onedriveToken {

    protected $provider;
    protected $id;
    protected $name;

    public function __construct() {
        $this->provider = new Provider\Azure([
            'clientId' => '088e81a1-5274-44dd-bae8-fe657686b19f',
            'clientSecret' => 'Ag4.cX~HE-x27aLO8W.9a~rZ77e_iqR3H_',
            'redirectUri' => 'http://localhost:8000/inicio/lista_conexion/crear_onedrive',
            //Optional
            'scopes' => ['openid'],
            //Optional
            'defaultEndPointVersion' => '2.0'
        ]);
        // Set to use v2 API, skip the line or set the value to Azure::ENDPOINT_VERSION_1_0 if willing to use v1 API
        $this->provider->defaultEndPointVersion = Provider\Azure::ENDPOINT_VERSION_2_0;

        //$baseGraphUri = $this->provider->getRootMicrosoftGraphUri(null);
        //$this->provider->scope = 'openid profile email offline_access ' . $baseGraphUri . '/User.Read';
        $this->provider->scope = 'openid profile email offline_access ';
        $this->provider->scope .= 'Files.Read Files.ReadWrite Files.Read.All Files.ReadWrite.All User.Read';
    }

    public function hayCodigo() {
        if (isset($_GET['code']) && isset($_SESSION['OAuth2.state']) && isset($_GET['state'])) {
            return TRUE;
        }
        return FALSE;
    }

    public function getToken() {
        try {

            if ($_GET['state'] == $_SESSION['OAuth2.state']) {
                unset($_SESSION['OAuth2.state']);

                // Try to get an access token (using the authorization code grant)
                /** @var AccessToken $token */
                $token = $this->provider->getAccessToken('authorization_code', [
                    'scope' => $this->provider->scope,
                    'code' => $_GET['code'],
                ]);

                // Verify token
                // Save it to local server session data

                $ref = 'https://graph.microsoft.com/v1.0/me/';
                $prueba = $this->provider->get($ref, $token);
                $this->id = $prueba['id'];
                $this->name = $prueba['displayName'];
                return $token;
            } else {
                echo 'Invalid state';

                return null;
            }
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $exc) {
            echo $exc->getCode() . "<br>";
            echo $exc->getMessage() . "<br>";
        }
    }

    public function urlAuth() {
        $authorizationUrl = $this->provider->getAuthorizationUrl(['scope' => $this->provider->scope]);
        $_SESSION['OAuth2.state'] = $this->provider->getState();
        $response = new RedirectResponse($authorizationUrl);
        $response->send();
    }

    public function obtenerToken() {
        if ($this->hayCodigo()) {
            return $this->getToken();
        } else {
            $this->urlAuth();
        }
    }

    public function token($token) {

        $tiempo = new \DateTime();
        $i = new \DateInterval('PT1H');
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
    }

    function getID() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

}

/*
  session_start();

  $client_id = '088e81a1-5274-44dd-bae8-fe657686b19f';
  $client_secret = 'Ag4.cX~HE-x27aLO8W.9a~rZ77e_iqR3H_';

  $redirect_uri = 'http://localhost:5572/inicio/crear_conexion';

  $objeto = new onedriveToken($client_id, $client_secret, $redirect_uri);
  $token = $objeto->obtenerToken();
  echo $objeto->token($token) . "<br>";
  echo $objeto->getId() . "<br>";
  // print_r($objeto->provider->scope) ;
 */
?>
