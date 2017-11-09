<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Comment;
use AppBundle\Entity\CommentVote;
use AppBundle\Entity\MessageReply;
use AppBundle\Entity\MessageThread;
use AppBundle\Entity\Submission;
use AppBundle\Entity\SubmissionVote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Command for removing IP addresses associated with some entities.
 *
 * This is intended to be run in a cron job or similar to ensure visitor
 * privacy.
 */
class PruneIpAddressesCommand extends Command implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('app:prune-ips')
            ->setDescription('Prunes IP addresses associated with some entities')
            ->addOption('max-age', 'm', InputOption::VALUE_REQUIRED,
                'The maximum age (strtotime format) of an entity in seconds before its IP address is cleared.'
            )
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE,
                'Don\'t apply the changes to the database.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        if ($input->isInteractive()) {
            if (!$io->confirm('Are you sure you wish to prune IP addresses?', false)) {
                $io->text('Aborting...');

                return 1;
            }
        }

        if ($input->getOption('max-age')) {
            $nowTime = new \DateTime('@'.time());
            $maxTime = clone $nowTime;

            if (!@$maxTime->modify($input->getOption('max-age'))) {
                $io->error('Invalid date format');

                return 1;
            }

            if ($maxTime > $nowTime) {
                $io->error('max-age option cannot be a future time');

                if ($io->isDebug()) {
                    $io->comment('now: '.$nowTime->format('c'));
                    $io->comment('max: '.$maxTime->format('c'));
                }

                return 1;
            }
        } else {
            $maxTime = null;
        }

        $this->manager->beginTransaction();

        $count = 0;
        $count += $this->clearIpsForEntity(Comment::class, $maxTime);
        $count += $this->clearIpsForEntity(CommentVote::class, $maxTime);
        $count += $this->clearIpsForEntity(Submission::class, $maxTime);
        $count += $this->clearIpsForEntity(SubmissionVote::class, $maxTime);
        $count += $this->clearIpsForEntity(MessageThread::class, $maxTime);
        $count += $this->clearIpsForEntity(MessageReply::class, $maxTime);

        if ($input->getOption('dry-run')) {
            $this->manager->rollback();
        } else {
            $this->manager->commit();
            $this->manager->flush();
        }

        if ($count > 0) {
            $io->success(sprintf('Pruned IPs for %s entit%s.',
                number_format($count),
                $count !== 1 ? 'ies' : 'y'
            ));
        } else {
            $io->note('No entities with IP addresses.');
        }

        return 0;
    }

    /**
     * @param string         $entity
     * @param \DateTime|null $maxTime
     * @param string         $ipField
     * @param string         $timestampField
     *
     * @return int number of affected rows
     */
    protected function clearIpsForEntity(
        string $entity,
        $maxTime,
        string $ipField = 'ip',
        string $timestampField = 'timestamp'
    ) {
        $qb = $this->manager->createQueryBuilder()
            ->update($entity, 'e')
            ->set('e.'.$ipField, '?1')
            ->setParameter(1, null)
            ->where('e.'.$ipField.' IS NOT NULL');

        if ($maxTime) {
            $qb->andWhere('e.'.$timestampField.' <= ?2');
            $qb->setParameter(2, $maxTime);
        }

        return $qb->getQuery()->execute();
    }
}
