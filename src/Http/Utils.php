<?php

namespace Flashy\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class Utils
{
    public static $knownContentTypes = ['text/html', 'application/json', 'text/xml', 'application/xml'];

    public static function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), static::$knownContentTypes);
        if (count($selectedContentTypes)) {
            return current($selectedContentTypes);
        }

        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, static::$knownContentTypes)) {
                return $mediaType;
            }
        }
        return 'text/html';
    }

    public static function dumpHeader(ServerRequestInterface $request)
    {
        $headers = [];
        $headers[] = sprintf("> %s %s HTTP/%s", $request->getMethod(), $request->getUri(), $request->getProtocolVersion());
        
        $requestHeaders = $request->getHeaders();
        ksort($requestHeaders, SORT_STRING);

        foreach (array_keys($requestHeaders) as $header) {
            $headers[] = sprintf("> %s: %s", $header, $request->getHeaderLine($header));
        }

        return $headers;
    }

    public static function json(StreamInterface $body)
    {
        $assoc = (bool) $this->options['forceArray'];
        $string = (string) $body;
        if ($string === '') {
            return [];
        }
        $data = json_decode($string, $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return $data ?: [];
    }
    
    public static function urlencode(StreamInterface $body)
    {
        parse_str((string) $body, $data);
        return $data ?: [];
    }
    
    public static function csv(StreamInterface $body)
    {
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $stream = $body->detach();
        $data = [];
        while (($row = fgetcsv($stream)) !== false) {
            $data[] = $row;
        }
        fclose($stream);
        return $data;
    }

    public static function dumpPayload(ServerRequestInterface $request)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $body = $request->getBody();

        if ($contentType == 'application/json') {
            $json = static::json($body);
            return $json ? json_encode($json, JSON_PRETTY_PRINT) : 'Invalid JSON payload';
        } elseif ($contentType == 'application/x-www-form-urlencoded') {
            return var_export(static::urlencode($body), true);
        } elseif ($contentType == 'text/csv') {
            return json_encode(static::csv($body), JSON_PRETTY_PRINT);
        }

        return $request->getMethod() == 'GET' ? 'No payload' : 'Can not display multipart/form-data payload';
    }
}
