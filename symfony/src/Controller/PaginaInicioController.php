<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaginaInicioController extends AbstractController
{
    /**
     * @Route("/", name="pagina_inicio")
     */
    public function index(): Response
    {
        return $this->render('pagina_inicio/index.html.twig', [
            'controller_name' => 'PaginaInicioController',
        ]);
    }
}
