<?php
namespace Flashy\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DoNothing implements MiddlewareInterface {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface {
        return $response;
    }
}