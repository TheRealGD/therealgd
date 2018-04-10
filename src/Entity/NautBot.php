<?php 
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NautBotRepository")
 */
class NautBot {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Forum")
     */
    private $forum;

    /**
     * @ORM\Column(type="string", nullable=true)
     */ 
    private $deep;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $shallow;

    /**
     * @ORM\OneToMany(targetEntity="RateLimit", mappedBy="nautBot")
     *
     * @var RateLimit[]|Collectoin|Selectable
     */
    private $rateLimits;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    public function __construct() {
        $this->rateLimits = new ArrayCollection();
    }

    public function getForum() {
        return $this->forum;
    }

    public function setForum($forum) {
        $this->forum = $forum;
    }

    public function getShallow() {
        return $this->shallow;
    }

    public function setShallow($shallow) {
        $this->shallow = $shallow;
    }

    public function getDeep() {
        return $this->Deep;
    }

    public function setDeep($Deep) {
        $this->deep = $Deep;
    }

    /**
     * @return Collection|Selectable|RateLimit[]
     */
    public function getrateLimits() {
        return $this->rateLimits;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}
