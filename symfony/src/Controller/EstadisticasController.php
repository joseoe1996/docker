<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\httpClient;
use App\Repository\HistorialRepository;

class EstadisticasController extends AbstractController {

    /**
     * @Route("/inicio/lista_conexion/estadisticas/{conexion}", name="estadisticas_conexion")
     */
    public function index(string $conexion, httpClient $client, HistorialRepository $historialRepo): Response {

        $about = $client->about($conexion);
        $criteria = ['user' => $this->getUser()];
        $historial = $historialRepo->findBy($criteria);
        return $this->render('estadisticas/index.html.twig', [
                    'controller_name' => 'Estadisticas',
                    'conexion' => $conexion,
                    'about' => json_decode($about),
                    'historial' => $historial
        ]);
    }

}
