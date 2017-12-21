<?php

namespace LaxGroup\InnKppBundle\Validator\Constraints;

use InnKppBundle\Validator\innVlaidator;
use InnKppBundle\Validator\kppValidator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class InnKppEntityValidator
 *
 * @package InnKppBundle\Validator\Constraints
 */
class InnKppEntityValidator extends ConstraintValidator
{

    const MESSAGE_ALREADY_USED = 'This value is already used.';
    const MESSAGE_KPP_REQUIRED = 'INN is less than 12 digits, you must enter KPP';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * InnKppEntityValidator constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object     $entity
     * @param Constraint $constraint
     *
     * @throws UnexpectedTypeException
     * @throws ConstraintDefinitionException
     */
    public function validate($entity, Constraint $constraint)
    {

        if (!$constraint instanceof InnKppEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\InnKppEntity');
        }

        if (!is_string($constraint->fieldInn)) {
            throw new UnexpectedTypeException($constraint->fieldInn, 'string');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fieldInn = (string)$constraint->fieldInn;

        if (!$fieldInn) {
            throw new ConstraintDefinitionException('fieldInn has to be specified.');
        }

        $fieldKpp = (string)$constraint->fieldKpp;

        if (!$fieldKpp) {
            throw new ConstraintDefinitionException('fieldKpp has to be specified.');
        }

        if ($constraint->em) {
            $em = $this->registry->getManager($constraint->em);

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->em));
            }
        } else {
            $em = $this->registry->getManagerForClass(get_class($entity));

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', get_class($entity)));
            }
        }

        /* @var $class ClassMetadata */
        $class = $em->getClassMetadata(get_class($entity));

        $criteria = [];

        if (!$class->hasField($fieldInn) && !$class->hasAssociation($fieldInn)) {
            throw new ConstraintDefinitionException(sprintf('The fieldInn "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $fieldInn));
        }

        $innValue     = $class->reflFields[$fieldInn]->getValue($entity);
        $errorMessage = null;

        $innValidator = new innVlaidator();

        if ($innValue !== null && !$innValidator->validate($innValue, $errorMessage)) {

            $this->context->buildViolation($errorMessage)
                ->atPath($fieldInn)
                ->setParameter('{{ value }}', $this->formatWithIdentifiers($em, $class, $innValue))
                ->setInvalidValue($innValue)
                ->addViolation();

            return;
        }

        $kppValue     = $class->reflFields[$fieldKpp]->getValue($entity);
        $errorMessage = null;

        $kppValidator = new kppValidator();

        if ($kppValue !== null && !$kppValidator->validate($kppValue, $errorMessage)) {

            $this->context->buildViolation($errorMessage)
                ->atPath($fieldKpp)
                ->setParameter('{{ value }}', $this->formatWithIdentifiers($em, $class, $kppValue))
                ->setInvalidValue($kppValue)
                ->addViolation();

            return;
        }

        if ($innValue && $this->isRequireKpp($innValue) && !$kppValue) {
            $this->context->buildViolation($this::MESSAGE_KPP_REQUIRED)
                ->atPath($fieldKpp)
                ->setParameter('{{ value }}', $this->formatWithIdentifiers($em, $class, $kppValue))
                ->setInvalidValue($kppValue)
                ->addViolation();

            return;
        }

        if ($constraint->ignoreNull && $innValue !== null) {

            $criteria[$fieldInn] = $innValue;

            if ($criteria[$fieldInn] !== null && $class->hasAssociation($fieldInn)) {
                $em->initializeObject($criteria[$fieldInn]);
            }

            if ($this->isRequireKpp($innValue)) {

                $criteria[$fieldKpp] = $kppValue;

                if ($criteria[$fieldKpp] !== null && $class->hasAssociation($fieldKpp)) {
                    $em->initializeObject($criteria[$fieldKpp]);
                }
            }
        }

        if (empty($criteria)) {
            return;
        }

        $repository = $em->getRepository(get_class($entity));
        $result     = $repository->{$constraint->repositoryMethod}($criteria);

        if ($result instanceof \IteratorAggregate) {
            $result = $result->getIterator();
        }

        /* If the result is a MongoCursor, it must be advanced to the first
         * element. Rewinding should have no ill effect if $result is another
         * iterator implementation.
         */
        if ($result instanceof \Iterator) {
            $result->rewind();
        } elseif (is_array($result)) {
            reset($result);
        }

        /* If no entity matched the query criteria or a single entity matched,
         * which is the same as the entity being validated, the criteria is
         * unique.
         */
        if (0 === count($result)
            || (1 === count($result)
                && $entity === ($result instanceof \Iterator ? $result->current() : current($result)))) {
            return;
        }

        $errorPath    = null !== $constraint->errorPath ? $constraint->errorPath : $fieldInn;
        $invalidValue = isset($criteria[$errorPath]) ? $criteria[$errorPath] : $criteria[$fieldInn];

        $this->context->buildViolation($this::MESSAGE_ALREADY_USED)
            ->atPath($errorPath)
            ->setParameter('{{ value }}', $this->formatWithIdentifiers($em, $class, $invalidValue))
            ->setInvalidValue($invalidValue)
            ->setCode(InnKppEntity::NOT_UNIQUE_ERROR)
            ->addViolation();
    }

    /**
     * @param ObjectManager $em
     * @param ClassMetadata $class
     * @param               $value
     *
     * @return string
     */
    private function formatWithIdentifiers(ObjectManager $em, ClassMetadata $class, $value)
    {
        if (!is_object($value) || $value instanceof \DateTimeInterface) {
            return $this->formatValue($value, self::PRETTY_DATE);
        }

        // non unique value is a composite PK
        if ($class->getName() !== $idClass = get_class($value)) {
            $identifiers = $em->getClassMetadata($idClass)->getIdentifierValues($value);
        } else {
            $identifiers = $class->getIdentifierValues($value);
        }

        if (!$identifiers) {
            return sprintf('object("%s")', $idClass);
        }

        array_walk($identifiers, function (&$id, $field) {
            if (!is_object($id) || $id instanceof \DateTimeInterface) {
                $idAsString = $this->formatValue($id, self::PRETTY_DATE);
            } else {
                $idAsString = sprintf('object("%s")', get_class($id));
            }

            $id = sprintf('%s => %s', $field, $idAsString);
        });

        return sprintf('object("%s") identified by (%s)', $idClass, implode(', ', $identifiers));
    }


    /**
     * @param string $inn
     *
     * @return bool
     */
    private function isRequireKpp(string $inn)
    {
        return (mb_strlen($inn) === 12) ? false : true;
    }


}
