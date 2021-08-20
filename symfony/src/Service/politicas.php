<?php

namespace App\Service;

use App\Service\httpClient;
use App\Entity\Conexiones;
use App\Service\FileUploader;

class politicas {

    private $client;
    private $politica_fichero;

    public function __construct(httpClient $client, FileUploader $uploader) {
        $this->client = $client;
        $this->politica_fichero = $uploader;
    }

    public function politicaDefecto($conexiones) {
        return $this->mayorEspacioLibre($conexiones);
    }

    public function mayorEspacioLibre($conexiones) {

        $max = 0;
        $eleccion = "";
        foreach ($conexiones as $conexion) {
            $actual = $this->client->about($conexion->getNombre())['free'];
            if ($actual > $max) {
                $max = $actual;
                $eleccion = $conexion->getAlias();
            }
        }
        return $eleccion;
    }

    public function menorNumeroArch($conexiones) {

        $min = $this->client->size($conexiones[0]->getNombre());
        $eleccion = "";
        foreach ($conexiones as $conexion) {
            $actual = $this->client->size($conexion->getNombre());
            if ($actual < $min) {
                $min = $actual;
                $eleccion = $conexion->getNombre();
            }
        }
        return $eleccion;
    }

    public function EleccionPolitica(int $id, $file, $conexiones) {

        $tipo = $this->politica_fichero->Politica_id($id)['Tipo'];
        $arg = $this->politica_fichero->Politica_id($id)['Args'];
        $Destino = $this->politica_fichero->Politica_id($id)['Destino'];   
        
        switch ($tipo) {
            case "Extension":
                $res=$this->politica_fichero->extension($file, $arg) ? $Destino : FALSE;
                break;
            case "Tamaño":
                $res=$this->politica_fichero->tamaño($file, $arg) ? $Destino : FALSE;
                break;
            case "Personal":
                $res=$Destino;
                break;
            case "Patron":
                $res=$this->politica_fichero->ContieneNombre($file, $arg) ? $Destino : FALSE;
                break;
            default:
                return $this->politicaDefecto($conexiones);
        }
        
        return ($res==FALSE ? $this->politicaDefecto($conexiones) : $res);
    }

}
