<?php

namespace DualMedia\DisableORMBundle;

use DualMedia\DisableORMBundle\Attribute\DisableORM;

class DisabledReflector
{
    /**
     * @var array<class-string, list<string>>
     */
    private array $cache = [];

    /**
     * @param class-string $class
     *
     * @return list<string>
     */
    public function getDisabledFields(
        string $class
    ): array {
        if (!array_key_exists($class, $this->cache)) {
            $this->cache[$class] = $this->readFields($class);
        }

        return $this->cache[$class];
    }

    /**
     * @param class-string $class
     *
     * @return list<string>
     */
    private function readFields(
        string $class
    ): array {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException) { // @phpstan-ignore-line
            return [];
        }

        $fields = [];

        foreach ($reflection->getProperties() as $property) {
            if (empty($property->getAttributes(DisableORM::class))) {
                continue;
            }

            $fields[] = $property->getName();
        }

        return $fields;
    }
}
