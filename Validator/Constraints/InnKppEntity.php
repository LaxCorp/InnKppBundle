<?php

namespace LaxCorp\InnKppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the InnKppEntityValidator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class InnKppEntity extends Constraint
{

    const NOT_UNIQUE_ERROR = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f';

    /**
     * @var string
     */
    public $service = 'laxgroup.inn_kpp_entity_validator';

    /**
     * @var null
     */
    public $em = null;

    /**
     * @var string
     */
    public $repositoryMethod = 'findBy';

    /**
     * @var null
     */
    public $fieldInn = null;

    /**
     * @var null
     */
    public $fieldKpp = null;

    /**
     * @var null
     */
    public $fields = null;

    /**
     * @var null
     */
    public $errorPath = null;

    /**
     * @var bool
     */
    public $ignoreNull = true;

    /**
     * @var array
     */
    protected static $errorNames = [
        self::NOT_UNIQUE_ERROR => 'NOT_UNIQUE_ERROR',
    ];

    /**
     * Returns the name of the required options.
     *
     * Override this method if you want to define required options.
     *
     * @return array
     *
     * @see __construct()
     */
    public function getRequiredOptions()
    {
        return ['fieldInn', 'fieldKpp'];
    }

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
