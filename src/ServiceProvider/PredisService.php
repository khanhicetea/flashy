<?php
namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use Predis\Client;
use Psr\Container\ContainerInterface;
use function DI\object;
use function DI\get;

class PredisService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []) : void
    {
        $def = array_merge([
            'predis.connection' => 'tcp://127.0.0.1:6379',
            'predis.database' => 0,
        ], $opts);

        $def['predis'] = get(Client::class);
        $def[Client::class] = object()
            ->constructor(get('predis.connection_string'))
            ->method('select', get('predis.database'));
        $def[Client::class] = function (ContainerInterface $container) {
            // Redis class reflection will be wrong so we define it manually way
            $redis = new Client($container->get('predis.connection'));
            $redis->select(intval($container->get('predis.database')));

            return $redis;
        };

        $builder->addDefinitions($def);
    }
}
