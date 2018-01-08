<?php

namespace App\Mailer;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class ResetPasswordMailer {
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string|null
     */
    private $noReplyAddress;

    /**
     * @var string
     */
    private $siteName;

    /**
     * @var string
     */
    private $salt;

    public function __construct(
        \Swift_Mailer $mailer,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        string $siteName,
        $noReplyAddress,
        string $salt
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->siteName = $siteName;
        $this->noReplyAddress = $noReplyAddress;
        $this->salt = $salt;
    }

    public function canMail(): bool {
        return !empty($this->noReplyAddress);
    }

    public function mail(User $user, Request $request) {
        if (!$this->canMail()) {
            throw new \RuntimeException('Cannot send mail without a no-reply address');
        }

        $expires = new \DateTime('@'.time().' +24 hours');

        $subject = $this->translator->trans('reset_password.email_subject', [
            '%site_name%' => $this->siteName,
            '%username%' => $user->getUsername(),
        ]);

        $body = $this->translator->trans('reset_password.email_body', [
            '%reset_link%' => $this->urlGenerator->generate(
                'password_reset', [
                    'expires' => $expires->format('U'),
                    'id' => $user->getId(),
                    'checksum' => $this->createChecksum($user, $expires),
                ], UrlGeneratorInterface::ABSOLUTE_URL
            ),
            '%site_name%' => $this->siteName,
        ]);

        $message = (new \Swift_Message())
            ->setFrom([$this->noReplyAddress => $this->siteName])
            ->setTo([$user->getEmail() => $user->getUsername()])
            ->setSubject($subject)
            ->setBody($body);

        $message->getHeaders()->addTextHeader(
            'X-Originating-IP',
            '['.implode(', ', $request->getClientIps()).']'
        );

        $this->mailer->send($message);
    }

    /**
     * Ensures that a checksum in a reset URL was actually sent by us and that
     * the password hasn't changed (which invalidates the reset URL).
     *
     * @param string    $checksum
     * @param User      $user
     * @param \DateTime $expiresAt
     *
     * @return bool
     */
    public function validateChecksum(string $checksum, User $user, \DateTime $expiresAt): bool {
        return hash_equals($checksum, $this->createChecksum($user, $expiresAt));
    }

    private function createChecksum(User $user, \DateTime $expiresAt): string {
        $data = sprintf('%d~%s~%s',
            $user->getId(),
            $user->getPassword(),
            $expiresAt->format('U')
        );

        return hash_hmac('sha256', $data, $this->salt);
    }

    public function getNoReplyAddress(): ?string {
        return $this->noReplyAddress;
    }

    public function getSalt(): string {
        return $this->salt;
    }

    public function getSiteName(): string {
        return $this->siteName;
    }
}
