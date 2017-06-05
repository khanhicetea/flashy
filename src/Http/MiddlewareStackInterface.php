<?php
namespace Flashy\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareStackInterface {
    public function addMiddleware(callable $middleware) : MiddlewareStackInterface;
    public function loadMiddlewares() : void;
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface;
}
