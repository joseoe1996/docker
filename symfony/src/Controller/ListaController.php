<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\httpClient;
use App\Repository\ConexionesRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Varios;

class ListaController extends AbstractController {

    /**
     * @Route("/inicio/lista/{conexion}/{ruta}", name="lista_archivos", requirements={"ruta"=".+"})
     Listar todos los elementos de las conexiones
     */
    public function index(httpClient $cliente, ConexionesRepository $conerepo, Request $request, Varios $varios, string $ruta = "", string $conexion = ""): Response {

        $userlog = $this->getUser()->getId();
        //Para saber si se ha pulsado la busqueda
        $busqueda = $request->get('busqueda');

        $final = $ruta;
        $lista = array();
        $alias = array();

	//Para saber si mostrar todas las conexiones o hemos accedido a una concreta
        if (!empty($conexion)) {
            $criteria = ['nombre' => $conexion];
            $conexiones_BD = $conerepo->findBy($criteria);
        } else {
            $criteria = ['user' => $userlog];
            $conexiones_BD = $conerepo->findBy($criteria);
        }
        //Recorremos las conexiones del usuario activo
        foreach ($conexiones_BD as $array) {
           //Obtenemos todos los elementos de a conexion
            $archivos_asociados = $cliente->lista($array->getNombre(), $final);
            //Dividimos entre carpetas y archivos
            $separados = $cliente->separar($archivos_asociados);
            $carpetas = $separados['carpeta'];
            $archivos = $separados['archivos'];
            $lista[$array->getNombre()] = ['carpetas' => $carpetas, 'archivos' => $archivos];
            $alias[$array->getNombre()] = $array->getAlias();
        }

	//Comprobamos si se ha pulsado la busqueda
        if (!empty($busqueda)) {
            //Se filtra por la cadena de busqueda
            $lista2 = $varios->busqueda($busqueda, $lista);
            $lista = $lista2;
            //Si es vacia se devuelve una pagina de error
            if (empty($lista)) {
                return $this->render('noEncontrado.html.twig', [
                            'titulo' => 'Elemento no encontrado',
                ]);
            }
        }

        return $this->render('lista/index.html.twig', [
                    'controller_name' => 'Lista de archivos de cada conexion',
                    'lista' => $lista,
                    'alias' => $alias
        ]);
    }

    /**
     * @Route("/inicio/lista_borrar_archivo/{conexion}/{ruta}", name="borrar_archivo", requirements={"ruta"=".+"})
     */
    public function borrarARCH(httpClient $client, string $ruta = "", string $conexion = "") {
	//Borrar el archivo a traves de Rclone
        $client->borrarARCH($conexion, $ruta);
        return $this->redirectToRoute('lista_archivos');
    }

    /**
     * @Route("/inicio/lista_borrar_carpeta/{conexion}/{ruta}", name="borrar_carpeta", requirements={"ruta"=".+"})
     */
    public function borrarCARP(httpClient $client, string $ruta = "", string $conexion = "") {
	//Borrar la carpeta a traves de rclone
        $client->borrarCARP($conexion, $ruta);
        return $this->redirectToRoute('lista_archivos');
    }

}
