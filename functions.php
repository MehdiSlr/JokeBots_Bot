<?php
/**
 * Configuration and functions for the Joke Bot.
 * This file includes essential bot functions and configurations required to interact with the Telegram API
 * and the JokeAPI for fetching jokes.
 */

// Load bot configuration and translation settings
require 'config.php';  // Contains bot token and other configuration settings
require 'translate.php';  // Handles text translations using Google Translate

// Define constants for the bot token and the Telegram API URL
define('BOT_TOKEN', $token);  // Bot token for authentication with Telegram API
define('TELEGRAM_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');  // Base URL for Telegram Bot API

/**
 * Check if the user selected a language and update the database.
 * This function processes callback data from the user to set the language preference and returns the updated callback data.
 * 
 * @param string $data The callback data received from the user (e.g., 'lang_en', 'lang_fa').
 * @param string $callback_id The ID of the callback query.
 * @return string The updated callback data to be used for further actions.
 */
function cCheck($data, $callback_id) {
    include 'config.php';  // Re-include config to access the database connection (avoid this if already included)
    $parts = explode("_", $data);  // Split the callback data into parts using the "_" delimiter
    if (count($parts) > 1 && $parts[0] == 'lang') {
        $lang = $parts[1];  // Extract the selected language from the callback data
        $uid = UID;  // Get the unique user ID from the constant defined in index.php
        $sql = "UPDATE `users` SET `lang` = '$lang' WHERE `uid` = '$uid'";  // SQL query to update the user's language preference
        $result = mysqli_query($conn, $sql);  // Execute the SQL query
        if ($lang != 'en') {
            // Send an alert to the user if the selected language is not English
            msg('answerCallbackQuery', array('callback_query_id' => $callback_id, 'text' => GoogleTranslate::translate('en', $lang, text('translate_error')), 'show_alert' => true));
        }
        $data = 'home';  // Set the data to 'home' after updating the language
        return $data;
    } else {
        return $data;  // Return the original data if it is not a language change
    }
}

/**
 * Send a message or other Telegram API request to the Telegram server.
 * 
 * @param string $method The Telegram API method to call (e.g., 'sendMessage', 'editMessageText').
 * @param array $params The parameters for the API request (e.g., chat ID, message text).
 * @return string The result of the cURL execution (JSON response from Telegram API).
 */
function msg($method, $params) {
    if (!$params) {
        $params = array();  // Ensure $params is an array
    }
    $params["method"] = $method;  // Set the method for the API request
    $ch = curl_init(TELEGRAM_URL);  // Initialize a cURL session
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  // Set connection timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // Set request timeout
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));  // Encode parameters as JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));  // Set the content type to JSON
    $result = curl_exec($ch);  // Execute the API request
    return $result;  // Return the result of the cURL execution
}

/**
 * Fetch a joke from the JokeAPI based on category and text.
 * 
 * @param string $cat The category of the joke (e.g., 'Any', 'Dark', 'Pun').
 * @param string $text The text to search within the joke.
 * @return array The decoded JSON response from the JokeAPI.
 */
function Joke($cat, $text) {
    $url = "https://v2.jokeapi.dev/joke/" . $cat . "?type=single&contains=" . $text;  // Construct the API URL
    $response = file_get_contents($url);  // Fetch the response from JokeAPI
    if ($response === false) {
        // Handle error if the request fails
        die('Failed to fetch data from the API.');
    }
    $data = json_decode($response, true);  // Decode the JSON response into a PHP array
    if ($data === null) {
        // Handle error if JSON decoding fails
        die('Failed to decode JSON response.');
    }
    return $data;  // Return the decoded data
}

/**
 * Format a joke message with its category for sending to the user.
 * 
 * @param string $cat The category of the joke.
 * @param string $text The text of the joke.
 * @return string The formatted joke message.
 */
function JokeMsg($cat, $text) {
    $msg = $text . '

🗂️ Category: ' . $cat . '
🤖 @JokeBots_Bot';  // Construct the message with the joke text and category
    return GoogleTranslate::translate('en', LANG, $msg);  // Translate the message to the user's language
}

/**
 * Retrieve predefined text messages for the bot.
 * 
 * @param string $msg The key for the message to retrieve (e.g., 'welcome', 'wait').
 * @return string The predefined message in the user's language.
 */
