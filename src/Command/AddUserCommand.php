<?php

namespace App\Command;

use App\Form\Model\UserData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommand extends Command {
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    private $defaultLocale;

    public function __construct(
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        string $defaultLocale
    ) {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->defaultLocale = $defaultLocale;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('app:user:add')
            ->setAliases(['app:add-user'])
            ->setDescription('Add a user account')
            ->addArgument('username', InputArgument::REQUIRED, 'The username for the new account')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The email address for the account')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The password for the account')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Sets this user to be an admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $password = $input->getOption('password');

        if (!strlen($password)) {
            if ($input->isInteractive()) {
                $password = $io->askHidden('Enter the password for the new account');
            } else {
                $io->error([
                    'You must specify a password with the -p option,',
                    'or provide one interactively.',
                ]);

                return 1;
            }
        }

        $data = new UserData();
        $data->setLocale($this->defaultLocale);
        $data->setUsername($input->getArgument('username'));
        $data->setPlainPassword($password);
        $data->setEmail($input->getOption('email'));

        $errors = $this->validator->validate($data, null, ['registration']);

        if (count($errors) > 0) {
            /* @var ConstraintViolationInterface $e */
            foreach ($errors as $error) {
                $io->error(sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage()));
            }

            return 1;
        }

        $data->setPassword($this->encoder->encodePassword($data, $data->getPlainPassword()));

        $user = $data->toUser();
        $user->setAdmin($input->getOption('admin'));

        $this->manager->persist($user);
        $this->manager->flush();

        $io->success('User successfully created.');

        return 0;
    }
}
