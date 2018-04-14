<?php

namespace App\Command;

use App\Form\Model\UserData;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ModifyUserCommand extends Command {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        EntityManagerInterface $manager,
        UserRepository $users,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->users = $users;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    public function configure() {
        $this
            ->setName('app:user:modify')
            ->setDescription('Change some attributes of a user account')
            ->addArgument('username', InputArgument::REQUIRED, 'Username of account to modify, or ID with --find-by-id')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'Change user\'s username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Change user\'s password')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Change user\'s email (set to empty string to remove)')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Give the user admin status')
            ->addOption('no-admin', null, InputOption::VALUE_NONE, 'Remove the user\'s admin status')
            ->addOption('find-by-id', null, InputOption::VALUE_NONE, 'Find user by ID instead of username')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('find-by-id')) {
            $user = $this->users->loadUserByUsername($input->getArgument('username'));
        } else {
            $user = $this->users->find($input->getArgument('username'));
        }

        if (!$user) {
            $io->error(sprintf('No such user "%s"', $input->getArgument('username')));

            return 1;
        }

        $data = UserData::fromUser($user);

        if ($input->getOption('username') !== null) {
            $data->setUsername($input->getOption('username'));
        }

        if ($input->getOption('password') !== null) {
            if ($input->getOption('password') === true) {
                if (!$input->isInteractive()) {
                    $io->error('Password must be specified as option in non-interactive mode.');

                    return 1;
                }

                $password = $io->askHidden('New password for this user');
            } else {
                $password = $input->getOption('password');
            }

            $data->setPlainPassword($password);
            $this->passwordEncoder->encodePassword($data, $password);
        }

        if ($input->getOption('email') !== null) {
            $email = $input->getOption('email') ?: null;

            $data->setEmail($email);
        }

        if ($input->getOption('admin')) {
            $data->setAdmin(true);
        }

        if ($input->getOption('no-admin')) {
            if ($input->getOption('admin')) {
                $io->error("You cannot have both 'admin' and 'no-admin' options set.");

                return 1;
            }

            $data->setAdmin(false);
        }

        $errors = $this->validator->validate($data, null, ['edit']);

        if (\count($errors) > 0) {
            $rows = [];

            /* @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $rows[] = [$error->getPropertyPath(), $error->getMessage()];
            }

            $io->table(['Field', 'Error'], $rows);
            $io->error('Fix the above errors and try again.');

            return 1;
        }

        $data->updateUser($user);

        $this->manager->flush();

        $io->success('User updated!');

        return 0;
    }
}
