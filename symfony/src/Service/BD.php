<?php

namespace App\Service;

use App\Entity\Conexiones;

class BD {

    private $em;

    public function __construct($em) {
        $this->em = $em;
    }

    //Crear entrada de tipo conexion en la base de datos
    public function C_conexion(string $name, $user, string $alias, string $tipo) {

        $conexion = new Conexiones();
        $conexion->setNombre($name);
        $conexion->setTipo($tipo);
        $conexion->setUser($user);
        $conexion->setAlias($alias);
        //Base de datos
        try {
            $this->em->persist($conexion);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
    
    public function B_conexion(Conexiones $conexion) {
        try {
            $this->em->remove($conexion);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
    
    public function E_conexion(Conexiones $conexion, string $alias){
        
        $conexion->setAlias($alias);
        try {
            $this->em->persist($conexion);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

}
