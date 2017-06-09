<?php
namespace Flashy\Http\Route;

use League\Route\RouteCollection;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Psr\Container\ContainerInterface;

abstract class Router extends RouteCollection
{
    public function __construct(ContainerInterface $container, RouteParser $parser = null, DataGenerator $generator = null)
    {
        parent::__construct($container, $parser, $generator);
    }

    public function getRouteParser() {
        return $this->routeParser;
    }

    abstract public function loadRoutes();
}
