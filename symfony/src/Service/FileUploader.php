<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader {

    private $targetDirectory;
    private $slugger;
    private $dirPoliticas;

    public function __construct($targetDirectory, SluggerInterface $slugger, $dirPoliticas) {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->dirPoliticas = $dirPoliticas;
    }

    public function upload(UploadedFile $file) {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function upload_politica(UploadedFile $file, int $id) {

        $fileName = $id . '_politicas.json';

        try {
            $file->move($this->dirPoliticas, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function extension(UploadedFile $file, string $extension) {
        $arch_extension = $file->guessExtension();
        if ($arch_extension == $extension) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function tamaño(UploadedFile $file, $args) {
        $arch_tam = filesize($file);
        $signo = preg_split('/ /', $args)[0];
        $tamaño = preg_split('/ /', $args)[1];

        $res = NULL;
        switch ($signo) {
            case '>':
                $res = $arch_tam > $tamaño ? TRUE : FALSE;
                break;
            case '<':
                $res = $arch_tam < $tamaño ? TRUE : FALSE;
                break;
            case '=':
                $res = $arch_tam == $tamaño ? TRUE : FALSE;
                break;
            default:
                break;
        }
        return $res;
    }

    public function ContieneNombre(UploadedFile $file, string $patron) {
        $arch_nombre = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        var_dump($patron);
        if (preg_match($patron, $arch_nombre)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getTargetDirectory() {
        return $this->targetDirectory;
    }

    public function ListaPoliticas(int $id_user) {
        $nombre = $id_user . '_politicas.json';
        try {
            $politicas = new UploadedFile($this->dirPoliticas . $nombre, $nombre);
            $json = json_decode($politicas->getContent());
            return (array) $json->politicas->id;
        } catch (\Exception $exc) {
            return [];
        }
    }

    public function Politica_id(int $id, int $id_user) {

        if ($id == 0) {
            return ['Tipo' => '', 'Args' => '', 'Destino' => ''];
        }
        $nombre = $id_user . 'politicas.json';
        $politicas = new UploadedFile($this->dirPoliticas . $nombre, $nombre);
        $json = json_decode($politicas->getContent());
        return ['Tipo' => $json->politicas->id->{$id}->Tipo,
            'Args' => $json->politicas->id->{$id}->Args,
            'Destino' => $json->politicas->id->{$id}->Destino];
    }

}
