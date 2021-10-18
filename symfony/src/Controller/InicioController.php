<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InicioController extends AbstractController {

    /**
     * @Route("/inicio", name="inicio")
     Inicio del usuario 
     */
    public function index(): Response
    {
    	//Obtenemos su nombre
        $userlog = $this->getUser()->getUsername();
        return $this->render('inicio.html.twig', [
            'controller_name' => 'Bienvenido '. $userlog,
	]);
    }

}
