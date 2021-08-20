<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\httpClient;
use App\Repository\ConexionesRepository;
use Symfony\Component\HttpFoundation\Request;

class ListaController extends AbstractController {

    /**
     * @Route("/inicio/lista/{conexion}/{ruta}", name="lista_archivos", requirements={"ruta"=".+"})
     */
    public function index(httpClient $cliente, ConexionesRepository $conerepo, Request $request, string $ruta = "", string $conexion = ""): Response {

        $userlog = $this->getUser()->getId();
        $busqueda = $request->get('busqueda');

        $final = $ruta;
        $lista = array();
        $alias = array();

        if (!empty($conexion)) {
            $criteria = ['nombre' => $conexion];
            $conexiones_BD = $conerepo->findBy($criteria);
        } else {
            $criteria = ['user' => $userlog];
            $conexiones_BD = $conerepo->findBy($criteria);
        }
        foreach ($conexiones_BD as $array) {
            
            $archivos_asociados = $cliente->lista($array->getNombre(), $final);
            $separados = $cliente->separar($archivos_asociados);
            $carpetas = $separados['carpeta'];
            $archivos = $separados['archivos'];
            $lista[$array->getNombre()] = ['carpetas' => $carpetas, 'archivos' => $archivos];
            $alias[$array->getNombre()] = $array->getAlias();
        }

        if (!empty($busqueda)) {
            $lista2 = $cliente->busqueda($busqueda, $lista);
            $lista = $lista2;
            if (empty($lista)) {
                return $this->render('noEncontrado.html.twig', [
                            'titulo' => 'Elemento no encontrado',
                ]);
            }
        }

        return $this->render('lista/index.html.twig', [
                    'controller_name' => 'ListaController',
                    'lista' => $lista,
                    'alias' => $alias
        ]);
    }

    /**
     * @Route("/inicio/lista_borrar_archivo/{conexion}/{ruta}", name="borrar_archivo", requirements={"ruta"=".+"})
     */
    public function borrarARCH(httpClient $client, string $ruta = "", string $conexion = "") {

        // $ruta2 = preg_replace('/_/', '/', $ruta);
        $client->borrarARCH($conexion, $ruta);
        return $this->redirectToRoute('lista_archivos');
    }

    /**
     * @Route("/inicio/lista_borrar_carpeta/{conexion}/{ruta}", name="borrar_carpeta", requirements={"ruta"=".+"})
     */
    public function borrarCARP(httpClient $client, string $ruta = "", string $conexion = "") {

        // $ruta2 = preg_replace('/_/', '/', $ruta);
        $client->borrarCARP($conexion, $ruta);
        return $this->redirectToRoute('lista_archivos');
    }

}
