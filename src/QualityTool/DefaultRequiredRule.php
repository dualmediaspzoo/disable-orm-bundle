<?php

declare(strict_types=1);

namespace DualMedia\DisableORMBundle\QualityTool;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use DualMedia\DisableORMBundle\Attribute\DisableORM;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
class DefaultRequiredRule implements Rule
{
    private const string ORM_COLUMN = Column::class;
    private const string ORM_JOIN_COLUMN = JoinColumn::class;

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(
        Node $node,
        Scope $scope
    ): array {
        $classReflection = $scope->getClassReflection();

        if (null === $classReflection || $classReflection->isAnonymous()) {
            return [];
        }

        $errors = [];

        /** @var Node\Stmt\Class_ $classNode */
        $classNode = $node->getOriginalNode();

        foreach ($classNode->getProperties() as $property) {
            if (!$this->hasAttribute($property)) {
                continue;
            }

            $propertyName = $property->props[0]->name->toString();

            if ($this->hasInvalidColumn($property)) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf(
                        'Property $%s has #[DisableORM] but its #[ORM\Column] is not nullable and has no default value.',
                        $propertyName,
                    )
                )
                    ->identifier('dualmedia.disableOrm.missingDefault')
                    ->line($property->getStartLine())
                    ->build();
            }

            if ($this->hasInvalidJoinColumn($property)) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf(
                        'Property $%s has #[DisableORM] but its #[ORM\JoinColumn] is not nullable.',
                        $propertyName,
                    )
                )
                    ->identifier('dualmedia.disableOrm.missingDefault')
                    ->line($property->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function hasAttribute(
        Node\Stmt\Property $property
    ): bool {
        foreach ($property->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (DisableORM::class === $attr->name->toString()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findAttribute(
        Node\Stmt\Property $property,
        string $attributeClass
    ): Node\Attribute|null {
        foreach ($property->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === $attributeClass) {
                    return $attr;
                }
            }
        }

        return null;
    }

    private function hasInvalidColumn(
        Node\Stmt\Property $property
    ): bool {
        $attr = $this->findAttribute($property, self::ORM_COLUMN);

        if (null === $attr) {
            return false;
        }

        if ($this->isNullable($attr)) {
            return false;
        }

        return !$this->hasDefaultOption($attr);
    }

    private function hasInvalidJoinColumn(
        Node\Stmt\Property $property
    ): bool {
        $attr = $this->findAttribute($property, self::ORM_JOIN_COLUMN);

        if (null === $attr) {
            return false;
        }

        return !$this->isNullable($attr, true);
    }

    private function isNullable(
        Node\Attribute $attr,
        bool $default = false
    ): bool {
        foreach ($attr->args as $arg) {
            if (null !== $arg->name && 'nullable' === $arg->name->toString()) {
                return $arg->value instanceof Node\Expr\ConstFetch
                    && 'true' === $arg->value->name->toLowerString();
            }
        }

        return $default;
    }

    private function hasDefaultOption(
        Node\Attribute $attr
    ): bool {
        foreach ($attr->args as $arg) {
            if (null === $arg->name || 'options' !== $arg->name->toString()) {
                continue;
            }

            if (!$arg->value instanceof Node\Expr\Array_) {
                return false;
            }

            foreach ($arg->value->items as $item) {
                if (null === $item) { // @phpstan-ignore-line
                    continue;
                }

                if ($item->key instanceof Node\Scalar\String_ && 'default' === $item->key->value) {
                    return true;
                }
            }
        }

        return false;
    }
}
