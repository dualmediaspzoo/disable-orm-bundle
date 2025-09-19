<?php

namespace DualMedia\DisableORMBundle\EventSubscriber;

use DualMedia\DisableORMBundle\Factory;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandSubscriber implements EventSubscriberInterface
{
    /**
     * @param list<string> $commands
     */
    public function __construct(
        private readonly array $commands
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => 'onCommand',
        ];
    }

    public function onCommand(
        ConsoleCommandEvent $event
    ): void {
        if (!in_array($event->getCommand()?->getName(), $this->commands)) {
            return;
        }

        Factory::setDisableORMMode(false);
    }
}
