<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\phpSSDP;

class InicioController extends AbstractController
{
    /**
     * @Route("/", name="pagina_inicio")
     */
    public function index(): Response
    {
    	$disponibles = phpSSDP::getDevicesByURN('urn:schemas-upnp-org:service:ContentDirectory:1');
        return $this->json($disponibles);

    }
}

