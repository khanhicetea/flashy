<?php
namespace Flashy\Http;
use League\Route\RouteCollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel {
    private $routing;

    public function __construct(RouteCollectionInterface $routing) {
        $this->routing = $routing;
    }

    public function getRouting() : RouteCollectionInterface {
        return $this->routing;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface {
        $response = $this->routing->dispatch($request, $response);

        return $next($request, $response);
    }
}
