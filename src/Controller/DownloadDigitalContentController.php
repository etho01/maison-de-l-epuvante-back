<?php

namespace App\Controller;

use App\Entity\DigitalContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class DownloadDigitalContentController extends AbstractController
{
    public function __invoke(DigitalContent $data): BinaryFileResponse
    {
        $filePath = $data->getFilePath();
        
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Le fichier n\'existe pas');
        }
        
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filePath)
        );
        
        return $response;
    }
}
