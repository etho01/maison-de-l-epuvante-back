<?php

namespace App\Controller\Auth;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordController extends AbstractController
{
    use ApiResponseTrait;

    public function changePassword(
        Request $request,
        #[CurrentUser] ?User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (null === $user) {
            return $this->errorResponse(401, ApiError::USER_NOT_AUTHENTICATED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
            return $this->errorResponse(400, ApiError::MISSING_PARAMETERS);
        }

        // Vérifier le mot de passe actuel
        if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->errorResponse(400, ApiError::INVALID_PASSWORD);
        }

        // Valider le nouveau mot de passe
        if (strlen($data['newPassword']) < 8) {
            return $this->errorResponse(400, ApiError::PASSWORD_TOO_SHORT);
        }

        // Mettre à jour le mot de passe
        $user->setPlainPassword($data['newPassword']);
        
        $errors = $validator->validate($user, groups: ['user:password']);
        if (count($errors) > 0) {
            $errorMessages = [ApiError::VALIDATION_FAILED];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->errorResponse(400, $errorMessages);
        }

        $entityManager->flush();

        return $this->json(['code' => 200, 'data' => null]);
    }

    public function requestResetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        ResetPasswordRequestRepository $resetPasswordRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->errorResponse(400, ApiError::MISSING_PARAMETERS);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if ($user) {
            // Supprimer les anciens tokens de cet utilisateur
            $resetPasswordRepository->removeAllForUser($user->getId());
            
            // Générer un nouveau token de réinitialisation
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = new \DateTimeImmutable('+1 hour');
            
            // Créer et stocker la demande de réinitialisation
            $resetRequest = new ResetPasswordRequest();
            $resetRequest->setUser($user);
            $resetRequest->setToken($resetToken);
            $resetRequest->setExpiresAt($expiresAt);
            
            $entityManager->persist($resetRequest);
            $entityManager->flush();
            
            // Construire l'URL de réinitialisation
            $resetUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
            $resetUrl .= '/reset-password?token=' . $resetToken;
            
            // TODO: Envoyer l'email avec le lien
            // Pour le développement, on retourne l'URL (à supprimer en production)
            // Utiliser Symfony Mailer en production :
            // $email = (new Email())
            //     ->from('noreply@votresite.com')
            //     ->to($user->getEmail())
            //     ->subject('Réinitialisation de votre mot de passe')
            //     ->html('<a href="' . $resetUrl . '">Cliquez ici pour réinitialiser</a>');
            // $mailer->send($email);
        }

        // Toujours retourner un succès pour ne pas révéler si l'email existe
        return $this->json(['code' => 200, 'data' => null]);
    }

    public function confirmResetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ResetPasswordRequestRepository $resetPasswordRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token']) || !isset($data['newPassword'])) {
            return $this->errorResponse(400, ApiError::MISSING_PARAMETERS);
        }

        // Vérifier le token en base de données
        $resetRequest = $resetPasswordRepository->findOneBy(['token' => $data['token']]);
        
        if (!$resetRequest) {
            return $this->errorResponse(400, ApiError::INVALID_TOKEN);
        }

        if ($resetRequest->isExpired()) {
            // Supprimer le token expiré
            $entityManager->remove($resetRequest);
            $entityManager->flush();
            
            return $this->errorResponse(400, ApiError::TOKEN_EXPIRED);
        }

        // Récupérer l'utilisateur depuis le resetRequest
        $user = $resetRequest->getUser();

        if (!$user) {
            return $this->errorResponse(404, ApiError::USER_NOT_FOUND);
        }

        // Valider le nouveau mot de passe
        if (strlen($data['newPassword']) < 8) {
            return $this->errorResponse(400, ApiError::PASSWORD_TOO_SHORT);
        }

        // Hasher et mettre à jour le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);
        $user->setUpdatedAt(new \DateTimeImmutable());

        // Supprimer le token utilisé
        $entityManager->remove($resetRequest);
        
        $entityManager->flush();

        return $this->json(['code' => 200, 'data' => null]);
    }
}
