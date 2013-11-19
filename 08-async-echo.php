<?php

require __DIR__.'/99-event-loop.php';

$server = stream_socket_server('tcp://0.0.0.0:5000');
stream_set_blocking($server, 0);

$loop = new EventLoop();

$loop->onReadable($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server, 0);
    stream_set_blocking($conn, 0);

    $buffer = '';
    $closing = false;

    $checkClose = function () use ($loop, &$conn, &$buffer, &$closing) {
        if ($closing && strlen($buffer) === 0) {
            $loop->remove($conn);
            fclose($conn);
        }
    };

    $loop->onReadable($conn, function ($conn) use ($loop, &$buffer, &$closing, $checkClose) {
        $data = fread($conn, 1024);

        if (!$data) {
            $closing = true;
            $loop->disableReads($conn);
            $checkClose();
            return;
        }

        $buffer .= $data;

        $loop->enableWrites($conn);
    });

    $loop->onWritable($conn, function ($conn) use ($loop, &$buffer, &$closing, $checkClose) {
        $written = fwrite($conn, $buffer);
        $buffer = (string) substr($buffer, $written);

        if (strlen($buffer) === 0) {
            $loop->disableWrites($conn);
        }

        $checkClose();
    });
});

$loop->run();

// BUG: buffer can be any size
