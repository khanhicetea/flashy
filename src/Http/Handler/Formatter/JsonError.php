<?php
namespace Flashy\Http\Handler\Formatter;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RingCentral\Psr7\Stream;

class JsonError {
    protected $template;

    public function output(ServerRequestInterface $request, ResponseInterface $response, Throwable $e) {
        $output = json_encode([
            'message' => 'Oopps ! Something went wrong !',
            'description' => "Don't worry ! It's not your fault, but our fault :( We will fix it soon.",
        ]);

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($output);
        
        return $response->withHeader('Content-type', 'application/json')->withBody($body);
    }
}