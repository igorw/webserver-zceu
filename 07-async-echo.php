<?php

require __DIR__.'/99-event-loop.php';

$server = stream_socket_server('tcp://0.0.0.0:5000');

$loop = new EventLoop();

$loop->onReadable($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server, 0);

    $buffer = '';
    $closing = false;

    $loop->onReadable($conn, function ($conn) use ($loop, &$buffer, &$closing) {
        $data = fread($conn, 1024);

        if (!$data) {
            $closing = true;
            $loop->disableReads($conn);
            return;
        }

        $buffer .= $data;

        $loop->enableWrites($conn);
    });

    $loop->onWritable($conn, function ($conn) use ($loop, &$buffer, &$closing) {
        $written = fwrite($conn, $buffer);
        $buffer = (string) substr($buffer, $written);

        if (strlen($buffer) === 0) {
            $loop->disableWrites($conn);
        }

        if ($closing && strlen($buffer) === 0) {
            $loop->remove($conn);
            fclose($conn);
        }
    });
});

$loop->run();

// BUG: only closes in write handler
