<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\FileUploader;
use App\Service\httpClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repository\ConexionesRepository;
use App\Service\politicas;
use App\Service\BD;

class UploadController extends AbstractController {

    /**
     * @Route("/inicio/upload", name="upload")
     Devolvemmos las politicas del usuario activo
     */
    public function index(FileUploader $uploader): Response {
        $userlog = $this->getUser()->getId();
        $politicas = $uploader->ListaPoliticas($userlog);
        return $this->render('upload/index.html.twig', [
                    'controller_name' => 'Subida de ficheros y politicas',
                    'politicas' => $politicas
        ]);
    }

    /**
     * @Route("/inicio/subir", name="subir")
     Subida de archivos
     */
    public function subir(Request $request, FileUploader $uploader, httpClient $client, ConexionesRepository $con, politicas $politica) {
	//Archivo subido al formulario
        $archivo = $request->files->get('formFile');
        //Politica a aplicar
        $id = $request->get('politica');

        $userlog = $this->getUser()->getId();
        //Lista de conexiones del usuario actual
        $criteria = ['user' => $userlog];
        $conexiones = $con->findBy($criteria);
        //Segun la politica seleccionada se devuelve a donde se envia
        $alias = $politica->EleccionPolitica($id, $archivo, $conexiones, $userlog);
        $criteria2 = ['alias' => $alias,'user'=>$this->getUser()];
        $destino=null;
        if(empty($con->findBy($criteria2))){
        return $this->render('noEncontrado.html.twig', [
                    'titulo' => 'Alias no encontrado'
        ]);
        }
        $destino = $con->findBy($criteria2)[0]->getNombre();
	
	//Se sube el archivo
        $nombreFichero = $uploader->upload($archivo);
            
        $origen = $nombreFichero;

        $response = $client->copiar_subir($origen, $destino, $nombreFichero);
	//Si la respuesta es correcta se borra de la aplicacion, que se ha usado como intermediaria
        if ($response->getStatusCode() == 200) {
            $filesystem = new Filesystem();
            $filesystem->remove($uploader->getTargetDirectory() . $nombreFichero);
            $BD = new BD($this->getDoctrine()->getManager());
            $BD->C_historial($nombreFichero, $alias, 'subida', new \DateTime(), $this->getUser());
        }


        return $this->redirectToRoute('lista_archivos');
    }

    /**
     * @Route("/inicio/subir_politica", name="subir_politica")
     */
    public function subir_politica(Request $request, FileUploader $uploader) {

        $archivo = $request->files->get('formFile');

        $userlog = $this->getUser()->getId();

        $uploader->upload_politica($archivo, $userlog);

        return $this->redirectToRoute('upload');
    }

    /**
     * @Route("/inicio/bajar/{conexion}/{ruta}", name="bajar", requirements={"ruta"=".+"})
     */
    public function bajar(FileUploader $uploader, httpClient $client, $ruta, $conexion, ConexionesRepository $con) {

        $archivo = preg_split("[/]", $ruta);
        $nombreArchivo = array_pop($archivo);
        $file = $nombreArchivo;
        //Nos traemos el archivo de rclone a nuestra aplicacion
        $respuesta = $client->copiar_bajar($conexion, $file, $ruta);
        $criteria = ['nombre' => $conexion,'user'=>$this->getUser()];
        $alias = $con->findBy($criteria)[0]->getAlias();
        $response = NULL;
	//Si la respuesta es satisfactoria
        if ($respuesta->getStatusCode() == 200) {
	//Configuramos donde se encuentra el archivo
            $destino = $uploader->getTargetDirectory() . $nombreArchivo;

            $response = new BinaryFileResponse($destino);

            $disposition = HeaderUtils::makeDisposition(
                            HeaderUtils::DISPOSITION_ATTACHMENT, $nombreArchivo
            );

            $response->headers->set('Content-Disposition', $disposition);
            //Se borra despues de la descarga
            $response->deleteFileAfterSend(true);
            
            $BD = new BD($this->getDoctrine()->getManager());
            //Lo incluimos en el historial
            $BD->C_historial($nombreArchivo, $alias, 'bajada', new \DateTime(), $this->getUser());
            
        }
        return $response;
    }

}
