<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

if (!isset($_SESSION['admin_id']) || !is_numeric($_SESSION['admin_id'])) {
    die(json_encode(["status" => "error", "message" => "Unauthorized, invalid admin_id"]));
}

$admin_id = (int) $_SESSION['admin_id'];

$profile_pics_result = $conn->query("SELECT profile_pictures.file_path, users.id FROM users INNER JOIN profile_pictures ON users.id = profile_pictures.user_id");

$profile_pics = [];
if ($profile_pics_result && $profile_pics_result->num_rows > 0) {
    while ($row = $profile_pics_result->fetch_assoc()) {
        $profile_pics[$row['id']] = $row['file_path'];
    }
}

$usersQuery = $conn->query("SELECT id, firstname, lastname, userID FROM users");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'sendMessage' && isset($_POST['receiver_id'], $_POST['receiver_type'], $_POST['message'])) {
        $receiver_id = (int) $_POST['receiver_id'];
        $receiver_type = $_POST['receiver_type'];
        $message = trim($_POST['message']);

        if (empty($message)) {
            echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
            exit();
        }

        if (!in_array($receiver_type, ['user', 'admin'])) {
            echo json_encode(["status" => "error", "message" => "Invalid receiver type"]);
            exit();
        }

        $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $checkUser->bind_param("i", $receiver_id);
        $checkUser->execute();
        $checkUser->store_result();

        if ($checkUser->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Receiver does not exist"]);
            exit();
        }
        $checkUser->close();

        $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, 'admin', ?, ?, ?)");
        $stmt->bind_param("iiss", $admin_id, $receiver_id, $receiver_type, $message);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        exit();
    }

    if ($_POST['action'] === 'fetchMessages' && isset($_POST['receiver_id'], $_POST['receiver_type'])) {
        $receiver_id = (int) $_POST['receiver_id'];
        $receiver_type = $_POST['receiver_type'];
        $last_id = isset($_POST['last_id']) ? (int) $_POST['last_id'] : 0;

        if (!in_array($receiver_type, ['user', 'admin'])) {
            echo json_encode(["status" => "error", "message" => "Invalid receiver type"]);
            exit();
        }

        $stmt = $conn->prepare("SELECT chat.id, chat.sender_id, chat.sender_type, chat.message, chat.sent_at, users.firstname, users.lastname FROM chat LEFT JOIN users ON chat.sender_id = users.id WHERE chat.id > ? AND ((chat.sender_id = ? AND chat.sender_type = 'admin' AND chat.receiver_id = ? AND chat.receiver_type = ?) OR (chat.sender_id = ? AND chat.sender_type = ? AND chat.receiver_id = ? AND chat.receiver_type = 'admin')) ORDER BY chat.id ASC");
        $stmt->bind_param("iiisisi", $last_id, $admin_id, $receiver_id, $receiver_type, $receiver_id, $receiver_type, $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        echo json_encode($messages);
        exit();
    }

    if ($_POST['action'] === 'checkUnread') {
        $result = $conn->query("SELECT sender_id, COUNT(*) as unread_count FROM chat WHERE receiver_id = $admin_id AND receiver_type = 'admin' AND seen = 0 GROUP BY sender_id");
        $unread = [];
        while ($row = $result->fetch_assoc()) {
            $unread[$row['sender_id']] = $row['unread_count'];
        }
        echo json_encode($unread);
        exit();
    }

    if ($_POST['action'] === 'markSeen' && isset($_POST['sender_id'])) {
        $sender_id = (int) $_POST['sender_id'];
        $stmt = $conn->prepare("UPDATE chat SET seen = 1 WHERE sender_id = ? AND receiver_id = ? AND receiver_type = 'admin'");
        $stmt->bind_param("ii", $sender_id, $admin_id);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        exit();
    }
}
?>

