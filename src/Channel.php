<?php

namespace App;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsConnection;
use SplObjectStorage;

class Channel implements MessageComponentInterface
{
    protected $clients;
    protected $name;

    public function __construct(string $name)
    {
        $this->clients = new SplObjectStorage();
        $this->name = $name;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "[{$this->name}] - New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo "[{$this->name}] - Connection {$from->resourceId} sending message \"{$msg}\" to {$numRecv} other connection(s)\n";

        /** @var WsConnection $client */
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "[{$this->name}] - Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "[{$this->name}] - An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
