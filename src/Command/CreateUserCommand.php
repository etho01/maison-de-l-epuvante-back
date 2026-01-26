<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Créer un administrateur')
            ->addOption('verified', 'v', InputOption::VALUE_NONE, 'Marquer l\'email comme vérifié')
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED, 'Prénom')
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED, 'Nom')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        
        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPlainPassword($password);
        
        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        }
        
        if ($input->getOption('verified')) {
            $user->setVerified(true);
        }
        
        if ($firstName = $input->getOption('first-name')) {
            $user->setFirstName($firstName);
        }
        
        if ($lastName = $input->getOption('last-name')) {
            $user->setLastName($lastName);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success([
            'Utilisateur créé avec succès !',
            sprintf('Email: %s', $email),
            sprintf('Rôles: %s', implode(', ', $user->getRoles())),
            sprintf('Email vérifié: %s', $user->isVerified() ? 'Oui' : 'Non'),
        ]);

        return Command::SUCCESS;
    }
}