<!-- HTML + JS + CSS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="chat-container">
    <div class="user-list">
        <h4 class="p-2">Users</h4>
        <ul class="list-group">
            <?php foreach ($users as $user): ?>
                <li class="list-group-item user-item" data-id="<?= $user['id'] ?>">
                    <?php
                    $user_id = $user['id'];
                    $profile_path = isset($profile_pics[$user_id])
                        ? htmlspecialchars("../".$profile_pics[$user_id])
                        : '../Assets/Profile/default.png'; // fallback image
                    ?>
                    <img src="<?= $profile_path ?>" alt="<?= htmlspecialchars($user['firstname']) ?>" class="user-avatar">

                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                    <span class="badge bg-danger float-end d-none" id="notif-<?= $user['id'] ?>">0</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat-box-container">
        <div id="chat-box" class="chat-box border p-3">
            <div class="loading-indicator">Loading...</div>
        </div>
        <input type="hidden" id="receiver_id" value="">
        <div class="chat-input">
            <textarea id="message" class="form-control" placeholder="Type your message..."></textarea>
            <button id="send-btn" class="btn btn-primary" disabled>Send</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const admin_id = <?= $admin_id ?>;
        let lastMessageId = 0;
        let receiver_id = null;
        let lastDateLabel = null;

        $('#message').on('input', function () {
            $('#send-btn').prop('disabled', $(this).val().trim() === '');
        });

        $(document).on('click', '.user-item', function () {
            $('.user-item').removeClass('selected');
            $(this).addClass('selected');
            receiver_id = $(this).data('id');
            lastMessageId = 0;
            lastDateLabel = null;
            $('#chat-box').empty().append("<div class='loading-indicator'>Loading...</div>");
            loadMessages(true);


        });

        $('#send-btn').click(sendMessage);
        $('#message').keypress(function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            const message = $('#message').val().trim();
            if (!receiver_id || message === '') return;
            $.post('./chat.php', {
                action: 'markSeen',
                sender_id: receiver_id
            });
            $.post('./chat.php', {
                action: 'sendMessage',
                receiver_id: receiver_id,
                receiver_type: 'user',
                message: message
            }, function (resp) {
                if (resp.status === 'success') {
                    $('#message').val('');
                    $('#send-btn').prop('disabled', true);
                    loadMessages(true);
                } else {
                    alert('❌ Error: ' + resp.message);
                }
            }, 'json').fail(function () {
                alert('❌ Failed to send message. Please try again.');
            });
        }

        function loadMessages(forceScroll = false) {
            if (!receiver_id) return;
            const box = $('#chat-box');
            const wasAtBottom = box[0].scrollHeight - box.scrollTop() <= box.outerHeight() + 50;
            $('.loading-indicator').show();

            $.post('./chat.php', {
                action: 'fetchMessages',
                receiver_id: receiver_id,
                receiver_type: 'user',
                last_id: lastMessageId
            }, function (data) {
                $('.loading-indicator').hide();
                data.forEach(msg => {
                    if (msg.id > lastMessageId) {
                        const msgDate = new Date(msg.sent_at).toDateString();
                        if (msgDate !== lastDateLabel) {
                            const label = formatDateLabel(msg.sent_at);
                            box.append(`<div class="date-divider">${label}</div>`);
                            lastDateLabel = msgDate;
                        }

                        const isAdmin = msg.sender_type === 'admin';
                        const senderName = isAdmin ? 'You' : `${msg.firstname || ''} ${msg.lastname || ''}`.trim();
                        const cls = isAdmin ? 'admin' : 'user';
                        const bg = isAdmin ? '#007bff' : '#e1e1e1';
                        const color = isAdmin ? '#fff' : '#000';

                        const html = `
                        <div class="chat-message ${cls}" style="background:${bg};color:${color}">
                            <div class="sender-name"><strong>${senderName}</strong></div>
                            <div>${msg.message}</div>
                            <div class="timestamp" title="${new Date(msg.sent_at).toLocaleString()}">
                                ${new Date(msg.sent_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true })}
                            </div>
                        </div>`;
                        box.append(html);
                        lastMessageId = msg.id;
                    }
                });
                if (wasAtBottom || forceScroll) {
                    box.scrollTop(box[0].scrollHeight);
                }
            }, 'json').fail(function () {
                $('.loading-indicator').hide();
                console.warn('⚠️ Failed to load messages.');
            });
        }

        setInterval(loadMessages, 3000);

        function formatDateLabel(dateStr) {
            const date = new Date(dateStr);
            const today = new Date();
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);

            if (date.toDateString() === today.toDateString()) return "Today";
            if (date.toDateString() === yesterday.toDateString()) return "Yesterday";

            return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        }
        function checkUnread() {
            $.post('./chat.php', { action: 'checkUnread' }, function (data) {
                $('.badge').addClass('d-none'); // hide all badges first

                Object.entries(data).forEach(([userId, count]) => {
                    const badge = $('#notif-' + userId);
                    badge.text(count);
                    badge.removeClass('d-none');
                });
            }, 'json');
        }

        setInterval(checkUnread, 3000); // every 3 seconds
        checkUnread(); // initial check

    });
</script>

<style>
    .chat-container {
        display: flex;
        height: 80vh;
        border: 1px solid #ddd;
        background-color: #f5f5f5;
    }

    .date-divider {
        text-align: center;
        margin: 15px 0;
        font-size: 12px;
        color: #888;
        font-weight: bold;
    }

    .user-list {
        width: 25%;
        background: #fff;
        border-right: 1px solid #ddd;
        padding: 10px;
        overflow-y: auto;
        box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 10px;
        cursor: pointer;
    }

    .user-item:hover {
        background-color: #f1f1f1;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .user-item.selected {
        background-color: #e9ecef;
        font-weight: 500;
    }

    .chat-box-container {
        width: 75%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .chat-box {
        flex-grow: 1;
        overflow-y: auto;
        padding: 15px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        height: 400px;
        position: relative;
    }

    .chat-message {
        max-width: 70%;
        padding: 12px;
        margin: 8px 0;
        border-radius: 15px;
        font-size: 14px;
        word-wrap: break-word;
    }

    .chat-message.admin {
        align-self: flex-end;
        border-radius: 15px 15px 0 15px;
    }

    .chat-message.user {
        align-self: flex-start;
        border-radius: 15px 15px 15px 0;
    }

    .sender-name {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .timestamp {
        font-size: 10px;
        opacity: 0.7;
        text-align: right;
        display: block;
        margin-top: 5px;
    }

    .chat-input {
        display: flex;
        padding: 10px;
        border-top: 1px solid #ddd;
        background-color: #fff;
    }

    .chat-input textarea {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 25px;
        resize: none;
        height: 50px;
        font-size: 14px;
    }

    .chat-input button {
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 25px;
        margin-left: 10px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    .chat-input button:hover {
        background: #0056b3;
    }

    .chat-input button:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .loading-indicator {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        color: #007bff;
        font-weight: bold;
    }

    .chat-box::-webkit-scrollbar {
        width: 8px;
    }

    .chat-box::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .chat-box::-webkit-scrollbar-thumb:hover {
        background: #007bff;
    }
</style>