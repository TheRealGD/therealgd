<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Embed\Embed;
use Embed\Exceptions\EmbedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Raddit\AppBundle\Entity\Submission;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Download related image after submission.
 */
final class SubmissionImageListener implements LoggerAwareInterface {
    use LoggerAwareTrait;

    const QUEUE_KEY = 'submission_thumbnail_queue';

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string
     */
    private $imageDirectory;

    public function __construct(
        EntityManagerInterface $manager,
        RequestStack $requestStack,
        ValidatorInterface $validator
    ) {
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->logger = new NullLogger();
        $this->imageDirectory = __DIR__.'/../../../web/submission_images';
    }

    /**
     * Stick every submission with a URL in a queue.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {
        $request = $this->requestStack->getMasterRequest();
        $entity = $args->getEntity();

        if (!$request || !$entity instanceof Submission || !$entity->getUrl() || $entity->getImage()) {
            return;
        }

        $queue = $request->attributes->get(self::QUEUE_KEY, []);
        $queue[] = $entity;

        $request->attributes->set(self::QUEUE_KEY, $queue);
    }

    /**
     * Loop through the queue at the end of the request and download the images.
     *
     * @param KernelEvent $event
     */
    public function onKernelTerminate(KernelEvent $event) {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $queue = $event->getRequest()->attributes->get(self::QUEUE_KEY, []);

        /** @var Submission $submission */
        foreach ($queue as $submission) {
            try {
                $embed = Embed::create($submission->getUrl());
                $imageUrl = $embed->getImage();

                if ($imageUrl) {
                    $submission->setImage($this->getFilename($imageUrl));
                }
            } catch (EmbedException $e) {
                $this->logger->info($e->getMessage());
            }
        }

        $this->manager->flush();
    }

    /**
     * @return string
     */
    public function getImageDirectory(): string {
        return $this->imageDirectory;
    }

    /**
     * @param string $imageDirectory
     */
    public function setImageDirectory(string $imageDirectory) {
        $this->imageDirectory = rtrim($imageDirectory, '/');
    }

    /**
     * Download, store, and rename the image.
     *
     * @param string $imageUrl
     *
     * @return string|null the final file name, or null if the download failed
     *
     * @todo refactor this, perhaps use a library
     */
    private function getFilename(string $imageUrl) {
        error_clear_last();

        $tempFile = @tempnam(sys_get_temp_dir(), 'raddit');
        $fh = @fopen($tempFile, 'w');

        if (!$fh) {
            $this->logger->warning('Could not open file handle', ['error' => error_get_last()]);
            @unlink($tempFile);

            return null;
        }

        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200;

        if (!$success) {
            $this->logger->info('Bad HTTP response', ['curl' => curl_getinfo($ch)]);
            @unlink($tempFile);

            return null;
        }

        $imageConstraint = new Image();
        $imageConstraint->detectCorrupted = true;

        $violations = $this->validator->validate($tempFile, $imageConstraint);

        if (count($violations) > 0) {
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $this->logger->info($violation->getMessageTemplate(), $violation->getParameters());
            }

            @unlink($tempFile);

            return null;
        }

        $mimeType = MimeTypeGuesser::getInstance()->guess($tempFile);
        $ext = ExtensionGuesser::getInstance()->guess($mimeType);

        $filename = hash_file('sha256', $tempFile).'.'.$ext;

        if (!@rename($tempFile, $this->imageDirectory.'/'.$filename)) {
            $this->logger->warning('Could not rename file', ['error' => error_get_last()]);

            @unlink($tempFile);

            return null;
        }

        return $filename;
    }
}
