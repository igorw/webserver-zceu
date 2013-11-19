<?php

$server = stream_socket_server('tcp://0.0.0.0:5000');

$read = [$server];
$write = [];

while (true) {
    $readable = $read ?: null;
    $writable = $write ?: null;
    $except = null;

    if (stream_select($readable, $writable, $except, 1)) {
        $readable = $readable ?: [];
        foreach ($readable as $stream) {
            if ($server === $stream) {
                $conn = stream_socket_accept($server, 0);
                stream_set_blocking($conn, 0);
                $read[] = $conn;
            } else {
                $data = fread($stream, 1024);

                if (!$data) {
                    if (false !== $index = array_search($stream, $read)) {
                        array_splice($read, $index);
                    }
                    if (false !== $index = array_search($stream, $write)) {
                        array_splice($write, $index);
                    }
                    fclose($stream);
                    continue;
                }

                echo "[$stream] $data";
            }
        }

        $writable = $writable ?: [];
        foreach ($writable as $stream) {
            // ...
        }
    }
}
