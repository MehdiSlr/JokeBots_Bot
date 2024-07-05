<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JokeBot</title>
</head>
<body>
    <h1>JokeBots_Bot</h1>
    <a href="https://t.me/JokeBots_Bot">
        <button style="cursor: pointer;">Open Bot</button>
    </a>
</body>
</html>

<?php

/**
 * index.php
 * 
 * Main script to handle updates from the Telegram bot.
 * Processes incoming messages and callback queries, interacts with the database,
 * and sends responses based on user interactions.
 */

require 'functions.php';  // Include external functions for bot operations

// Get the raw input from the incoming update
$content = file_get_contents('php://input');

// Decode the JSON content into a PHP associative array
$update = json_decode($content, true);

// Extract user message details from the update
$update_id = $update['update_id'];  // ID of the update
$chat_id = $update['message']['chat']['id'];  // User chat ID
$text = $update['message']['text'];  // Text of the user’s message
$message_id = $update['message']['message_id'];  // ID of the message
$user_id = $update['message']['chat']['username'];  // Username of the user
$user_name = $update['message']['chat']['first_name'];  // First name of the user

// Extract callback query details if available
$callback_chat_id = $update['callback_query']['message']['chat']['id'] ?? null;  // Chat ID from callback
$callback_data = $update['callback_query']['data'] ?? null;  // Data from the callback
$callback_id = $update['callback_query']['id'] ?? null;  // ID of the callback
$callback_message = $update['callback_query']['message']['text'] ?? null;  // Message text from callback
$callback_message_id = $update['callback_query']['message']['message_id'] ?? null;  // Message ID from callback
$bot_id = $update['callback_query']['from']['id'] ?? null;  // Bot ID from callback

// Optionally log the incoming update (commented out for now)
// file_put_contents('update_log.json', print_r($content, true));

// Insert the raw update content into the `update_log` table for debugging or logging purposes
$sql = "INSERT INTO `update_log` (`id`, `json`) VALUES ('$update_id', '$content')";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = mysqli_error($conn);
    msg('sendMessage', array('chat_id' => $chat_id, 'text' => $error));
}

// Check if there is any callback data, and set UID based on whether it's a callback query or a regular message
if ($callback_data != null) {
    define('UID', $callback_chat_id);  // Set UID for callback queries
    $callback_data = cCheck($callback_data, $callback_id);  // Check and process the callback data
} else {
    define('UID', $chat_id);  // Set UID for regular messages
}

// Get the user's language preference from the database
$uid = UID;
$sql = "SELECT lang FROM `users` WHERE `uid` = '$uid'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$lang = $row['lang'] ?? 'en';  // Default to 'en' if no language preference is set

// Define a constant for the user's language
define('LANG', $lang);

// Translate the user message to the user's language
$text = GoogleTranslate::translate(LANG, 'en', $text);

// Adjust the text if it contains certain keywords
if (strpos($text, '🏠') !== false) {
    $text = 'Home | 🏠';
}
if (strpos($text, '🔄️') !== false) {
    $text = 'Change Category | 🔄️';
}

