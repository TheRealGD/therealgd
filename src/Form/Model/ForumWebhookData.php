<?php

namespace App\Form\Model;

use App\Entity\Forum;
use App\Entity\ForumWebhook;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class ForumWebhookData {
    /**
     * @Assert\Choice(choices=ForumWebhook::EVENTS)
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    public $event;

    /**
     * @Assert\Url()
     *
     * @var string|null
     */
    public $url;

    /**
     * @Assert\Length(max=420)
     *
     * @var string|null
     */
    public $secretToken;

    /**
     * @var Uuid|null
     */
    private $entityId;

    public function __construct(ForumWebhook $forumWebhook = null) {
        if ($forumWebhook) {
            $this->entityId = $forumWebhook->getId();
            $this->event = $forumWebhook->getEvent();
            $this->url = $forumWebhook->getUrl();
            $this->secretToken = $forumWebhook->getSecretToken();
        }
    }

    public function toWebhook(Forum $forum): ForumWebhook {
        return new ForumWebhook(
            $forum,
            $this->event,
            $this->url,
            $this->secretToken
        );
    }

    public function updateWebhook(ForumWebhook $forumWebhook): void {
        $forumWebhook->setEvent($this->event);
        $forumWebhook->setUrl($this->url);
        $forumWebhook->setSecretToken($this->secretToken);
    }

    public function getEntityId(): ?Uuid {
        return $this->entityId;
    }
}
