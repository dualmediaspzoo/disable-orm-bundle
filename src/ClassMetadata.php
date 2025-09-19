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
        parent::mapField($mapping);
        $this->removeField($mapping['fieldName']);
    }

    public function addInheritedFieldMapping(
        FieldMapping $fieldMapping
    ): void {
        parent::addInheritedFieldMapping($fieldMapping);
        $this->removeField($fieldMapping['fieldName']);
    }

    protected function _storeAssociationMapping(
        AssociationMapping $assocMapping
    ): void {
        $sourceFieldName = $assocMapping['fieldName'];

        if (in_array($sourceFieldName, $this->disabledFields)) {
            return;
        }

        parent::_storeAssociationMapping($assocMapping);
    }

    private function removeField(
        string $field
    ): void {
        if (empty($this->disabledFields) || !in_array($field, $this->disabledFields)) {
            return;
        }

        // we want to remove this field from entity
        $mapping = $this->fieldMappings[$field];

        unset($this->fieldMappings[$mapping['fieldName']]);
        unset($this->fieldNames[$mapping['columnName']]);
    }
}
