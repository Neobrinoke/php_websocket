<?php

namespace App;

use PDO;
use Ratchet\ConnectionInterface;

class PrivateMessage extends Channel
{
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $message = json_decode($msg);

        $pdo = new PDO('mysql:dbname=websocket_php;host=127.0.0.1', 'root', 'root');
        $statement = $pdo->prepare("INSERT INTO messages (`message`, `type`, `from`, `to`, `sent_at`) values (?, 'pm', ?, ?, NOW())");
        $statement->execute([
            $message->content,
            $message->from,
            $message->to,
        ]);

        parent::onMessage($from, $msg);
    }
}
