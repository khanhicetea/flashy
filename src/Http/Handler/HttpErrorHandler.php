<?php
namespace Flashy\Http\Handler;

use Throwable;
use League\Route\Http\Exception\MethodNotAllowedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Flashy\Http\Handler\Formatter\DebugError;
use Flashy\Http\Handler\Formatter\HtmlError;
use Flashy\Http\Handler\Formatter\JsonError;
use Flashy\Http\Utils;

class HttpErrorHandler {
    private $handlers = [];
    private $debug;
    private $debug_handler;
    
    public function __construct(
        DebugError $debugError,
        HtmlError $htmlError,
        JsonError $jsonError,
        bool $debug = false
    ) {
        $this->debug = $debug;

        if ($this->debug) {
            $this->debug_handler = $debugError;
        } else {
            $this->handlers = [
                'html' => $htmlError,
                'json' => $jsonError,
            ];
        }
    }

    public function getHandler(string $contentType) {
        $parts = explode('/', $contentType);
        $type = $parts[1] ?? $parts[0];
        return $this->debug ? $this->debug_handler : $this->handlers[$type];
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, Throwable $e) : ResponseInterface {
        if ($e instanceof MethodNotAllowedException) {
            return $this->handleMethodNotAllowed($request, $response, $e);
        }
    }

    protected function handleMethodNotAllowed(ServerRequestInterface $request, ResponseInterface $response, MethodNotAllowedException $e) {
        $statusCode = $request->getMethod() == 'OPTIONS' ? 200 : 405;
        $contentType = $request->getMethod() == 'OPTIONS' ? 'text/plain' : Utils::determineContentType($request);
        $headers = $e->getHeaders();

        $response = $response
            ->withStatus($statusCode)
            ->withHeader('Content-type', $contentType)
            ->withHeader('Allow', $headers['Allow']);

        return $this->getHandler($contentType)->output($request, $response, $e);
    }
}