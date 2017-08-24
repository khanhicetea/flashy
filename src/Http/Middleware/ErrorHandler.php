<?php
namespace Flashy\Http\Middleware;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Flashy\Http\Handler\HttpErrorHandler;

class ErrorHandler implements MiddlewareInterface
{
    public function __construct(HttpErrorHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface
    {
        try {
            return $next($request, $response);
        } catch (Throwable $e) {
            return $this->handler->handle($request, $response, $e);
        }
    }
}
