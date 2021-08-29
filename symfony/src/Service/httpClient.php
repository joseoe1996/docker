<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\onedriveToken;
use App\Service\driveToken;

const NOMBRE = 'jose';
const PASS = 'jose';
const IP = '127.0.0.1';
//const IP = 'rclone';
const PUERTO = '5572';
const DIR = NOMBRE .':'.PASS.'@'.IP . ':' . PUERTO;
const CLIENT_ID_ONEDRIVE = '088e81a1-5274-44dd-bae8-fe657686b19f';
const SECRETO_ONEDRIVE = 'Ag4.cX~HE-x27aLO8W.9a~rZ77e_iqR3H_';
const CLIENTE_ID_DRIVE = '673961889608-7bhejsqnglluor9prgrb03e13g3s18mg.apps.googleusercontent.com';
const SECRETO_DRIVE = 'tzXjmMQkz1qZ90FNNDtl2XKy';

class httpClient {

    private $client;

    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

    public function POST($param, string $operacion) {
        $response = $this->client->request('POST', 'http://' . DIR . $operacion, [
            // these values are automatically encoded before including them in the URL
            'query' =>
            $param
        ]);
        return $response;
    }

    public function sspd() {
        $response = $this->client->request('POST', 'http://' . '192.168.0.108:8000');
        return $response->toArray();
    }

    //Listar todos los archivos de una conexion
    public function lista(string $nombre, string $remote) {
        $parametros = ['fs' => $nombre . ':', 'remote' => $remote];
        $operacion = '/operations/list';
        $content = $this->POST($parametros, $operacion)->toArray();
        return $content;
    }

    //Dividir un directorio entre carpetas y archivos
    public function separar($lista) {
        $carpeta = array();
        $archivos = array();
        foreach ($lista as $arch) {
            foreach ($arch as $value) {
                if ($value['IsDir'] == 1) {
                    $carpeta[$value['Name']] = $value['Path']; //preg_replace('~/~', '_', $value['Path']);
                } else {
                    $archivos[$value['Name']] = $value['Path']; //preg_replace('~/~', '_', $value['Path']);
                }
            }
        }
        $res = array('carpeta' => $carpeta, 'archivos' => $archivos);
        return $res;
    }

    //Crear la conexion onedrive
    public function onedrive() {
        //Crear el token de validacion y el nombre de la conexion
        $objeto = new onedriveToken();
        $token = $objeto->obtenerToken();
        $token_modificado = $objeto->token($token);
        $id = $objeto->getID();
        $alias = $objeto->getName();
        $name = $id . "_onedrive";

        $json = array("config_is_local" => "false"
            , "config_refresh_token" => "false"
            , "client_id" => CLIENT_ID_ONEDRIVE
            , "client_secret" => SECRETO_ONEDRIVE
            , "region" => 'global'
            , "drive_id" => $id
            , "drive_type" => 'personal'
            , "token" => $token_modificado
        );
        //Creamos la conexion con RCLONE
        $operacion = '/config/create';
        $parametros = ['name' => $name,
            'type' => 'onedrive',
            'obscure' => 'true',
            'parameters' => json_encode($json)];
        $this->POST($parametros, $operacion);

        return ['alias' => $alias, 'nombre' => $name];
    }

    //Crear conexion drive
    public function drive() {

        $objeto = new driveToken();
        $token = $objeto->getToken();
        $token_final = $objeto->token($token);

        $jwt = $token->getValues()['id_token'];
        $claves = preg_split('[\.]', $jwt);

        $alias = json_decode(base64_decode($claves[1]))->name;
        $name = json_decode(base64_decode($claves[1]))->sub . '_drive';

        $json = array("config_is_local" => "false"
            , "config_refresh_token" => "false"
            , "client_id" => CLIENTE_ID_DRIVE
            , "client_secret" => SECRETO_DRIVE
            , "token" => $token_final
        );
        //Creamos la conexion con RCLONE
        $operacion = '/config/create';
        $parametros = [
            'name' => $name,
            'type' => 'drive',
            'obscure' => 'true',
            'parameters' => json_encode($json)
        ];
        $this->POST($parametros, $operacion);

        return ['alias' => $alias, 'nombre' => $name];
    }

    public function sftp(string $IP, string $user, string $pass) {

        $json = array(
            "host" => $IP
            , "user" => $user
            , "pass" => $pass
            , "port" => 2222
        );
        $name = preg_replace('[\.]', '_', $IP);

        $operacion = '/config/create';
        $parametros = [
            'name' => $name,
            'type' => 'sftp',
            'obscure' => 'true',
            'parameters' => json_encode($json)
        ];
        //Creamos la conexion con RCLONE
        $this->POST($parametros, $operacion);
    }

    public function borrarARCH(string $conexion, string $ruta) {
        $operacion = '/operations/deletefile';
        $parametros = ['fs' => $conexion . ':', 'remote' => $ruta];
        $this->POST($parametros, $operacion);
    }

    public function borrarCARP(string $conexion, string $ruta) {
        $operacion = '/operations/purge';
        $parametros = ['fs' => $conexion . ':', 'remote' => $ruta];
        $this->POST($parametros, $operacion);
    }

    public function borrarConexion(string $conexion) {
        $operacion = '/config/delete';
        $parametros = ['name' => $conexion];
        $this->POST($parametros, $operacion);
    }

    public function copiar_subir($origen, $destino, $nombre_final) {
        $operacion = '/operations/copyfile';
        //$parametros = ['srcFs' => "/home/", 'srcRemote' => $origen, 'dstFs' => $destino . ':', 'dstRemote' => $nombre_final];
        $parametros = ['srcFs' => "C:/", 'srcRemote' => $origen, 'dstFs' => $destino . ':', 'dstRemote' => $nombre_final];
        $reponse = $this->POST($parametros, $operacion);
        return $reponse;
    }

    public function copiar_bajar($origen, $destino, $nombre_final) {
        $operacion = '/operations/copyfile';
        /*$parametros = ['srcFs' => $origen . ':', 'srcRemote' => $nombre_final,
		'dstFs' => "/home/", 'dstRemote' => $destino];*/
        $parametros = ['srcFs' => $origen . ':', 'srcRemote' => $nombre_final, 'dstFs' => "C:/", 'dstRemote' => $destino];
        $response = $this->POST($parametros, $operacion);
        return $response;
    }

    public function about(string $conexion) {
        $operacion = '/operations/about';
        $parametros = ['fs' => $conexion . ':'];
        $reponse = $this->POST($parametros, $operacion);
        return $reponse->toArray();
    }

    public function info_remote(string $conexion) {
        $operacion = '/operations/fsinfo';
        $parametros = ['fs' => $conexion . ':'];
        $reponse = $this->POST($parametros, $operacion);
        return $reponse->toArray();
    }

    //Creamos un alias para la conexion del movil, para que no sea tan larga
    public function alias(string $nombre, string $alias) {
        $operacion = '/config/create';
        $json = array(
            "remote" => $alias
        );
        $parametros = [
            'name' => $nombre,
            'type' => 'alias',
            'parameters' => json_encode($json)
        ];
        //Creamos la conexion con RCLONE
        $this->POST($parametros, $operacion);
    }

    public function size(string $conexion) {
        $operacion = '/operations/size';
        $parametros = ['fs' => $conexion . ':'];
        $reponse = $this->POST($parametros, $operacion);
        return $reponse->toArray()['bytes'];
    }

}
