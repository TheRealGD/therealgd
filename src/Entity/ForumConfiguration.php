<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForumConfigurationRepository")
 * @ORM\Table(name="forum_configuration")
 */
class ForumConfiguration {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     *
     * @var int|null
     */
    private $forumId;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $announcement;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     *
     * @var int|null
     */
    private $announcementSubmissionId;

    public function __construct($forumId) {
        $this->forumId = $forumId;
    }

    public function getId() {
      return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getForumId() {
      return $this->forumId;
    }

    public function setForumId($forumId) {
      $this->forumId = $forumId;
    }

    public function getAnnouncement() {
        return $this->announcement;
    }

    public function setAnnouncement($announcement) {
        $this->announcement = $announcement;
    }

    public function getAnnouncementSubmissionId() {
        return $this->announcementSubmissionId;
    }

    public function setAnnouncementSubmissionId($id) {
        $this->announcementSubmissionId = $id;
    }
}
