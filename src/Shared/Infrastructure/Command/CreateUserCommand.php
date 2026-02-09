<?php

namespace App\Shared\Infrastructure\Command;

use App\Context\User\Domain\Entity\User;
use App\Context\User\Infrastructure\Repository\UserRepository;
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
    description: 'Creates a new user.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user will be created as an admin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $isAdmin = $input->getOption('admin');

        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        if ($existingUser !== null) {
            $io->note(sprintf('Usuário já existe: %s', $email));
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->userRepository->add($user, true);

        $roleType = $isAdmin ? 'ADMIN' : 'USER';
        $io->success(sprintf('%s account created successfully: %s', $roleType, $email));

        return Command::SUCCESS;
    }
}
