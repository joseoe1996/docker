<?php

require __DIR__ . '/vendor/autoload.php';

class onedriveToken {

    protected $provider;
    protected $id;

    public function __construct(string $client_id, string $client_secret, string $redirect_uri) {
        $this->provider = new TheNetworg\OAuth2\Client\Provider\Azure([
            'clientId' => $client_id,
            'clientSecret' => $client_secret,
            'redirectUri' => $redirect_uri,
            //Optional
            'scopes' => ['openid'],
            //Optional
            'defaultEndPointVersion' => '2.0'
        ]);

        // Set to use v2 API, skip the line or set the value to Azure::ENDPOINT_VERSION_1_0 if willing to use v1 API
        $this->provider->defaultEndPointVersion = TheNetworg\OAuth2\Client\Provider\Azure::ENDPOINT_VERSION_2_0;

        //$baseGraphUri = $this->provider->getRootMicrosoftGraphUri(null);
        //$this->provider->scope = 'openid profile email offline_access ' . $baseGraphUri . '/User.Read';
        $this->provider->scope = 'openid profile email offline_access ';
        $this->provider->scope .='Files.Read Files.ReadWrite Files.Read.All Files.ReadWrite.All User.Read';
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
                //echo $token->getToken();
                $ref = 'https://graph.microsoft.com/v1.0/me/';
                $prueba = $this->provider->get($ref, $token);
                $this->id = $prueba['id'];
                return $token;
            } else {
                echo 'Invalid state';

                return null;
            }
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $exc) {
            echo $exc->getCode() . "<br>";
            echo $exc->getMessage() . "<br>";
            //echo $exc->getTraceAsString() . "<br>";
            //print_r($exc->getResponseBody());
        }
    }

    public function urlAuth() {
        $authorizationUrl = $this->provider->getAuthorizationUrl(['scope' => $this->provider->scope]);
        $_SESSION['OAuth2.state'] = $this->provider->getState();

        header('Location: ' . $authorizationUrl);
    }

    public function obtenerToken() {
        if ($this->hayCodigo()) {
            return $this->getToken();
        } else {
            $this->urlAuth();
        }
    }

    public function token($token) {

        $tiempo = new DateTime();
        $i = new DateInterval('PT1H');
        $tiempo->add($i);

        $youraccesstoken = $token->getToken();
        $yourrefreshtoken = $token->getRefreshToken();
        $type = $token->getValues()['token_type'];
        $expiry = $tiempo->format("Y-m-d\TH:i:s.uP");
        /*
          echo '{' .
          '"access_token":' . '"' . $youraccesstoken . '"' .
          ',"token_type":' . '"' . $type . '"' .
          ',"refresh_token":' . '"' . $yourrefreshtoken . '"' .
          ',"expiry":' . '"' . $expiry . '"' .
          '}'
          ;

         */
        $res = '{' .
                '"access_token":' . '"' . $youraccesstoken . '"' .
                ',"token_type":' . '"' . $type . '"' .
                ',"refresh_token":' . '"' . $yourrefreshtoken . '"' .
                ',"expiry":' . '"' . $expiry . '"' .
                '}';
        return $res;
    }

    function getId() {
        return $this->id;
    }

}

/*
  session_start();

  //$client_id='388dc16a-294e-4623-852e-af93d9f99f5b';
  //$client_secret='xOyRvyYdkN5RH-6Pv2Mj_-Y6moX_2o3BKz';

  $client_id = '088e81a1-5274-44dd-bae8-fe657686b19f';
  $client_secret = 'Ag4.cX~HE-x27aLO8W.9a~rZ77e_iqR3H_';

  $redirect_uri = 'http://localhost/onedrive/onedriveToken.php';

  $objeto = new onedriveToken($client_id, $client_secret, $redirect_uri);
  $token = $objeto->obtenerToken();
  echo $objeto->token($token) . "<br>";
  echo $objeto->getId() . "<br>";
 // print_r($objeto->provider->scope) ;
 */
?>