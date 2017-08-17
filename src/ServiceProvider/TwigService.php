<?php

namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\object;
use function DI\get;
use Twig_Environment;
use Twig_LoaderInterface;
use Twig_Loader_Filesystem;

class TwigService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []): void
    {
        $def = array_merge([
            'twig.path' => null,
            'twig.options' => [],
        ], $opts);

        $def['twig'] = get(Twig_Environment::class);
        $def['twig.loader'] = get(Twig_LoaderInterface::class);
        $def[Twig_LoaderInterface::class] = object(Twig_Loader_Filesystem::class)
            ->constructor(get('twig.path'));
        $def[Twig_Environment::class] = object()
            ->constructor(get('twig.loader'), get('twig.options'));

        $builder->addDefinitions($def);
    }
}
