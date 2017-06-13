<?php
namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use function DI\object;
use function DI\get;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;

class EloquentService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []) : void
    {
        $def = array_merge([
            'db.connection' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => null,
                'username' => 'root',
                'password' => null,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => null,
            ],
        ], $opts);

        $def['capsule'] = get(Manager::class);
        $def['db'] = function (ContainerInterface $c) {
            return $c['capsule']->getDatabaseManager();
        };
        $def[Manager::class] = object()
            ->method('addConnection', get('db.connection'))
            ->method('bootEloquent')
            ->method('setAsGlobal');

        $builder->addDefinitions($def);
    }
}
