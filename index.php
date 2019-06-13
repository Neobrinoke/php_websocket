<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('mysql:dbname=websocket_php;host=127.0.0.1', 'root', 'root');

if (isset($_POST['user_id'])) {
    $_SESSION['user_id'] = $_POST['user_id'];
}

$loggedUser = null;
if (isset($_SESSION['user_id'])) {
    $loggedUser = $pdo->query("select * from users where id = '{$_SESSION['user_id']}'")->fetch(PDO::FETCH_OBJ);
}

if ($loggedUser) {
    $allUsers = $pdo->query("select * from users")->fetchAll(PDO::FETCH_OBJ);
    $users = $pdo->query("select * from users where name != '{$loggedUser->name}'")->fetchAll(PDO::FETCH_OBJ);
    for ($i = 0; $i < count($users); $i++) {
        $messages = $pdo->query("select * from messages where (`from` = '{$users[$i]->id}' and `to` = '{$loggedUser->id}') or (`from` = '{$loggedUser->id}' and `to` = '{$users[$i]->id}') order by sent_at asc")->fetchAll(PDO::FETCH_OBJ);
        for ($j = 0; $j < count($messages); $j++) {
            $messages[$j]->is_mine = intval($messages[$j]->from) === intval($loggedUser->id);
        }

        $users[$i]->messages = $messages;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <div class="container-fluid" style="margin-top: 50px;">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <span>[<?= $loggedUser->name ?>]</span>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                            <ul class="list-group">
                                <?php foreach ($users as $user): ?>
                                    <a href="#" onclick="event.preventDefault(); handleShowUserChat('<?= $user->id ?>')" id="show_user_<?= $user->id ?>" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action show_users">
                                        <?= $user->name ?>
                                        <span class="badge badge-success badge-pill" style="display: none;" id="show_user_badge_<?= $user->id ?>">0</span>
                                    </a>
                                <?php endforeach; ?>
                            </ul>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post">
                                <ul class="list-group">
                                    <?php foreach ($allUsers as $user): ?>
                                        <button type="submit" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action <?= $user->id == $loggedUser->id ? 'active' : '' ?>" name="user_id" value="<?= $user->id ?>"><?= $user->name ?></button>
                                    <?php endforeach; ?>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <?php foreach ($users as $user): ?>
                        <div class="card cards_user" id="card_user_<?= $user->id ?>" style="display: none;">
                            <div class="card-header">
                                <span>[<?= $user->name ?>]</span>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush mb-3" style="height: 150px; overflow: scroll;" id="messages_<?= $user->id ?>">
                                    <?php foreach ($user->messages as $message): ?>
                                        <li class="list-group-item <?= $message->is_mine ? 'text-right' : '' ?>">
                                            <p class="mb-1"><?= $message->message ?></p>
                                            <small class="text-muted"><?= $message->sent_at ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <div class="form-group">
                                    <label for="message_<?= $user->id ?>">Message</label>
                                    <textarea name="message_<?= $user->id ?>" id="message_<?= $user->id ?>" class="form-control"></textarea>
                                </div>
                                <button class="btn btn-primary btn-block" onclick="sendMessage()">Envoyer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script>
            function handleAppendMessage(element, message, isMine = false, date = null) {
                let defaultHtml = '<li class="list-group-item ' + (isMine ? 'text-right' : '') + '"><p class="mb-1">__MESSAGE__</p><small class="text-muted">__DATE__</small></li>'
                .replace('__MESSAGE__', message)
                .replace('__DATE__', date || (new Date()).toISOString());

                element.insertAdjacentHTML('beforeend', defaultHtml);
                element.scrollTop = element.scrollHeight;
            }

            function sendMessage() {
                if (currentChatUserId) {
                    let messageElement = document.getElementById('message_' + currentChatUserId);
                    if (messageElement.value) {
                        chat.send(JSON.stringify({
                            from: '<?= $loggedUser->id ?>',
                            to: currentChatUserId,
                            content: messageElement.value,
                        }));

                        handleAppendMessage(document.getElementById('messages_' + currentChatUserId), messageElement.value, true);

                        messageElement.value = '';
                        messageElement.focus();
                    }
                }
            }

            function handleShowUserChat(userId) {
                currentChatUserId = userId;

                $('.show_users').removeClass('active');
                $('#show_user_' + currentChatUserId).addClass('active');

                $('.cards_user').hide();
                $('#card_user_' + currentChatUserId).show();
                console.log(currentChatUserId);

                let badgeUserElement = document.getElementById('show_user_badge_' + currentChatUserId);
                badgeUserElement.innerText = '0';
                badgeUserElement.style.display = 'none';

                let messagesElement = document.getElementById('messages_' + currentChatUserId);
                messagesElement.scrollTop = messagesElement.scrollHeight;
            }

            let currentChatUserId = null;

            let chat = new WebSocket('ws://localhost:8080/pm');

            chat.onmessage = function (e) {
                let message = JSON.parse(e.data);
                console.log(message);

                if (parseInt(message.to) === parseInt('<?= $loggedUser->id ?>')) {
                    handleAppendMessage(document.getElementById('messages_' + message.from), message.content);

                    if (!currentChatUserId || parseInt(currentChatUserId) !== parseInt(message.from)) {
                        let badgeUserElement = document.getElementById('show_user_badge_' + message.from);
                        badgeUserElement.innerText = (parseInt(badgeUserElement.innerText) + 1).toString();
                        if (parseInt(badgeUserElement.innerText) > 0) {
                            badgeUserElement.style.display = '';
                        }
                    }
                }
            };

            chat.onopen = function (e) {
                console.log('Connection established!');
            };
        </script>
    </body>
</html>