<?php

namespace App\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
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
    public function changePassword(
        Request $request,
        #[CurrentUser] ?User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (null === $user) {
            return $this->json([
                'message' => 'Non autorisé',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
            return $this->json([
                'message' => 'Mot de passe actuel et nouveau mot de passe requis',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier le mot de passe actuel
        if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
            return $this->json([
                'message' => 'Mot de passe actuel incorrect',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Valider le nouveau mot de passe
        if (strlen($data['newPassword']) < 8) {
            return $this->json([
                'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Mettre à jour le mot de passe
        $user->setPlainPassword($data['newPassword']);
        
        $errors = $validator->validate($user, groups: ['user:password']);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $errorMessages,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Mot de passe modifié avec succès',
        ]);
    }

    public function requestResetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        ResetPasswordRequestRepository $resetPasswordRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json([
                'message' => 'Email requis',
            ], JsonResponse::HTTP_BAD_REQUEST);
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
        return $this->json([
            'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé',
            // En développement uniquement :
            // 'resetUrl' => $resetUrl ?? null,
        ]);
    }

    public function confirmResetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ResetPasswordRequestRepository $resetPasswordRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token']) || !isset($data['newPassword'])) {
            return $this->json([
                'message' => 'Token et nouveau mot de passe requis',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier le token en base de données
        $resetRequest = $resetPasswordRepository->findOneBy(['token' => $data['token']]);
        
        if (!$resetRequest) {
            return $this->json([
                'message' => 'Token invalide',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($resetRequest->isExpired()) {
            // Supprimer le token expiré
            $entityManager->remove($resetRequest);
            $entityManager->flush();
            
            return $this->json([
                'message' => 'Le lien de réinitialisation a expiré. Veuillez en demander un nouveau.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Récupérer l'utilisateur depuis le resetRequest
        $user = $resetRequest->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // Valider le nouveau mot de passe
        if (strlen($data['newPassword']) < 8) {
            return $this->json([
                'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Hasher et mettre à jour le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);
        $user->setUpdatedAt(new \DateTimeImmutable());

        // Supprimer le token utilisé
        $entityManager->remove($resetRequest);
        
        $entityManager->flush();

        return $this->json([
            'message' => 'Mot de passe réinitialisé avec succès',
        ]);
    }
}
