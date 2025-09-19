<?php

namespace DualMedia\DisableORMBundle\Tests\Unit;

use DualMedia\DisableORMBundle\DisabledReflector;
use DualMedia\DisableORMBundle\Tests\Model\ExampleEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[CoversClass(DisabledReflector::class)]
class DisabledReflectorTest extends TestCase
{
    public function test(): void
    {
        static::assertEquals([
            'disabled',
        ], (new DisabledReflector())->getDisabledFields(ExampleEntity::class));
    }
}
