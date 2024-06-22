<?php
/**
 * Configuration file for the bot.
 */
require 'config.php';

// Define constants for the bot token and Telegram API URL
define('BOT_TOKEN', $token);
define('TELEGRAM_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

/**
 * Send a message to the Telegram bot.
 *
 * @param string $method The Telegram API method to call.
 * @param array $params The parameters to send with the API call.
 * @return string The result of the cURL execution.
 */
function msg($method, $params) {
    if (!$params) {
        $params = array();
    }
    $params["method"] = $method;
    $ch = curl_init(TELEGRAM_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
    $result = curl_exec($ch);
    return $result;
}

/**
 * Fetch a joke from the JokeAPI.
 *
 * @param string $cat The category of the joke.
 * @param string $text The text to search within the joke.
 * @return array The decoded JSON response from the API.
 */
function Joke($cat, $text) {
    $url = "https://v2.jokeapi.dev/joke/" . $cat . "?type=single&contains=" . $text;
    $response = file_get_contents($url);
    if ($response === false) {
        // Handle error
        die('Failed to fetch data from the API.');
    }
    $data = json_decode($response, true);
    if ($data === null) {
        // Handle error
        die('Failed to decode JSON response.');
    }
    return $data;
}

/**
 * Format a joke message with its category.
 *
 * @param string $cat The category of the joke.
 * @param string $text The text of the joke.
 * @return string The formatted joke message.
 */
function JokeMsg($cat, $text) {
    $msg = $text . '

🗂️ Category: ' . $cat . '
🤖 @JokeBots_Bot';
    return $msg;
}

/**
 * Retrieve predefined text messages.
 *
 * @param string $msg The key for the message to retrieve.
 * @return string The predefined message.
 */
function text($msg) {
    $welcome = 'Welcome to Joke Bot. 🤗
I can tell you some jokes. 😁
Just select the category and I will tell you a joke. 🗂️';

    $random = 'Select the category and I will tell you a random joke. 🗂️👇🏼';
    $random_error = 'Sorry! No matching joke found. 😢
Try another category. 🗂️';
    $custom_cat = 'Select the category of the joke. 🗂️';
    $custom_text = 'Enter the word you want to tell you a joke about. ⌨️';
    $custom_error = 'Sorry! No matching joke found. 😢
Try another category or word. 🗂️⌨️';

    $info = 'ℹ️ Bot Information:
🧑🏻‍💻 Creator: @Meytttii
😼 GitHub: <a href="https://github.com/MehdiSlr"> Mehdi Salari </a>
🔁 API Site: https://v2.jokeapi.dev
#️⃣ Programming Language: PHP';

    if ($msg == 'welcome') { return $welcome; }
    if ($msg == 'random') { return $random; }
    if ($msg == 'custom_cat') { return $custom_cat; }
    if ($msg == 'custom_text') { return $custom_text; }
    if ($msg == 'random_error') { return $random_error; }
    if ($msg == 'custom_error') { return $custom_error; }
    if ($msg == 'info') { return $info; }
}

/**
 * Generate the appropriate keyboard layout.
 *
 * @param string $keyboard The type of keyboard to generate.
 * @return array The keyboard layout.
 */
function keyboard($keyboard) {
    $home = array(
        array(
            array('text' => 'Random Joke | 🎲', 'callback_data' => 'random'), 
            array('text' => 'Custom Joke | 📝', 'callback_data' => 'custom')
        ),
        array(
            array('text' => 'Info | 💡', 'callback_data' => 'info')
        )
    );
    $cat = array(
        array(
            array('text' => 'Any 🤷🏻', 'callback_data' => 'Any'),
            array('text' => 'Dark 💀', 'callback_data' => 'Dark'),
            array('text' => 'Pun 🤔', 'callback_data' => 'Pun'),
            array('text' => 'Misc 😕', 'callback_data' => 'Miscellaneous')
        ),
        array(
            array('text' => 'Programming 🧑🏻‍💻', 'callback_data' => 'Programming'),
            array('text' => 'Spooky 👻', 'callback_data' => 'Spooky'),
            array('text' => 'Christmas 🎄', 'callback_data' => 'Christmas'),
        ),
        array(
            array('text' => 'Back | ⤵️', 'callback_data' => 'home')
        )
    );
    $custom_text = array(
        array('Change Category | 🔄️'),
        array('Home | 🏠')
    );
    $info = array(
        array(
            array('text' => 'Back | ⤵️', 'callback_data' => 'home')
        )
    );

    if ($keyboard == 'home') { $btns = $home; }
    if ($keyboard == 'cat') { $btns = $cat; }
    if ($keyboard == 'info') { $btns = $info; }

    if ($keyboard == 'custom_text') {
        $keyboard = array(
            'resize_keyboard' => true,
            'keyboard' => $custom_text,
        );
    } elseif ($keyboard == 'remove') {
        $keyboard = array(
            'remove_keyboard' => true
        );
    } else {
        $keyboard = array(
            'resize_keyboard' => true,
            'inline_keyboard' => $btns
        );
    }

    return $keyboard;
}
?>
