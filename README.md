# JokeBot

JokeBot is a simple Telegram bot designed to send jokes to users based on various categories or specific words. The bot uses the [JokeAPI](https://v2.jokeapi.dev/) to fetch jokes and interacts with users through the Telegram API.

## Features

- Send random jokes from multiple categories.
- Send jokes containing specific words provided by the user.
- Language selection feature to support multilingual user interactions.
- Provides bot information and usage instructions.
- Logs interactions for debugging and improvement.

## Requirements

- PHP 7.4 or higher
- cURL extension for PHP
- A Telegram bot token
- MySQL or compatible database

## Installation

Follow these steps to set up the bot:

1. Clone the repository

Clone the repository to your local machine and navigate into the project directory:

```sh
git clone https://github.com/MehdiSlr/JokeBots_Bot.git
cd JokeBots_Bot
```

2. Install Dependencies

There are no specific PHP dependencies to install via Composer at the moment, but ensure you have the cURL extension enabled.

3. Configure `config.php`

Create a `config.php` file in the project root directory with your Telegram bot token and database details:

```php
<?php
$token = 'YOUR_TELEGRAM_BOT_TOKEN';
$conn = mysqli_connect('YOUR_DATABASE_HOST', 'YOUR_DATABASE_USERNAME', 'YOUR_DATABASE_PASSWORD', 'YOUR_DATABASE_NAME');
?>
```

4. Set the Bot Webhook

Set the webhook URL for the bot by entering the following URL in your browser:

```sh
https://api.telegram.org/bot<YOUR_TELEGRAM_BOT_TOKEN>/setWebhook?url=<YOUR_HOST_URL>/index.php
```
5. Update the Database Schema

Ensure that your database has the necessary tables for the bot. Here’s an example SQL schema to set up the `users` and `update_log` tables:

```sql
CREATE TABLE `users` (
    `uid` BIGINT PRIMARY KEY,
    `name` VARCHAR(255),
    `user` VARCHAR(255),
    `type` VARCHAR(255),
    `cat` VARCHAR(255),
    `lang` VARCHAR(5)
);

CREATE TABLE `update_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `json` TEXT
);
```

6. Start the Bot

Start a conversation with the bot on Telegram to initiate interaction.

## Usage

### Sending Messages

The `msg` function is used to send messages to the Telegram bot. It takes two parameters: the API method and the parameters for the method.

```php
msg('sendMessage', [
    'chat_id' => $chat_id,
    'text' => $message,
]);
```

### Fetching Jokes

The `Joke` function is used to fetch a joke from the JokeAPI based on a category and a search term.

```php
$joke = Joke('Programming', 'bug');
```

### Formatting Jokes

The `JokeMsg` function is used to format a joke message with its category.

```php
$formatted_joke = JokeMsg('Programming', 'Why do programmers prefer dark mode?');
```

### Predefined Text Messages

The `text` function is used to retrieve predefined text messages for the bot.

```php
$welcome_message = text('welcome');
```

### Keyboard Layout

The `keyboard` function is used to generate the appropriate keyboard layout for the bot.

```php
$keyboard = keyboard('home');
```

## Bot Commands and Callbacks

The bot responds to commands and callback queries:

- `/start` - Displays the welcome message and main keyboard.
- `Change Category | 🔄️` - Switches to custom joke mode.
- `random` - Sends a random joke.
- `custom` - Sends a joke containing the search term provided by the user.
- `language` - Allows users to select a language preference.
- `info` - Displays the bot and API information.
- Callback queries for joke categories (`Any`, `Dark`, `Pun`, `Miscellaneous`, `Programming`, `Spooky`, `Christmas`).

## Database Logging

The bot logs incoming updates to the `update_log` table for debugging purposes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

- [JokeAPI](https://github.com/official-joke-api) for providing the jokes.
- [Telegram](https://core.telegram.org/bots/api) for the Telegram bot API.

## Support

If you have any questions or feedback, please [open an issue](https://github.com/MehdiSlr/jokebot/issues/new) on GitHub.

## Author

Created by [Mehdi Salari](https://github.com/MehdiSlr) - Telegram [@Meytttii](https://t.me/Meytttii).