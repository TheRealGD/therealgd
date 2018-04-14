<?php

namespace App\Command;

use App\Form\Model\SubmissionData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportFromReddit extends Command {
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    private $defaultLocale;

    public function __construct(
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
        string $defaultLocale
    ) {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->defaultLocale = $defaultLocale;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('app:import:reddit')
            ->setAliases(['app:import-reddit'])
            ->setDescription('Import posts from reddit.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        //$password = $input->getOption('password');

        // Prime Response
        $successReport = [
          "success" => 'false',
          "imported" => array(),
          "ignored" => array()
        ];

        // Check top 10 every time.
        $redditUrl = "https://www.reddit.com/r/gundeals/new.json?sort=new&limit=10";

        try {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $redditUrl);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

          $response = curl_exec($ch);

          // The goods.
          $data = json_decode($response);
          foreach ($data->data->children as $posting)
          {
            $url = $posting->data->url;
            $title = $posting->data->title;
            $permalink = $posting->data->permalink;
            $hidden = $posting->data->hidden;
            $pinned = $posting->data->pinned;
            $modbanned = $posting->data->banned_by;

            // Only import non banned and non hidden and non pinned posts
            if ($hidden == false && $modbanned == null && $pinned == false){
              // We got that url? YAAA BOY!
              if ($url != null && $title != null)
              {

                $sr = $this->manager->getRepository('App:Submission');
                $ur = $this->manager->getRepository('App:User');
                $fr = $this->manager->getRepository('App:Forum');

                /* Check for submission with url of incoming url */
                $foundEntry = $sr->findOneByUrl($url);
                if ($foundEntry == null){
                  /* If not, create submission */
                  $forum = $fr->findOneByCaseInsensitiveName("gundeals");

                  $data = new SubmissionData($forum);
                    $data->setTitle($title);
                    $data->setUrl($url);
                    $data->setBody("Imported From /r/gundeals. | " . $permalink);
                    $data->setSticky(false);
                    $data->setModThread(false);

                  $user = $ur->loadUserByUsername("gundeals");
                  $submission = $data->toSubmission($user, "127.0.0.1");

                  $this->manager->persist($submission);
                  $this->manager->flush();

                  array_push($successReport['imported'], $url);
                  $successReport['success'] = 'true';
                } else {
                  array_push($successReport['ignored'], $url);
                  $successReport['success'] = 'true';
                }
              } else {

              }
            }
          }
        } catch (Exception $e){
          // fuk.
        }

        $jsonResponse = json_encode($successReport);
        $io->success($jsonResponse);
        return 0;
    }
}
