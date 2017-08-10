<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Raddit\AppBundle\Validator\Constraints\Css;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A custom stylesheet. Can be applied to forums, user pages, etc.
 *
 * @ORM\Entity(repositoryClass="Raddit\AppBundle\Repository\StylesheetRepository")
 * @ORM\Table(name="stylesheets", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="stylesheets_user_name_idx", columns={"user_id", "name"})
 * })
 *
 * @UniqueEntity("name")
 */
class Stylesheet {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     *
     * @var string|null
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100000)
     * @Css()
     *
     * @var string|null
     */
    private $css;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $appendToDefaultStyle = true;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $nightFriendly = false;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User|null
     */
    private $user;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    public function __construct() {
        $this->timestamp = new \DateTime('@'.time());
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getCss() {
        return $this->css;
    }

    /**
     * @param null|string $css
     */
    public function setCss($css) {
        $this->css = $css;
    }

    /**
     * @return bool
     */
    public function isAppendToDefaultStyle(): bool {
        return $this->appendToDefaultStyle;
    }

    /**
     * @param bool $appendToDefaultStyle
     */
    public function setAppendToDefaultStyle(bool $appendToDefaultStyle) {
        $this->appendToDefaultStyle = $appendToDefaultStyle;
    }

    /**
     * @return bool
     */
    public function isNightFriendly(): bool {
        return $this->nightFriendly;
    }

    /**
     * @param bool $nightFriendly
     */
    public function setNightFriendly(bool $nightFriendly) {
        $this->nightFriendly = $nightFriendly;
    }

    /**
     * @return null|User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param null|User $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp) {
        $this->timestamp = $timestamp;
    }
}