function text($msg) {
    // Define various text messages for the bot
    $welcome = 'Welcome to Joke Bot. 🤗
I can tell you some jokes. 😁
Just select the category and I will tell you a joke. 🗂️';
    $wait = 'Please Wait... ⏳';
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

    $language = 'Select your language. 🌍

<i>* remember that if you use a language other than English, the texts may not translate correctly.</i>';

    $translate_error = '⚠️ You have selected a language other than English. the texts may not translate correctly.';

    // Map message keys to predefined text
    if ($msg == 'welcome') { $msg = $welcome; }
    if ($msg == 'wait') { $msg = $wait; }
    if ($msg == 'random') { $msg = $random; }
    if ($msg == 'custom_cat') { $msg = $custom_cat; }
    if ($msg == 'custom_text') { $msg = $custom_text; }
    if ($msg == 'random_error') { $msg = $random_error; }
    if ($msg == 'custom_error') { $msg = $custom_error; }
    if ($msg == 'info') { $msg = $info; }
    if ($msg == 'language') { $msg = $language; }
    if ($msg == 'translate_error') { $msg = $translate_error; }

    return GoogleTranslate::translate('en', LANG, $msg);  // Translate the message to the user's language
}

/**
 * Generate the appropriate keyboard layout for the bot.
 * 
 * @param string $keyboard The type of keyboard to generate (e.g., 'home', 'cat', 'info', 'language', 'custom_text', 'remove').
 * @return array The keyboard layout for the Telegram bot.
 */
function keyboard($keyboard) {
    // Define various keyboard layouts for different actions
    $home = array(
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Random Joke | 🎲'), 'callback_data' => 'random'), 
            array('text' => GoogleTranslate::translate('en', LANG, 'Custom Joke | 📝'), 'callback_data' => 'custom')
        ),
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Language | 🌍'), 'callback_data' => 'language'),
            // Uncomment the line below if you want to add an info button on the home screen
            // array('text' => GoogleTranslate::translate('en', LANG, 'Info | ℹ️'), 'callback_data' => 'info')
        )
    );

    $cat = array(
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Any 🤷🏻'), 'callback_data' => 'Any'),
            array('text' => GoogleTranslate::translate('en', LANG, 'Dark 💀'), 'callback_data' => 'Dark'),
            array('text' => GoogleTranslate::translate('en', LANG, 'Pun 🤔'), 'callback_data' => 'Pun'),
            array('text' => GoogleTranslate::translate('en', LANG, 'Misc 😕'), 'callback_data' => 'Miscellaneous')
        ),
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Programming 🧑🏻‍💻'), 'callback_data' => 'Programming'),
            array('text' => GoogleTranslate::translate('en', LANG, 'Spooky 👻'), 'callback_data' => 'Spooky'),
            array('text' => GoogleTranslate::translate('en', LANG, 'Christmas 🎄'), 'callback_data' => 'Christmas'),
        ),
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Back | ⤵️'), 'callback_data' => 'home')
        )
    );

    $custom_text = array(
        array(GoogleTranslate::translate('en', LANG, 'Change Category | 🔄️')),
        array(GoogleTranslate::translate('en', LANG, 'Home | 🏠'))
    );

    $info = array(
        array(
            array('text' => GoogleTranslate::translate('en', LANG, 'Back | ⤵️'), 'callback_data' => 'home')
        )
    );

    $language = array(
        array(
            array('text' => 'English | 🇬🇧', 'callback_data' => 'lang_en'),
            array('text' => 'Persian | 🇮🇷', 'callback_data' => 'lang_fa'),
        ),
        array(
            array('text' => 'Russian | 🇷🇺', 'callback_data' => 'lang_ru'),
            array('text' => 'Arabic | 🇦🇪', 'callback_data' => 'lang_ar'),
        ),
        array(
            array('text' => 'Spanish | 🇪🇸', 'callback_data' => 'lang_es'),
            array('text' => 'Turkish | 🇹🇷', 'callback_data' => 'lang_tr'),
        ),
        array(
            array('text' => 'German | 🇩🇪', 'callback_data' => 'lang_de'),
            array('text' => 'Chinese | 🇨🇳', 'callback_data' => 'lang_zh'),
        )
    );

    // Determine which keyboard to use based on the provided type
    if ($keyboard == 'home') { $btns = $home; }
    if ($keyboard == 'cat') { $btns = $cat; }
    if ($keyboard == 'info') { $btns = $info; }
    if ($keyboard == 'language') { $btns = $language; }

    if ($keyboard == 'custom_text') {
        $keyboard = array(
            'resize_keyboard' => true,
            'keyboard' => $custom_text,
        );
    } elseif ($keyboard == 'remove') {
        $keyboard = array(
            'remove_keyboard' => true  // Remove the current keyboard
        );
    } else {
        $keyboard = array(
            'resize_keyboard' => true,
            'inline_keyboard' => $btns  // Use an inline keyboard layout
        );
    }

    return $keyboard;  // Return the constructed keyboard layout
}
?>
