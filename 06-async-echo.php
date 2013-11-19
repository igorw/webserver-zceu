<?php

require __DIR__.'/99-event-loop.php';

$server = stream_socket_server('tcp://0.0.0.0:5000');
stream_set_blocking($server, 0);

$loop = new EventLoop();

$loop->onReadable($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server, 0);
    stream_set_blocking($conn, 0);

    $buffer = '';

    $loop->onReadable($conn, function ($conn) use ($loop, &$buffer) {
        $data = fread($conn, 1024);

        if (!$data) {
            $loop->remove($conn);
            fclose($conn);
            return;
        }

        $buffer .= $data;

        $loop->enableWrites($conn);
    });

    $loop->onWritable($conn, function ($conn) use ($loop, &$buffer) {
        $written = fwrite($conn, $buffer);
        $buffer = (string) substr($buffer, $written);

        if (strlen($buffer) === 0) {
            $loop->disableWrites($conn);
        }
    });
});

$loop->run();

// BUG: closes too early
