<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InicioController extends AbstractController {

    /**
     * @Route("/inicio", name="inicio")
     */
<<<<<<< HEAD
    public function index(): Response
    {
        $userlog = $this->getUser()->getUsername();
        return $this->render('inicio.html.twig', [
            'controller_name' => 'Bienvenido '. $userlog,
=======
    public function index(): Response {

        $userlog = $this->getUser();
        
        return $this->render('inicio.html.twig', [
                    'controller_name' => 'InicioController',
>>>>>>> 5a8e91f5a1154c3b33e63c008af893c3d172c406
        ]);
    }

}
