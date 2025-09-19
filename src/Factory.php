<?php

namespace DualMedia\DisableORMBundle;

use Doctrine\Bundle\DoctrineBundle\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;

class Factory extends ClassMetadataFactory
{
    private EntityManagerInterface $em;
    private DisabledReflector $reflector;

    private static bool $disableORMMode = true;

    public function __construct()
    {
        $this->reflector = new DisabledReflector();
    }

    public static function setDisableORMMode(
        bool $mode
    ): void {
        self::$disableORMMode = $mode;
    }

    public static function getDisableORMMode(): bool
    {
        return self::$disableORMMode;
    }

    public function setEntityManager(
        EntityManagerInterface $em
    ): void {
        parent::setEntityManager($em);
        $this->em = $em;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return ClassMetadata<T>
     */
    protected function newClassMetadataInstance(
        string $className
    ): ClassMetadata {
        return new ClassMetadata(
            $className,
            $this->em->getConfiguration()->getNamingStrategy(),
            $this->em->getConfiguration()->getTypedFieldMapper(),
            self::$disableORMMode ? $this->reflector->getDisabledFields($className) : []
        );
    }
}
