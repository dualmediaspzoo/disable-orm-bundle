<?php

namespace DualMedia\DisableORMBundle\Tests\Unit\EventSubscriber;

use DualMedia\DisableORMBundle\EventSubscriber\CommandSubscriber;
use DualMedia\DisableORMBundle\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Group('unit')]
#[Group('event-subscriber')]
#[CoversClass(CommandSubscriber::class)]
class CommandSubscriberTest extends TestCase
{
    protected function setUp(): void
    {
        Factory::setDisableORMMode(true);
    }

    protected function tearDown(): void
    {
        Factory::setDisableORMMode(true);
    }

    public function testSubscribedEvents(): void
    {
        static::assertEquals([
            ConsoleCommandEvent::class => 'onCommand',
        ], CommandSubscriber::getSubscribedEvents());
    }

    #[TestWith([
        true,
        'app:random',
    ])]
    #[TestWith([
        false,
        'doctrine:schema:validate',
    ])]
    #[TestWith([
        true,
        'app:other',
    ])]
    #[TestWith([
        false,
        'doctrine:migrations:diff',
    ])]
    public function testOnCommand(
        bool $state,
        string $name
    ): void {
        $command = $this->createMock(Command::class);
        $command->method('getName')->willReturn($name);

        (new CommandSubscriber([
            'doctrine:schema:validate',
            'doctrine:migrations:diff',
        ]))->onCommand(new ConsoleCommandEvent(
            $command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        ));

        static::assertEquals(
            $state,
            Factory::getDisableORMMode()
        );
    }
}
