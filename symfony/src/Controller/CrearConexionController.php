<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\httpClient;
use App\Repository\ConexionesRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Service\BD;
use App\Service\Varios;

const SERVER_MOVIL = "CyberLinkJava";
const SERVER_ORDE = "DLNADOC";

class CrearConexionController extends AbstractController {

    /**
     * @Route("/inicio/lista_conexion", name="lista_conexion")
     */
    public function index(ConexionesRepository $con, httpClient $client, Varios $varios): Response {

        $userlog = $this->getUser()->getId();

        //Lista de conexiones del susario actual
        $criteria = ['user' => $userlog];
        $conexiones = $con->findBy($criteria);
        //Listar conexiones SFTP disponibles
        $disponibles2 = $client->sspd();
        
        //var_dump($disponibles2);
        //return new Response();
        
        $movil = ['IP' => '192.168.0.103', 'UUID' => '6e346cda-383a-49de-8e55-716660c865b6', 'SERVER' => 'CyverLinkJava',
            'DESCRIPTION' => ['friendlyName' => 'Jose Movil']];
        $ordenador = ['IP' => '192.168.0.107', 'UUID' => '4d696e69-444c-164e-9d41-000c29d538cc', 'SERVER' => 'DLNADOC',
            'DESCRIPTION' => ['friendlyName' => 'ubuntu 2014: mini dlna']];
        $disponibles = [$movil, $ordenador];
        //Comporar con las que ya esten en la BD
	
        $final=$varios->filtrado($disponibles2,$criteria,$con);
                
        
        return $this->render('crear_conexion/index.html.twig', [
                    'controller_name' => 'CrearConexionController',
                    'conexiones' => $conexiones,
                    'sftp' => $final
        ]);
    }

    /**
     * @Route("/inicio/lista_conexion/crear_onedrive", name="crear_onedrive")
     */
    public function onedrive(httpClient $client): Response {
        $ayuda = $client->onedrive();
        $BD = new BD($this->getDoctrine()->getManager());
        $BD->C_conexion($ayuda['nombre'], $this->getUser(), $ayuda['alias'], 'onedrive');
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/crear_drive", name="crear_drive")
     */
    public function drive(httpClient $client): Response {

        $ayuda = $client->drive();
        $BD = new BD($this->getDoctrine()->getManager());
        $BD->C_conexion($ayuda['nombre'], $this->getUser(), $ayuda['alias'], 'drive');
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/crear_sftp", name="crear_sftp")
     */
    public function sftp(httpClient $client, Request $request): Response {

        $usuario = $request->get('user');
        $pas = $request->get('password');
        $IP = $request->get('IP');
        $server = $request->get('SERVER');

        if (preg_match('*' . strtolower($server) . '*', strtolower(SERVER_MOVIL))) {
            $tipo = 'sftp_movil';
            $name = preg_replace('[\.]', '_', $IP);
            $client->alias($name . '_alias', $name . ':/storage/emulated/0');
            $name .= '_alias';
        } else {
            $tipo = 'sftp_orde';
            $name = preg_replace('[\.]', '_', $IP);
        }

        $client->sftp($IP, $usuario, $pas);
        $BD = new BD($this->getDoctrine()->getManager());
        $BD->C_conexion($name, $this->getUser(), $usuario, $tipo);
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/borrar_conexion/{conexion}", name="borrar_conexion")
     */
    public function borrarConexion(httpClient $client, string $conexion, ConexionesRepository $con) {

        $criteria = ['nombre' => $conexion];
        $conexiones = $con->findBy($criteria);
        //Borro el alias
        if (preg_match('*' . '_alias' . '*', $conexion)) {
            $client->borrarConexion(str_replace('_alias', "", $conexion));
        }
        //Borro la conexion
        $client->borrarConexion($conexion);
        $BD = new BD($this->getDoctrine()->getManager());
        $BD->B_conexion($conexiones[0]);
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/editar_alias", name="editar_alias")
     */
    public function editarAlias(Request $request, ConexionesRepository $con) {

        $alias = $request->get('user');
        $nombre = $request->get('nombre');
        $criteria = ['nombre' => $nombre];
        $conexion = $con->findBy($criteria);
        $BD = new BD($this->getDoctrine()->getManager());
        $BD->E_conexion($conexion[0], $alias);
        return $this->redirectToRoute('lista_conexion');
    }

}
