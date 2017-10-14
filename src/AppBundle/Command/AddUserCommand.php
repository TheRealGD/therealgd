<?php

namespace Raddit\AppBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommand extends Command implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('app:add-user')
            ->setDescription('Add a user account')
            ->addArgument('username', InputArgument::REQUIRED, 'The username for the new account')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address for the account')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Sets this user to be an admin')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
        $this->validator = $this->container->get('validator');
        $this->encoder = $this->container->get('security.password_encoder');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $password = $io->askHidden('Enter the password for the new account');

        $user = new User(
            $input->getArgument('username'),
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 13])
        );
        $user->setEmail($input->getArgument('email'));
        $user->setAdmin($input->getOption('admin'));

        $errors = $this->validator->validate($user);

        if ($errors->count() > 0) {
            /** @var ConstraintViolationInterface $e */
            foreach ($this->validator->validate($user) as $e) {
                $io->error($e->getPropertyPath().': '.$e->getMessage());
            }

            return 1;
        }

        $this->manager->persist($user);
        $this->manager->flush();

        $io->success('User successfully created.');

        return 0;
    }
}
