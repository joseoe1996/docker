<?php

namespace App\Service;

use App\Service\BD;

class Varios {

    public function filtrado($ssdp, $criterio, $busqueda) {

        $final = [];

        foreach ($ssdp as $valor) {
            $IP = $valor['IP'];
            $nombreBD = preg_replace('[\.]', '_', $IP) . '_alias';
            $criterio['nombre'] = $nombreBD;
            $conexiones = $busqueda->findBy($criterio);
            //No se encunetra en la BD
            if (!$conexiones) {
                $final[] = $valor;
            }
            array_pop($criterio);
        }
        return $final;
    }

    public function busqueda(string $buscar, array $lista) {

        foreach ($lista as $conexion => $value) {
            foreach ($value as $tipo => $archivo) {
                foreach ($archivo as $nombre => $path) {
                    if (!preg_match('*' . strtolower($buscar) . '*', strtolower($nombre))) {
                        unset($lista[$conexion][$tipo][$nombre]);
                    }
                }
            }
            if (empty($lista[$conexion]['carpetas']) && empty($lista[$conexion]['archivos'])) {
                unset($lista[$conexion]);
            }
        }
        return $lista;
    }
}
