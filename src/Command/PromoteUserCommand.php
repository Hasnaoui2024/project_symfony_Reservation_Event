<?php

namespace App\Command;

use App\Entity\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class PromoteUserCommand extends Command
{
    // Définissez le nom de la commande ici
    protected static $defaultName = 'app:promote-user';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
{
    $this
        ->setName('app:promote-user') // Définir le nom de la commande
        ->setDescription('Promotes a user to a given role.')
        ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
        ->addArgument('role', InputArgument::REQUIRED, 'The role to promote the user to.');
}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');

        // Récupérer l'utilisateur depuis la base de données
        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>User not found.</error>');
            return Command::FAILURE;
        }

        // Ajouter le rôle à l'utilisateur
        $user->addRole($role);

        // Enregistrer les modifications dans la base de données
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>User %s promoted to role %s.</info>', $email, $role));

        return Command::SUCCESS;
    }
}