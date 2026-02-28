<?php

namespace App\Controller;

use App\Entity\DigitalContent;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DownloadDigitalContentController extends AbstractController
{
    use ApiResponseTrait;

    public function __invoke(DigitalContent $data): BinaryFileResponse|JsonResponse
    {
        $filePath = $data->getFilePath();
        
        if (!file_exists($filePath)) {
            return $this->errorResponse(404, ApiError::FILE_NOT_FOUND);
        }
        
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filePath)
        );
        
        return $response;
    }
}
