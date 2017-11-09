<?php

namespace AppBundle\Form\Model;

use AppBundle\Entity\MessageReply;
use AppBundle\Entity\MessageThread;
use AppBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class MessageData {
    /**
     * @Assert\Length(max=300, groups={"thread"})
     * @Assert\NotBlank(groups={"thread"})
     *
     * @var string|null
     */
    private $title;

    /**
     * @Assert\NotBlank(groups={"thread", "reply"})
     * @Assert\Length(max="10000", groups={"thread", "reply"})
     *
     * @var string|null
     */
    private $body;

    /**
     * @var User
     */
    private $sender;

    /**
     * @var string|null
     */
    private $ip;

    public function __construct(User $sender, $ip) {
        $this->sender = $sender;
        $this->ip = $ip;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function toThread(User $receiver) {
        return new MessageThread(
            $this->sender,
            $this->body,
            $this->ip,
            $receiver,
            $this->title
        );
    }

    public function toReply(MessageThread $thread) {
        return new MessageReply(
            $this->sender,
            $this->body,
            $this->ip,
            $thread
        );
    }
}
