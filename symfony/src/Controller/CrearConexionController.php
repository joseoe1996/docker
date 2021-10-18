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
     Se encarga de mostrar las conexiones disponibles
     */
    public function index(ConexionesRepository $con, httpClient $client, Varios $varios): Response {

        $userlog = $this->getUser()->getId();

        //Lista de conexiones del susario actual
        $criteria = ['user' => $userlog];
        $conexiones = $con->findBy($criteria);
        //Listar conexiones SSDP disponibles
        $disponibles2 = $client->sspd();
                
        //Comprobar con las que ya esten en la BD
	
        $final=$varios->filtrado($disponibles2,$criteria,$con);
                
        
        return $this->render('crear_conexion/index.html.twig', [
                    'controller_name' => 'Conexiones Disponibles',
                    'conexiones' => $conexiones,
                    'sftp' => $final
        ]);
    }

    /**
     * @Route("/inicio/lista_conexion/crear_onedrive", name="crear_onedrive")
     Crear conexion OneDrive
     */
    public function onedrive(httpClient $client): Response {
        //Obtencion del token y creacion en RCLONE
        $ayuda = $client->onedrive();
        $BD = new BD($this->getDoctrine()->getManager());
        //Insertat en la BD
        $BD->C_conexion($ayuda['nombre'], $this->getUser(), $ayuda['alias'], 'onedrive');
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/crear_drive", name="crear_drive")
     Crear conexion Drive
     */
    public function drive(httpClient $client): Response {
	//Obtener token y crear en Rclone
        $ayuda = $client->drive();
        $BD = new BD($this->getDoctrine()->getManager());
        //Guardar en la BD
        $BD->C_conexion($ayuda['nombre'], $this->getUser(), $ayuda['alias'], 'drive');
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/crear_sftp", name="crear_sftp")
     Crear conexion SFTP
     */
    public function sftp(httpClient $client, Request $request): Response {
	//Obtener los valores del formulario
        $usuario = $request->get('user');
        $pas = $request->get('password');
        $IP = $request->get('IP');
        $server = $request->get('SERVER');
	//Elegir entre movil y ordenador
        if (preg_match('*' . strtolower(SERVER_MOVIL) . '*', strtolower($server))) {
            $tipo = 'sftp_movil';
            $name = preg_replace('[\.]', '_', $IP);
            //Establecer un alial para el almacenamiento principal en android
            $client->alias($name . '_alias', $name . ':/storage/emulated/0');
            $name .= '_alias';
        } else {
            $tipo = 'sftp_orde';
            $name = preg_replace('[\.]', '_', $IP);
        }
	//Guardar en Rclone
        $client->sftp($IP, $usuario, $pas);
        $BD = new BD($this->getDoctrine()->getManager());
        //Guardar en la BD
        $BD->C_conexion($name, $this->getUser(), $usuario, $tipo);
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/borrar_conexion/{conexion}", name="borrar_conexion")
     */
    public function borrarConexion(httpClient $client, string $conexion, ConexionesRepository $con) {

        $criteria = ['nombre' => $conexion,'user'=>$this->getUser()];
        $conexiones = $con->findBy($criteria);
        //Borro el alias si decido borrar la conexion movil
        if (preg_match('*' . '_alias' . '*', $conexion)) {
            $client->borrarConexion(str_replace('_alias', "", $conexion));
        }
        //Borro la conexion
        $client->borrarConexion($conexion);
        $BD = new BD($this->getDoctrine()->getManager());
        //Borrar en la BD
        $BD->B_conexion($conexiones[0]);
        return $this->redirectToRoute('lista_conexion');
    }

    /**
     * @Route("/inicio/lista_conexion/editar_alias", name="editar_alias")
     */
    public function editarAlias(Request $request, ConexionesRepository $con) {
	//Obtener los campos del formulario
        $alias = $request->get('user');
        $nombre = $request->get('nombre');
        $criteria = ['nombre' => $nombre,'user'=>$this->getUser()];
        //Buscamos la conexion a editar
        $conexion = $con->findBy($criteria);
        $BD = new BD($this->getDoctrine()->getManager());
        //Editamos
        $BD->E_conexion($conexion[0], $alias);
        return $this->redirectToRoute('lista_conexion');
    }

}
