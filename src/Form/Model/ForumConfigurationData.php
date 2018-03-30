<?php

namespace App\Form\Model;

use App\Entity\ForumConfiguration;
use Symfony\Component\Validator\Constraints as Assert;

class ForumConfigurationData {
    /**
     * @var int
     */
    public $id;

    /**
     * @var int|null
     */
    public $forumId;

    /**
     * @var string|null
     */
    public $announcement;

    public function __construct(ForumConfiguration $fc) {
        $this->id = $fc->getId();
        $this->forumId = $fc->getForumId();
        $this->announcement = $fc->getAnnouncement();
    }

    public function toForumConfiguration(): ForumConfiguration {
         $fc = new ForumConfiguration($this->forumId);
         $fc->setId($this->id);
         $fc->setAnnouncement($this->announcement);

         return $fc;
    }
}
