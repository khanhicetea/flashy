<?php
namespace Flashy\Http;
use League\Route\RouteCollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel {
    private $middleware_stack;

    public function __construct(AbstractMiddlewareStack $middleware_stack) {
        $this->middleware_stack = $middleware_stack;
    }

    public function getRouting() : RouteCollectionInterface {
        return $this->middleware_stack->getRouting();
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface {
        $response = call_user_func($this->middleware_stack, $request, $response, $next);

        return $next($request, $response);
    }
}
