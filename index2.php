<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>
    <body>
        <?php
        $dsn = 'mysql:dbname=websocket_php;host=127.0.0.1';
        $user = 'root';
        $password = 'root';

        $pdo = new PDO($dsn, $user, $password);

        $messages = $pdo->query("SELECT * FROM messages WHERE channel = 'chat'")->fetchAll(PDO::FETCH_ASSOC);
        $messages2 = $pdo->query("SELECT * FROM messages WHERE channel = 'chat2'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <fieldset>
            <legend>Messages</legend>
            <ul id="messages">
                <?php foreach ($messages as $message): ?>
                    <li><?= htmlspecialchars($message['message']) ?></li>
                <?php endforeach; ?>
            </ul>
        </fieldset>
        <fieldset>
            <legend>Commentaire</legend>
            <textarea name="comment" id="comment" title="comment" style="display: block; width: 100%;"></textarea>
            <button onclick="sendMessage()" style="margin-top: 15px;">Envoyer</button>
        </fieldset>

        <hr>

        <fieldset>
            <legend>Messages</legend>
            <ul id="messages2">
                <?php foreach ($messages2 as $message): ?>
                    <li><?= htmlspecialchars($message['message']) ?></li>
                <?php endforeach; ?>
            </ul>
        </fieldset>
        <fieldset>
            <legend>Commentaire</legend>
            <textarea name="comment2" id="comment2" title="comment2" style="display: block; width: 100%;"></textarea>
            <button onclick="sendMessage2()" style="margin-top: 15px;">Envoyer</button>
        </fieldset>

        <script>
            let chat = new WebSocket('ws://localhost:8080/chat');
            chat.onopen = function (e) {
                console.log("Connection established!");
            };

            chat.onmessage = function (e) {
                appendMessage(e.data);
            };

            function sendMessage() {
                let commentElement = document.getElementById('comment');

                if (commentElement.value) {
                    chat.send(commentElement.value);
                    appendMessage(commentElement.value);

                    commentElement.value = '';
                }
            }

            function appendMessage(message) {
                console.log('appendMessage ' + message);
                let liElement = document.createElement('li');
                liElement.innerHTML = message;

                let messagesElement = document.getElementById('messages');
                messagesElement.appendChild(liElement);
            }
        </script>

        <script>
            let chat2 = new WebSocket('ws://localhost:8080/chat2');
            chat2.onopen = function (e) {
                console.log("Connection established!");
            };

            chat2.onmessage = function (e) {
                appendMessage2(e.data);
            };

            function sendMessage2() {
                let commentElement = document.getElementById('comment2');

                if (commentElement.value) {
                    chat2.send(commentElement.value);
                    appendMessage2(commentElement.value);

                    commentElement.value = '';
                }
            }

            function appendMessage2(message) {
                console.log('appendMessage2 ' + message);
                let liElement = document.createElement('li');
                liElement.innerHTML = message;

                let messagesElement = document.getElementById('messages2');
                messagesElement.appendChild(liElement);
            }
        </script>
    </body>
</html>