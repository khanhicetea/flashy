<?php
namespace Flashy\ServiceProvider;

use Flashy\ServiceProviderInterface;
use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use function DI\object;
use function DI\get;

class LogService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []) : void
    {
        $def = array_merge([
            'logger.name' => 'Flashy',
            'logger.stream' => false,
            'logger.level' => Logger::DEBUG,
        ], $opts);

        $def['logger'] = get(LoggerInterface::class);
        $def['logger.handler'] = object(StreamHandler::class)
            ->constructor(get('logger.stream'), get('logger.level'));
        $def[LoggerInterface::class] = object(Logger::class)
            ->constructor(get('logger.name'))
            ->method('pushHandler', get('logger.handler'));

        $builder->addDefinitions($def);
    }
}
