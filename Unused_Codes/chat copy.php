<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id']) || !is_numeric($_SESSION['admin_id'])) {
    die(json_encode(["status" => "error", "message" => "Unauthorized, invalid admin_id"]));
}

$admin_id = (int)$_SESSION['admin_id'];

// Fetch users for chat selection
$usersQuery = $conn->query("SELECT id, firstname, lastname FROM users");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'sendMessage' && isset($_POST['receiver_id'], $_POST['receiver_type'], $_POST['message'])) {
        $receiver_id = (int) $_POST['receiver_id'];
        $receiver_type = $_POST['receiver_type'];
        $message = trim($_POST['message']);

        // Validate input
        if (empty($message)) {
            echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
            exit();
        }
        
        if (!in_array($receiver_type, ['user', 'admin'])) {
            echo json_encode(["status" => "error", "message" => "Invalid receiver type"]);
            exit();
        }

        // Ensure the receiver exists in the `users` table
        $checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $checkUser->bind_param("i", $receiver_id);
        $checkUser->execute();
        $checkUser->store_result();

        if ($checkUser->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Receiver does not exist"]);
            exit();
        }
        $checkUser->close();

        // Insert message into chat table
        $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_type, receiver_id, receiver_type, message) 
                                VALUES (?, 'admin', ?, ?, ?)");
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

        // Validate receiver type
        if (!in_array($receiver_type, ['user', 'admin'])) {
            echo json_encode(["status" => "error", "message" => "Invalid receiver type"]);
            exit();
        }

        // Updated query with JOIN to fetch sender's name from the users table
        $stmt = $conn->prepare("
            SELECT chat.id, chat.sender_id, chat.sender_type, chat.message, chat.sent_at, 
                   users.firstname, users.lastname
            FROM chat
            LEFT JOIN users ON chat.sender_id = users.id
            WHERE chat.id > ? 
              AND ((chat.sender_id = ? AND chat.sender_type = 'admin' AND chat.receiver_id = ? AND chat.receiver_type = ?) 
                OR (chat.sender_id = ? AND chat.sender_type = ? AND chat.receiver_id = ? AND chat.receiver_type = 'admin'))
            ORDER BY chat.id ASC
        ");
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
}
?>


<div class="chat-container">
    <div class="user-list">
        <h4 class="p-2">Users</h4>
        <ul class="list-group">
            <?php foreach ($users as $user): ?>
                <li class="list-group-item user-item" data-id="<?= $user['id'] ?>">
                    <img src="../Assets/Profiles/Lebrown.png" alt="<?= htmlspecialchars($user['firstname']) ?>" class="user-avatar">
                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat-box-container">
        <div id="chat-box" class="chat-box border p-3">
            <div class="loading-indicator">Loading...</div> <!-- Loading indicator -->
        </div>
        <input type="hidden" id="receiver_id" value="">
        <div class="chat-input">
            <textarea id="message" class="form-control" placeholder="Type your message..." oninput="toggleSendButton()"></textarea>
            <button id="send-btn" class="btn btn-primary" disabled>Send</button>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    let admin_id = <?= $admin_id ?>;
    let lastMessageId = 0;
    let receiver_id = null;

    function toggleSendButton() {
        $("#send-btn").prop("disabled", $("#message").val().trim() === "");
    }

    // Handle user selection for chat
    $(document).on("click", ".user-item", function() {
        $(".user-item").removeClass("selected");
        $(this).addClass("selected");

        receiver_id = $(this).data("id");
        $("#receiver_id").val(receiver_id);
        $("#chat-box").html("<div class='loading-indicator'>Loading...</div>");
        lastMessageId = 0;
        loadMessages();
    });

    // Send Message
    $(document).on("click", "#send-btn", sendMessage);

    $(document).on("keypress", "#message", function(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        let message = $("#message").val().trim();
        if (!receiver_id || message === "") return;

        $.post("Tabs/chat.php", { 
            action: "sendMessage", 
            receiver_id: receiver_id, 
            receiver_type: "user", 
            message: message 
        }, function(response) {
            if (response.status === "success") {
                $("#message").val("");
                toggleSendButton();
                loadMessages(true);
            } else {
                alert("❌ Error: " + response.message);
            }
        }, "json").fail(function() {
            alert("❌ Failed to send message. Please try again.");
        });
    }

    function loadMessages(forceScroll = false) {
        if (!receiver_id) return;

        $.post("Tabs/chat.php", { 
            action: "fetchMessages", 
            receiver_id: receiver_id, 
            receiver_type: "user", 
            last_id: lastMessageId 
        }, function(data) {
            let chatBox = $("#chat-box");
            if (chatBox.length === 0) return;

            let isAtBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 50;
            $(".loading-indicator").hide();

            data.forEach(msg => {
                if (msg.id > lastMessageId) {
                    let isAdmin = (msg.sender_type === "admin");
                    let senderName = isAdmin ? "You" : `${msg.firstname} ${msg.lastname}`;
                    let alignment = isAdmin ? "admin" : "user";
                    let bgColor = isAdmin ? "#007bff" : "#e1e1e1"; // Blue for admin, gray for user
                    let textColor = isAdmin ? "white" : "black";

                    let messageDiv = `
                        <div class="chat-message ${alignment}" style="background: ${bgColor}; color: ${textColor};">
                            <div class="sender-name"><strong>${senderName}</strong></div>
                            <span>${msg.message}</span>
                            <small class="timestamp">${new Date(msg.sent_at).toLocaleTimeString()}</small>
                        </div>`;

                    chatBox.append(messageDiv);
                    lastMessageId = msg.id;
                }
            });

            // Auto-scroll if at the bottom
            if (isAtBottom || forceScroll) {
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }
        }, "json").fail(function() {
            console.warn("⚠️ Failed to load messages.");
        });
    }

    // Auto-fetch new messages every 3 seconds
    setInterval(loadMessages, 3000);
});
</script>







<style>
.chat-container {
    display: flex;
    height: 90vh;
    border: 1px solid #ddd;
    background-color: #f5f5f5; /* Light background */
}

.user-list {
    width: 25%;
    background: #fff;
    border-right: 1px solid #ddd;
    padding: 10px;
    overflow-y: auto;
    box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
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
    position: relative;
    word-wrap: break-word;
    transition: background 0.3s ease;
}

.chat-message.admin {
    align-self: flex-end;
    background: #007bff;
    color: white;
    text-align: left;
    border-radius: 15px 15px 0 15px;
}

.chat-message.user {
    align-self: flex-start;
    background: #e1e1e1;
    color: black;
    text-align: left;
    border-radius: 15px 15px 15px 0;
}

.chat-message .sender-name {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
}

.chat-message .timestamp {
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

/* Scrollbar Styling */
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