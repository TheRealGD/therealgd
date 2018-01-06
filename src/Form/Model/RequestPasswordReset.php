<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordReset {
    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }
}
