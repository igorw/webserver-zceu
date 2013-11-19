<?php

function parseRequest($request)
{
    list($head, $body) = explode("\r\n\r\n", $request, 2);

    $lines = explode("\r\n", $head);

    $requestLine = array_shift($lines);
    list($method, $path, $protocol) = explode(' ', $requestLine);

    $headers = [];
    while ($header = array_shift($lines)) {
        list($name, $value) = explode(':', $header, 2);
        $headers[trim($name)] = trim($value);
    }

    return compact('method', 'path', 'protocol', 'headers', 'body');
}

$parsed = [
    'method'    => 'GET',
    'path'      => '/',
    'protocol'  => 'HTTP/1.1',
    'headers'   => ['Host' => 'igor.io'],
    'body'      => '',
];
