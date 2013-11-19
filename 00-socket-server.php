<?php

$server = stream_socket_server('tcp://0.0.0.0:5000');
stream_set_blocking($server, 0);

while (true) {
    $conn = stream_socket_accept($server, -1);
    stream_set_blocking($conn, 0);
    fwrite($conn, date(DATE_RFC822)."\n");
    fclose($conn);
}
