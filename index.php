<?php

/**
 * Main script to handle Telegram bot updates.
 */

require 'functions.php';

// Get the incoming update content
$content = file_get_contents('php://input');
$update = json_decode($content, true);

// Extract user message details
$chat_id = $update['message']['chat']['id']; // User chat ID
$text = $update['message']['text']; // User message text
$message_id = $update['message']['message_id']; // Message ID
$user_id = $update['message']['chat']['username']; // User username
$user_name = $update['message']['chat']['first_name']; // User first name

// Extract callback query details
$callback_chat_id = $update['callback_query']['message']['chat']['id']; // Chat ID from callback
$callback_data = $update['callback_query']['data']; // Callback data
$callback_message_id = $update['callback_query']['message']['message_id']; // Message ID from callback
$callback_message = $update['callback_query']['message']['text']; // Message text from callback
$bot_id = $update['callback_query']['from']['id']; // Bot ID from callback

// Handle user messages
switch($text) { 
    case '/start': // If user starts the bot or clicks on the home button
    case 'Home | 🏠':
        $sql = "SELECT * FROM `joke_users` WHERE `uid` = '$chat_id'"; // Check if user exists
        $result = mysqli_query($conn, $sql);

        // Check for database connection error
        if (!$result) {
            $db_error = mysqli_error($conn);
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => $db_error));
            die();
        }

        // If user exists, update user data
        if (mysqli_num_rows($result) > 0) {
            $sql = "UPDATE `joke_users` SET `name` = '$user_name', `user` = '$user_id', `type` = NULL, `cat` = NULL WHERE `uid` = '$chat_id'";
            $uresult = mysqli_query($conn, $sql);
        } else {
            // If user does not exist, create new user data
            $sql = "INSERT INTO `joke_users` (`uid`, `name`, `user`, `type`, `cat`) VALUES ('$chat_id', '$user_name', '$user_id', NULL, NULL)";
            $iresult = mysqli_query($conn, $sql);
        }

        // Send welcome message
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => 'Please Wait... ⏳', 'reply_markup' => keyboard('remove')));
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('welcome'), 'reply_markup' => keyboard('home')));
        break;

    case 'Change Category | 🔄️':
        // Update user category preference to custom
        $sql = "UPDATE `joke_users` SET `type` = 'custom', `cat` = NULL WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => 'Please Wait... ⏳', 'reply_markup' => keyboard('remove')));
        msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('custom_cat'), 'reply_markup' => keyboard('cat')));
        break;

    default:
        // Handle default user message
        $sql = "SELECT * FROM `joke_users` WHERE `uid` = '$chat_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['type'] == 'custom') {
            // If user has custom type, fetch a joke
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
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => text('custom_text')));
        }
        break;
}

// Handle callback queries
switch ($callback_data) {
    case 'home':
        // Handle 'home' callback
        $sql = "UPDATE `joke_users` SET `type` = NULL WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('welcome'), 'reply_markup' => keyboard('home')));
        break;

    case 'random':
        // Handle 'random' callback
        $sql = "UPDATE `joke_users` SET `type` = 'random' WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            $db_error = mysqli_error($conn);
            msg('sendMessage', array('chat_id' => $chat_id, 'text' => $db_error));
            die();
        }
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('random'), 'reply_markup' => keyboard('cat')));
        break;

    case 'custom':
        // Handle 'custom' callback
        $sql = "UPDATE `joke_users` SET `type` = 'custom' WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        msg('editMessageText', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id, 'text' => text('custom_cat'), 'reply_markup' => keyboard('cat')));
        break;

    case 'info':
        // Handle 'info' callback
        msg('editMessageText', array(
            'chat_id' => $callback_chat_id,
            'message_id' => $callback_message_id,
            'text' => text('info'),
            'link_preview_options' => ['url' => 'https://t.me/Meytttii'],
            'reply_markup' => keyboard('info'),
            'parse_mode' => 'HTML'
        ));
        break;

    default:
        // Handle default callback
        $sql = "SELECT * FROM `joke_users` WHERE `uid` = '$callback_chat_id'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['type'] == 'random') {
            // If user type is random, fetch a random joke
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
            msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => text('random'), 'reply_markup' => keyboard('cat')));
        } elseif ($row['type'] == 'custom') {
            // If user type is custom, update category
            $sql = "UPDATE `joke_users` SET `cat` = '$callback_data' WHERE `uid` = '$callback_chat_id'";
            $result = mysqli_query($conn, $sql);
            msg('deleteMessage', array('chat_id' => $callback_chat_id, 'message_id' => $callback_message_id));
            msg('sendMessage', array('chat_id' => $callback_chat_id, 'text' => text('custom_text'), 'reply_markup' => keyboard('custom_text')));
        }
        break;
}
?>