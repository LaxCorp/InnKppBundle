<?php

/*
 * Inn validator
 */

namespace LaxCorp\InnKppBundle\Validator\Constraints;

use LaxCorp\InnKppBundle\Validator\InnVlaidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @inheritdoc
 */
class InnValidator extends ConstraintValidator
{

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof Inn) {
            throw new UnexpectedTypeException($constraint, Inn::class);
        }

        $message      = '';
        $innValidator = new InnVlaidator();

        if (!$innValidator->validate($value, $message)) {
            $this->context->buildViolation($message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode($message)
                ->addViolation();
        }

        return;
    }
}