// Handle user messages based on the text content
switch ($text) {
    case '/start':  // Command for starting the bot or the home button
    case 'Home | 🏠':
        // Check if the user exists in the database
        $sql = "SELECT * FROM `users` WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);

        // Handle database connection errors
        if (!$result) {
            $db_error = mysqli_error($conn);
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => $db_error));
            die();
        }

        // If user exists, update their data; if not, create a new user record
        if (mysqli_num_rows($result) > 0) {
            $sql = "UPDATE `users` SET `name` = '$user_name', `user` = '$user_id', `type` = NULL, `cat` = NULL WHERE `uid` = '$chat_id'";
            $result = mysqli_query($conn, $sql);
        } else {
            $sql = "INSERT INTO `users` (`uid`, `name`, `user`, `type`, `cat`, `lang`) VALUES ('$chat_id', '$user_name', '$user_id', NULL, NULL, NULL)";
            $result = mysqli_query($conn, $sql);
        }

        // Check if the user has set their language preference
        $sql = "SELECT lang FROM `users` WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['lang'] == NULL) {
            // Prompt the user to select a language if it's not set
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('language'), 'reply_markup' => keyboard('language'), 'parse_mode' => 'HTML'));
        } else {
            // Send welcome messages if the language is set
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('wait'), 'reply_markup' => keyboard('remove')));
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('welcome'), 'reply_markup' => keyboard('home')));
        }
        break;

    case 'Change Category | 🔄️':
        // Set user preference to 'custom' for category selection
        $sql = "UPDATE `users` SET `type` = 'custom', `cat` = NULL WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('wait'), 'reply_markup' => keyboard('remove')));
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('custom_cat'), 'reply_markup' => keyboard('cat')));
        break;

    default:
        // Handle messages that are not commands or predefined options
        $sql = "SELECT * FROM `users` WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['type'] == 'custom') {
            // Fetch a joke based on the user's selected category
            $cat = $row['cat'];
            $joke = Joke($cat, $text);
            $error = $joke['error'];
            $jokeCat = $joke['category'];
            $jokeText = $joke['joke'];
            switch ($error) {
                case 'true':
                    msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('custom_error')));
                    break;
                default:
                    msg('sendMessage', array('chat_id' => $chat_id, 'text' => JokeMsg($jokeCat, $jokeText)));
                    break;
            }
            sleep(3);  // Wait for 3 seconds before sending the next message
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('custom_text')));
        }
        break;
}

// Handle callback queries based on the data
switch ($callback_data) {
    case 'home':
        // Reset the user’s type to NULL and send a welcome message
        $sql = "UPDATE `users` SET `type` = NULL WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('welcome'), 'reply_markup' => keyboard('home')));
        break;

    case 'random':
        // Set the user’s type to 'random' and prompt for category selection
        $sql = "UPDATE `users` SET `type` = 'random' WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            $db_error = mysqli_error($conn);
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => $db_error));
            die();
        }
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('random'), 'reply_markup' => keyboard('cat')));
        break;

    case 'custom':
        // Set the user’s type to 'custom' for category selection
        $sql = "UPDATE `users` SET `type` = 'custom' WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('custom_cat'), 'reply_markup' => keyboard('cat')));
        break;

    case 'info':
        // Provide information about the bot
        msg('editMessageText', array(
            'chat_id' => $callback_chat_id,
            'message_id' => $callback_message_id,
            'text' => text('info'),
            'link_preview_options' => ['url' => 'https://t.me/Meytttii'],
            'reply_markup' => keyboard('info'),
            'parse_mode' => 'HTML'
        ));
        break;

    case 'language':
        // Prompt the user to select a language
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('language'), 'reply_markup' => keyboard('language'), 'parse_mode' => 'HTML'));
        break;

    default:
        // Handle default callback query actions based on the user’s type
        $sql = "SELECT * FROM `users` WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['type'] == 'random') {
            // Fetch a random joke based on the selected category
            msg('deleteMessage', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id));
            $cat = $callback_data;
            $text = '';
            $joke = Joke($cat, $text);
            $error = $joke['error'];
            $jokeCat = $joke['category'];
            $jokeText = $joke['joke'];
            switch ($error) {
                case 'true':
                    msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => text('random_error')));
                    break;
                default:
                    msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => JokeMsg($jokeCat, $jokeText)));
                    break;
            }
            sleep(3);  // Wait for 3 seconds before sending the next message
            msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => text('random'), 'reply_markup' => keyboard('cat')));
        } elseif ($row['type'] == 'custom') {
            // Update the category based on the callback data
            $sql = "UPDATE `users` SET `cat` = '$callback_data' WHERE `uid` = '$callback_chat_id'";
            $result = mysqli_query($conn, $sql);
            msg('deleteMessage', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id));
            msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => text('custom_text'), 'reply_markup' => keyboard('custom_text')));
        }
        break;
}
?>
