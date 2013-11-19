<?php

class EventLoop
{
    public $read = [], $write = [];
    public $onReadable = [], $onWritable = [];

    function run()
    {
        while (true) {
            $this->tick();
        }
    }

    function tick()
    {
        $readable = $this->read ?: null;
        $writable = $this->write ?: null;
        $except = null;

        if (stream_select($readable, $writable, $except, 1)) {
            $readable = $readable ?: [];
            foreach ($readable as $stream) {
                $this->callReadListener($stream);
            }

            $writable = $writable ?: [];
            foreach ($writable as $stream) {
                $this->callWriteListener($stream);
            }
        }
    }

    function onReadable($stream, callable $listener)
    {
        $this->onReadable[(int) $stream] = $listener;
        $this->enableReads($stream);
    }

    function onWritable($stream, callable $listener)
    {
        $this->onWritable[(int) $stream] = $listener;
    }

    function enableReads($stream)
    {
        $this->read[] = $stream;
    }

    function disableReads($stream)
    {
        if (false !== $index = array_search($stream, $this->read)) {
            array_splice($this->read, $index);
        }
    }

    function enableWrites($stream)
    {
        $this->write[] = $stream;
    }

    function disableWrites($stream)
    {
        if (false !== $index = array_search($stream, $this->write)) {
            array_splice($this->write, $index);
        }
    }

    function callReadListener($stream)
    {
        if (isset($this->onReadable[(int) $stream])) {
            $listener = $this->onReadable[(int) $stream];
            $listener($stream);
        }
    }

    function callWriteListener($stream)
    {
        if (isset($this->onWritable[(int) $stream])) {
            $listener = $this->onWritable[(int) $stream];
            $listener($stream);
        }
    }

    function remove($stream)
    {
        $this->disableReads($stream);
        $this->disableWrites($stream);
        unset($this->onReadable[(int) $stream]);
        unset($this->onWritable[(int) $stream]);
    }
}
