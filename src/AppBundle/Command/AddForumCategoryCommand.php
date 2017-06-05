<?php

namespace Raddit\AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Raddit\AppBundle\Entity\ForumCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddForumCategoryCommand extends ContainerAwareCommand {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    protected function configure() {
        $this
            ->setName('raddit:forum:add-category')
            ->setDescription('Adds a new forum category')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the category to add')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $category = new ForumCategory();
        $category->setName($input->getArgument('name'));

        $this->manager->persist($category);
        $this->manager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('The category '.$input->getArgument('name').' was successfully added.');
    }
}
