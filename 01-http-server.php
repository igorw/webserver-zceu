<?php

$server = stream_socket_server('tcp://0.0.0.0:5000');

while (true) {
    $response = implode("\r\n", [
        'HTTP/1.1 200 OK',
        'Content-Length: 3',
        '',
        "Hi\n",
    ]);

    $conn = stream_socket_accept($server, -1);
    fwrite($conn, $response);
    fclose($conn);
}
