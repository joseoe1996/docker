<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\httpClient;

class EstadisticasController extends AbstractController {

    /**
     * @Route("/inicio/lista_conexion/estadisticas/{conexion}", name="estadisticas_conexion")
     */
    public function index(string $conexion, httpClient $client): Response {
        
        $about= $client->about($conexion);
       // var_dump($about['free']);
       print_r($client->info_remote($conexion));
        return $this->render('estadisticas/index.html.twig', [
                    'controller_name' => 'EstadisticasController',
                    'conexion' => $conexion,
                    'about'=> implode(" ", $about)
        ]);
    }

}
