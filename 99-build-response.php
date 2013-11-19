<?php

function buildResponse(array $responseSpec)
{
    list($status, $headers, $body) = $responseSpec;

    return  buildResponseStatusLine($status).
            buildResponseHeaders($headers).
            "\r\n".
            $body;
}

function buildResponseStatusLine($status)
{
    $statusMap = [
        200 => 'OK',
        404 => 'Not Found',
    ];

    return "HTTP/1.1 $status {$statusMap[$status]}\r\n";
}

function buildResponseHeaders(array $headers)
{
    $flatHeaders = [];

    foreach ($headers as $name => $value) {
        $flatHeaders[] = "$name: $value";
    }

    return implode("\r\n", $flatHeaders)."\r\n";
}
