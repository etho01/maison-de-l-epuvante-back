<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class VerifyEmailController extends AbstractController
{
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
            return $this->json([
                'message' => 'Paramètres manquants',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (null === $user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->isVerified()) {
            return $this->json([
                'message' => 'Email déjà vérifié',
            ]);
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->json([
                'message' => 'Le lien de vérification est invalide ou a expiré',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setVerified(true);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Email vérifié avec succès',
        ]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json([
                'message' => 'Email requis',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (null === $user) {
            // Ne pas révéler si l'email existe
            return $this->json([
                'message' => 'Si cet email existe et n\'est pas vérifié, un nouvel email a été envoyé',
            ]);
        }

        if ($user->isVerified()) {
            return $this->json([
                'message' => 'Email déjà vérifié',
            ]);
        }

        // Dans une vraie application, générez et envoyez un email avec le lien de vérification
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'api_verify_email',
            $user->getId(),
            $user->getEmail()
        );

        // Ici, vous enverriez normalement un email avec le lien : $signatureComponents->getSignedUrl()
        
        return $this->json([
            'message' => 'Email de vérification envoyé',
            // En développement, vous pouvez retourner l'URL :
            // 'verificationUrl' => $signatureComponents->getSignedUrl(),
        ]);
    }
}
