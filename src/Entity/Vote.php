<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all vote entities.
 *
 * @ORM\MappedSuperclass()
 */
abstract class Vote {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $upvote;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="inet", nullable=true)
     *
     * @var string|null
     */
    private $ip;

    /**
     * @param User        $user
     * @param string|null $ip
     * @param bool|int    $choice
     *
     * @throws \InvalidArgumentException if $choice is bad
     */
    public function __construct(User $user, ?string $ip, int $choice) {
        $this->timestamp = new \DateTime('@'.time());
        $this->user = $user;
        $this->setIp($ip); // must be after $this->user is declared
        $this->setChoice($choice);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getChoice(): int {
        return $this->upvote ? Votable::USER_UPVOTED : Votable::USER_DOWNVOTED;
    }

    /**
     * @param int $choice one of Votable::VOTE_UP or Votable::VOTE_DOWN
     *
     * @throws \InvalidArgumentException if $choice isn't a valid parameter
     */
    public function setChoice(int $choice): void {
        if ($choice === Votable::VOTE_UP || $choice === Votable::VOTE_DOWN) {
            $this->upvote = $choice === Votable::VOTE_UP;
        } elseif ($choice === Votable::VOTE_RETRACT) {
            throw new \InvalidArgumentException('A vote entity cannot have a "retracted" status');
        } else {
            throw new \InvalidArgumentException('Unknown choice');
        }
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getIp(): ?string {
        return $this->ip;
    }

    public function setIp(?string $ip): void {
        if ($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Bad IP address');
        }

        $this->ip = $this->user->isTrustedOrAdmin() ? null : $ip;
    }

    /**
     * Legacy getter needed for `Selectable` compatibility.
     *
     * @return bool
     *
     * @internal
     */
    public function getUpvote(): bool {
        return $this->upvote;
    }
}
