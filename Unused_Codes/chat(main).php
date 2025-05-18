<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = 1; // Fixed Admin ID

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // ✅ Send Message (User → Admin)
    if ($_POST['action'] === 'sendMessage' && isset($_POST['message'])) {
        $message = trim($_POST['message']);

        if (!empty($message)) {
            $stmt = $conn->prepare("INSERT INTO chat (sender_id, sender_type, receiver_id, receiver_type, message) 
                                    VALUES (?, 'user', ?, 'admin', ?)");
            $stmt->bind_param("iis", $user_id, $admin_id, $message);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        exit();
    }

    // ✅ Fetch Messages (User ↔ Admin)
    if ($_POST['action'] === 'fetchMessages' && isset($_POST['last_msg_id'])) {
        $last_msg_id = (int) $_POST['last_msg_id'];
        $stmt = $conn->prepare("SELECT id, sender_id, sender_type, message, sent_at 
        FROM chat 
        WHERE id > ? 
        AND (
            (sender_id = ? AND receiver_id = ?) 
            OR 
            (sender_id = ? AND receiver_id = ?)
        ) 
        ORDER BY id ASC");
$stmt->bind_param("iiiii", $last_msg_id, $user_id, $admin_id, $admin_id, $user_id);
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












<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Admin</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            
            margin: 0;
            background: #f4f4f4;
        }
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 90vh;
            max-width: 100%;
            margin: auto;
            border: 1px solid #ddd;
            background: white;
        }
        .chat-header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .chat-box {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
            background: #f9f9f9;
        }
        .chat-message {
            max-width: 70%;
            padding: 10px 14px;
            margin: 6px 10px;
            border-radius: 10px;
            word-wrap: break-word;
            font-size: 15px;
            position: relative;
        }
        .message-right {
            align-self: flex-end;
            background: #007bff;
            color: white;
            border-radius: 15px 15px 0 15px;
        }
        .message-left {
            align-self: flex-start;
            background: #e1e1e1;
            color: black;
            border-radius: 15px 15px 15px 0;
        }
        .timestamp {
            display: block;
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            background: white;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 16px;
            outline: none;
        }
        .chat-input button {
            padding: 12px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            margin-left: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .chat-input button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <i class="fa fa-comments"></i> Chat with Admin
    </div>
    <div class="chat-box" id="chat-box"></div>
    <div class="chat-input">
        <input type="text" id="message-input" placeholder="Type a message..." oninput="toggleSendButton()" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
        <button id="send-button" disabled><i class="fa fa-paper-plane"></i></button>
    </div>
</div>


    
    <!-- Place this inside your chat.php, at the bottom of the page, just before </body> -->
<script>
$(document).ready(function() {
    let adminId = 1;  // Admin ID
    let lastMessageId = 0;  // Last message ID
    let displayedMessageIds = new Set();  // Set to track displayed message IDs
    let chatInterval = null;  // Interval for auto-refreshing messages
    let chatBox = $("#chat-box");
    // Function to enable/disable the send button based on input
    function toggleSendButton() {
        $("#send-button").prop("disabled", $("#message-input").val().trim() === "");
    }

    // Function to load chat messages
    function loadMessages() {
    $.post("Tabs/chat.php", { 
        action: "fetchMessages", 
        last_msg_id: lastMessageId  
    }, function(data) {
        

        // Check if the chat box exists and the current tab is Chat
        if (chatBox.length > 0) {
            let isAtBottom = chatBox[0].scrollHeight - chatBox.scrollTop() <= chatBox.outerHeight() + 50;

            data.forEach(msg => {
                if (!displayedMessageIds.has(msg.id)) {
                    let isUser = (msg.sender_type === "user");
                    let alignment = isUser ? "message-right" : "message-left";
                    let senderName = isUser ? "You" : "Admin";
                    let bgColor = isUser ? "#007bff" : "#e1e1e1";
                    let textColor = isUser ? "white" : "black";

                    let messageDiv = `
                        <div class="chat-message ${alignment}" style="background: ${bgColor}; color: ${textColor};">
                            <div class="sender-name"><strong>${senderName}</strong></div>
                            <span>${msg.message}</span>
                            <small class="timestamp">${new Date(msg.sent_at).toLocaleTimeString()}</small>
                        </div>`;

                    chatBox.append(messageDiv);
                    displayedMessageIds.add(msg.id);
                    lastMessageId = msg.id;
                }
            });

            // Scroll to bottom if needed
            if (isAtBottom) {
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }
        }
    }, "json");
}

    // Function to send a new message
    async function sendMessage() {
        let messageInput = $("#message-input");
        let message = messageInput.val().trim();

        if (message !== "") {
            try {
                let response = await fetch("./Tabs/chat.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=sendMessage&message=${encodeURIComponent(message)}&receiver_id=${adminId}`
                });

                let result = await response.json();
                if (result.status === "success") {
                    messageInput.val(""); // Clear message input field
                    //toggleSendButton(); // Disable the send button until there's new input

                    // let chatBox = $("#chat-box");
                    // let messageDiv = `<div class="chat-message message-right" style="background: #007bff; color: white;">
                    //                     <strong>You</strong><br>
                    //                     <span>${message}</span>
                    //                     <small class="timestamp">${new Date().toLocaleTimeString()}</small>
                    //                   </div>`;
                    // chatBox.append(messageDiv); // Display sent message
                    // chatBox.scrollTop(chatBox[0].scrollHeight); // Scroll to the bottom
                    // loadMessages(); // Load new messages
                }
            } catch (error) {
                console.error("Error sending message:", error);
            }
        }
    }

    // Event Listeners for Input and Send Button
    $("#send-button").on("click", sendMessage);
    $("#message-input").on("input", toggleSendButton);
    $("#message-input").on("keypress", function (event) {
        if (event.key === "Enter") sendMessage();
    });

    // Handle tab switching and ensure chat script runs only once
    $(".chat-tab").click(function() {
        // Prevent re-initialization by checking if it's already initialized
        if (!chatInterval) {
            chatInterval = setInterval(loadMessages, 3000); // Start auto-refresh every 3 seconds
            loadMessages(); // Load messages immediately when tab is opened
        }
    });

    // Stop the interval and clear chat when switching to another tab
    $(".other-tab").click(function() {
        if (chatInterval) {
            clearInterval(chatInterval); // Clear the interval
            chatInterval = null; // Reset chat interval variable
        }
        $("#chat-box").html(""); // Optionally clear the chat content
    });

    // Auto-refresh messages every 3 seconds while chat tab is active
    setInterval(loadMessages, 3000); // Default auto-refresh on page load in case chat is already active
});
</script>


</script>

</body>
</html>
