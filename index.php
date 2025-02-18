<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        .message {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .message img, .message video {
            max-width: 200px;
            display: block;
            margin-top: 5px;
        }
        form {
            display: flex;
            gap: 10px;
        }
        input[type="text"], input[type="file"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="messages" id="messages"></div>
    <form id="chatForm" method="POST" enctype="multipart/form-data">
        <input type="text" name="message" id="messageInput" placeholder="Type your message...">
        <input type="file" name="file" id="fileInput">
        <button type="submit">Send</button>
    </form>
</div>

<script>
    const username = prompt("Enter your username:");
    const messagesDiv = document.getElementById('messages');

    function fetchMessages() {
        fetch('cache.json')
            .then(response => response.json())
            .then(data => {
                messagesDiv.innerHTML = '';
                data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    messageDiv.innerHTML = `<strong>${message.username}:</strong> ${message.text}`;
                    if (message.file) {
                        if (message.file.type.startsWith('image/')) {
                            messageDiv.innerHTML += `<img src="files/${message.file.name}" alt="Image">`;
                        } else if (message.file.type.startsWith('video/')) {
                            messageDiv.innerHTML += `<video src="files/${message.file.name}" controls></video>`;
                        } else {
                            messageDiv.innerHTML += `<a href="files/${message.file.name}" download>${message.file.name}</a>`;
                        }
                    }
                    messagesDiv.appendChild(messageDiv);
                });
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            });
    }

    fetchMessages();
    setInterval(fetchMessages, 3000);
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? 'Anonymous';
    $message = $_POST['message'] ?? '';
    $messagesFile = 'cache.json';
    $uploadsDir = 'files/';

    // Ensure files/ and cache.json exist
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }
    if (!file_exists($messagesFile)) {
        file_put_contents($messagesFile, '[]');
    }

    // Handle file upload
    $fileData = null;
    if (!empty($_FILES['file']['name'])) {
        $fileName = basename($_FILES['file']['name']);
        $targetFilePath = $uploadsDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            $fileData = [
                'name' => $fileName,
                'type' => $_FILES['file']['type']
            ];
        }
    }

    // Load existing messages
    $messages = json_decode(file_get_contents($messagesFile), true);

    // Add new message
    $messages[] = [
        'username' => $username,
        'text' => $message,
        'file' => $fileData
    ];

    // Save messages to file
    file_put_contents($messagesFile, json_encode($messages));
}
?>

</body>
</html>