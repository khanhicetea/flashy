<?php

namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;
use function DI\object;
use function DI\get;

class FlashyService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []): void
    {
        $def = array_merge([
            'app.debug' => false,
        ], $opts);

        $def['console'] = get(Application::class);
        $def[Application::class] = object()
            ->constructor(get('console.name'), get('console.version'), get(ContainerInterface::class));

        $builder->addDefinitions($def);
    }
}
