<?php

namespace DualMedia\DisableORMBundle;

use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\Mapping\FieldMapping;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\TypedFieldMapper;

/**
 * @template-covariant T of object
 *
 * @template-extends DoctrineClassMetadata<T>
 */
class ClassMetadata extends DoctrineClassMetadata
{
    /**
     * @param list<string> $disabledFields
     */
    public function __construct(
        string $name,
        NamingStrategy|null $namingStrategy = null,
        TypedFieldMapper|null $typedFieldMapper = null,
        private readonly array $disabledFields = []
    ) {
        parent::__construct($name, $namingStrategy, $typedFieldMapper);
    }

    public function mapField(
        array $mapping
    ): void {
        if (in_array($mapping['fieldName'], $this->disabledFields)) {
            return;
        }

        parent::mapField($mapping);
    }

    public function addInheritedFieldMapping(
        FieldMapping $fieldMapping
    ): void {
        if (in_array($fieldMapping['fieldName'], $this->disabledFields)) {
            return;
        }

        parent::addInheritedFieldMapping($fieldMapping);
    }

    protected function _storeAssociationMapping(
        AssociationMapping $assocMapping
    ): void {
        if (in_array($assocMapping['fieldName'], $this->disabledFields)) {
            return;
        }

        parent::_storeAssociationMapping($assocMapping);
    }
}
