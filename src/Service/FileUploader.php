<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct(?string $targetDirectory = null)
    {
        $this->setTargetDirectory($targetDirectory);
    }

    public function upload(mixed $file, string $prefix = null, &$path = null, $newFileName = false, $replacePath = false)
    {
        if (!is_a($file, UploadedFile::class)) {
            throw new Exception("Le fichier n'est pas une insatnce de UploadedFile !", 1);
        }

        if (!$replacePath) {
            if ($prefix == 'private') {
                $path = dirname($this->targetDirectory) . '/data';
            } else {
                $path = $this->targetDirectory . '/public/uploads/nas';
            }
        }

        $this->setTargetDirectory($path);

        // On privilégie l'extension du fichier original envoyé par le client :
        // guessExtension() déduit l'extension à partir du type MIME détecté sur le
        // contenu, ce qui casse les documents Office (.docx, .xlsx, .pptx sont des
        // archives ZIP en interne et sont souvent mal détectés comme .zip), rendant
        // le fichier illisible une fois téléchargé sous la mauvaise extension.
        $extension = $file->getClientOriginalExtension();

        if (!$extension) {
            $extension = $file->guessExtension();
        }

        $realFileName = str_slug(basename($file->getClientOriginalName(), ".{$extension}"), '_');



        $fileName = $newFileName === false ? md5(uniqid()) : (substr($newFileName . '_' . uniqid() . '_' . $realFileName, 0, 200));
        $fileName .= '.' . $extension;


        $file->move($this->getTargetDirectory(), $fileName);


        $path .= "/{$fileName}";

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }


    public function setTargetDirectory($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }
}
