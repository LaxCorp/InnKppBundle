<?php

namespace LaxCorp\InnKppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the InnValidator.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Inn extends Constraint
{

}