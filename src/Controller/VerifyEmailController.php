<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class VerifyEmailController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function verifyUserEmail(Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        $token = $request->query->get('token');

        if (null === $id || null === $token) {
            return $this->errorResponse(400, ApiError::MISSING_PARAMETERS);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (null === $user) {
            return $this->errorResponse(404, ApiError::USER_NOT_FOUND);
        }

        if ($user->isVerified()) {
            return $this->errorResponse(400, ApiError::EMAIL_ALREADY_VERIFIED);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->errorResponse(400, ApiError::INVALID_TOKEN);
        }

        $user->setVerified(true);
        $this->entityManager->flush();

        return $this->json(['code' => 200, 'data' => null]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->errorResponse(400, ApiError::MISSING_PARAMETERS);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (null === $user) {
            // Ne pas révéler si l'email existe
            return $this->json(['code' => 200, 'data' => null]);
        }

        if ($user->isVerified()) {
            return $this->errorResponse(400, ApiError::EMAIL_ALREADY_VERIFIED);
        }

        // Dans une vraie application, générez et envoyez un email avec le lien de vérification
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'api_verify_email',
            $user->getId(),
            $user->getEmail()
        );

        // Ici, vous enverriez normalement un email avec le lien : $signatureComponents->getSignedUrl()
        
        return $this->json(['code' => 200, 'data' => null]);
    }
}
