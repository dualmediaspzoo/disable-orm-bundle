<?php

namespace DualMedia\DisableORMBundle;

use DualMedia\DisableORMBundle\EventSubscriber\CommandSubscriber;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DisableORMBundle extends AbstractBundle
{
    protected string $extensionAlias = 'dm_disable_orm';

    public function configure(
        DefinitionConfigurator $definition
    ): void {
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('disable_on_commands')
                ->useAttributeAsKey('name')
                ->defaultValue([
                    'doctrine:schema:validate',
                    'doctrine:migrations:diff',
                ])
                ->info('This value lets you specify when the functionality must be disabled. By default it should apply only to database operations and schema verification.')
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        $loader = new PhpFileLoader(
            $builder,
            new FileLocator(__DIR__.'/../config')
        );

        $loader->load('services.php');

        $services = $container->services();

        $services->set(CommandSubscriber::class)
            ->arg('$commands', $config['disable_on_commands']);
    }
}
