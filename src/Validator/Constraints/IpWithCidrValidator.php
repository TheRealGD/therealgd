<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IpWithCidrValidator extends ConstraintValidator {
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint) {
        if (!$constraint instanceof IpWithCidr) {
            throw new UnexpectedTypeException($constraint, IpWithCidr::class);
        }

        if (!is_scalar($value) && method_exists($value, '__toString')) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        if ($value === '') {
            return;
        }

        list($ip, $cidr) = array_pad(explode('/', $value, 2), 2, null);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->context->buildViolation($constraint->invalidIpMessage)
                ->setCode(IpWithCidr::INVALID_IP)
                ->addViolation();

            return;
        }

        if ($cidr === null) {
            if (!$constraint->cidrOptional) {
                $this->context->buildViolation($constraint->missingCidrMessage)
                    ->setCode(IpWithCidr::MISSING_CIDR)
                    ->addViolation();
            }
        } else {
            $maxCidr = strpos($ip, ':') !== false ? 128 : 32;

            if (!ctype_digit($cidr) || $cidr < 0 || $cidr > $maxCidr) {
                $this->context->buildViolation($constraint->invalidCidrMessage)
                    ->setCode(IpWithCidr::INVALID_CIDR)
                    ->addViolation();
            }
        }
    }
}
