<?php
namespace Flashy\Http\Handler;

use Throwable;
use League\Route\Http\Exception as HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Flashy\Http\Handler\Formatter\DebugError;
use Flashy\Http\Handler\Formatter\HtmlError;
use Flashy\Http\Handler\Formatter\JsonError;
use Flashy\Http\Handler\Formatter\XmlError;
use Flashy\Http\Utils;

class HttpErrorHandler
{
    private $handlers = [];
    private $debug;
    private $debug_handler;
    
    public function __construct(
        DebugError $debugError,
        HtmlError $htmlError,
        JsonError $jsonError,
        XmlError $xmlError,
        bool $debug = true
    ) {
        $this->debug = $debug;

        if ($this->debug) {
            $this->debug_handler = $debugError;
        } else {
            $this->handlers = [
                'html' => $htmlError,
                'json' => $jsonError,
                'xml' => $xmlError,
            ];
        }
    }

    public function getHandler(string $contentType)
    {
        $parts = explode('/', $contentType);
        $type = $parts[1] ?? $parts[0];
        return $this->debug ? $this->debug_handler : $this->handlers[$type];
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, Throwable $e) : ResponseInterface
    {
        if ($e instanceof HttpException) {
            return $this->handleHttpException($request, $response, $e);
        } elseif ($e instanceof Throwable) {
            return $this->handleException($request, $response, $e);
        }
    }

    protected function handleHttpException(ServerRequestInterface $request, ResponseInterface $response, HttpException $e)
    {
        if ($request->getMethod() == 'OPTIONS') {
            return $response
                ->withStatus(200)
                ->withHeader('Content-type', 'text/plain')
                ->getBody()->write($e->getMessage());
        }

        $headers = $e->getHeaders();
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        $contentType = Utils::determineContentType($request);
        $response = $response->withStatus($e->getStatusCode());

        return $this->getHandler($contentType)->output($request, $response, $e);
    }

    protected function handleException(ServerRequestInterface $request, ResponseInterface $response, Throwable $e)
    {
        if ($request->getMethod() == 'OPTIONS') {
            return $response
                ->withStatus(200)
                ->withHeader('Content-type', 'text/plain')
                ->getBody()->write($e->getMessage());
        }

        $contentType = Utils::determineContentType($request);
        $response = $response->withStatus(500);

        return $this->getHandler($contentType)->output($request, $response, $e);
    }
}
