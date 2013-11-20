<?php

require __DIR__.'/99-event-loop.php';

$server = stream_socket_server('tcp://0.0.0.0:5000');

$loop = new EventLoop();

$loop->onReadable($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server, 0);

    $loop->onReadable($conn, function ($conn) use ($loop) {
        $data = fread($conn, 1024);

        if (!$data) {
            $loop->remove($conn);
            fclose($conn);
            return;
        }

        echo "[$conn] $data";
    });
});

$loop->run();
