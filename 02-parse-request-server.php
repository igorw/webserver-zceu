<?php

require __DIR__.'/99-parse-request.php';
require __DIR__.'/99-build-response.php';

$server = stream_socket_server('tcp://0.0.0.0:5000');

while (true) {
    $conn = stream_socket_accept($server, -1);

    $request = fread($conn, 512);
    if (!$request)
        continue;

    $parsedRequest = parseRequest($request);

    switch ($parsedRequest['path']) {
        case '/':
            $responseSpec = [200, ['Content-Length' => 3], "Hi\n"];
            break;
        default:
            $responseSpec = [404, ['Content-Length' => 10], "Not found\n"];
            break;
    }

    fwrite($conn, buildResponse($responseSpec));
    fclose($conn);
}
