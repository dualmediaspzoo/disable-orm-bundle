<?php

use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->set(\DualMedia\DisableORMBundle\EventSubscriber\CommandSubscriber::class)
        ->arg('$commands', new AbstractArgument('Will be set via bundle configuration'))
        ->tag('kernel.event_subscriber');
};
