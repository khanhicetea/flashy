<?php

namespace Flashy\Http\Route;

use League\Route\RouteCollection;
use League\Route\Dispatcher;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Router extends RouteCollection
{
    private $prepared = false;

    public function __construct(ContainerInterface $container, RouteParser $parser = null, DataGenerator $generator = null)
    {
        parent::__construct($container, $parser, $generator);
    }

    public function getRouteParser()
    {
        return $this->routeParser;
    }

    public function getDispatcher(ServerRequestInterface $request)
    {
        if (is_null($this->getStrategy())) {
            $this->setStrategy(new ApplicationStrategy());
        }

        if (!$this->prepared) {
            $this->prepared = true;
            $this->prepRoutes($request);
        }

        return (new Dispatcher($this->getData()))->setStrategy($this->getStrategy());
    }

    abstract public function loadRoutes();
}
