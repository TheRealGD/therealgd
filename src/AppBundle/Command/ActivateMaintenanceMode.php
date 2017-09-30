<?php

namespace Raddit\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ActivateMaintenanceMode extends ContainerAwareCommand {
    public function configure() {
        $this
            ->setName('app:maintenance')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Custom message to display.')
            ->addOption('deactivate', 'd', InputOption::VALUE_NONE, 'Deactivate maintenance mode')
            ->setDescription('Manages maintenance mode')
            ->setHelp(<<<EOHELP
Takes the application down for maintenance.

To set a custom message, use the <info>-m</info> option.

To deactivate maintenance mode, use the <info>-d</info> option.
EOHELP
);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('deactivate')) {
            $this->writeIncludeFile($input->getOption('message'));

            $io->success('Maintenance mode has been activated.');
        } else {
            if (!@unlink($this->getIncludeFilePath())) {
                $io->error('Could not remove '.$this->getIncludeFilePath());

                return 1;
            }

            $io->success('Maintenance mode is now deactivated.');
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->getIncludeFilePath(), true);
        }
    }

    private function writeIncludeFile($message) {
        $title = $this->getContainer()->getParameter('env(SITE_NAME)');
        $img = base64_encode(file_get_contents(__DIR__.'/../../../web/apple-touch-icon-precomposed.png'));
        $message = nl2br($message ?: 'The site will return shortly.');

        $php = <<<EOPHP
<?php http_response_code(503) ?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>{$title}</title>
<style>
body {
  background: #f8f8f8;
  color: #444;
  font-family: sans-serif;
  margin: 2em;
  text-align: center;
}
</style>
<p><img src="data:image/png;base64,{$img}"></p>
<h1>{$title} is down for maintenance</h1>
<p>$message</p>
<?php exit ?>
EOPHP;

        $tempnam = tempnam(sys_get_temp_dir(), 'ra');

        if (!@file_put_contents($tempnam, $php)) {
            throw new \RuntimeException("Couldn't write temporary file");
        }

        @chmod($tempnam, 0666 & ~umask());

        if (!@rename($tempnam, $this->getIncludeFilePath())) {
            throw new \RuntimeException("Couldn't write to file");
        }
    }

    private function getIncludeFilePath(): string {
        return __DIR__.'/../../../var/maintenance.php';
    }
}
