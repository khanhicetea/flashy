<?php
namespace Flashy\Http\Handler\Formatter;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RingCentral\Psr7\Stream;

class XmlError
{
    protected $template;

    protected function processErrorBody(ServerRequestInterface $request, Throwable $e) : string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><root><message>Not found</message><description>Don\'t worry ! It\'s not your fault, but our fault :( We will fix it soon.</description></root>';
    }

    public function output(ServerRequestInterface $request, ResponseInterface $response, Throwable $e)
    {
        $output = $this->processErrorBody($request, $e);

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($output);
        
        return $response->withHeader('Content-type', 'text/xml')->withBody($body);
    }
}
