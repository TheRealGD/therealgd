<?php

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms usernames into {@link User} objects and vice versa.
 */
class UserTransformer implements DataTransformerInterface {
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value) {
        if ($value instanceof User) {
            return $value->getUsername();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value) {
        if (strlen($value) > 0) {
            return $this->userRepository->loadUserByUsername($value);
        }

        return null;
    }
}
