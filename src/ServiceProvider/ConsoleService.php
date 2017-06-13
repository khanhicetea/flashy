<?php
namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;
use function DI\object;
use function DI\get;

class ConsoleService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []) : void
    {
        $def = array_merge([
            'console.name' => 'Flashy',
            'console.version' => '1.0.0',
            'console.register_func' => null,
        ], $opts);

        $def['console'] = get(Application::class);
        $def[Application::class] = object()
            ->constructor(get('console.name'), get('console.version'), get(ContainerInterface::class));

        $builder->addDefinitions($def);
    }
}
