<?php

namespace App\Form\Model;

use App\Entity\UserGroup;
use App\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entityClass="App\Entity\UserGroup", errorPath="name",
 *     fields={"normalizedName"}, idFields={"entityId": "id"})
 */
final class UserGroupData {
    /**
     * @var int|null
     */
    private $entityId;

    /**
     * @Assert\Length(min=3, max=40)
     * @Assert\NotBlank()
     * @Assert\Regex("/^\w+$/")
     *
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $normalizedName;

    /**
     * @Assert\Length(min=1, max=80)
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $title;

    /**
     * @var bool
     */
    private $displayTitle = false;

    public function __construct(UserGroup $userGroup = null) {
        if ($userGroup) {
            $this->entityId = $userGroup->getId();
            $this->setName($userGroup->getName());
            $this->title = $userGroup->getTitle();
            $this->displayTitle = $userGroup->getDisplayTitle();
        }
    }

    public function toUserGroup(): UserGroup {
        return new UserGroup($this->name, $this->title, $this->displayTitle);
    }

    public function updateUserGroup(UserGroup $userGroup): void {
        $userGroup->setName($this->name);
        $userGroup->setTitle($this->title);
        $userGroup->setDisplayTitle($this->displayTitle);
    }

    public function getEntityId(): ?int {
        return $this->entityId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): void {
        $this->name = $name;
        $this->normalizedName = isset($name)
            ? UserGroup::normalizeName($name)
            : null;
    }

    public function getNormalizedName(): ?string {
        return $this->normalizedName;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }


    public function getDisplayTitle() {
        return $this->displayTitle;
    }
    public function setDisplayTitle($displayTitle) {
        $this->displayTitle = $displayTitle;
    }
}
