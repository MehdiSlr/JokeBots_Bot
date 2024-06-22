# JokeBot

JokeBot is a simple Telegram bot that sends random jokes or jokes containing specific words provided by the user. The bot utilizes the [JokeAPI](https://v2.jokeapi.dev/) to fetch jokes and sends them to the user via the Telegram API.

## Features

- Send random jokes from various categories.
- Send jokes containing specific words provided by the user.
- Provides bot information and usage instructions.

## Requirements

- PHP 7.4 or higher
- cURL extension for PHP
- A Telegram bot token

## Installation

1. Clone the repository to your local machine:

```sh
git clone https://github.com/MehdiSlr/JokeBots_Bot.git
cd JokeBots_Bot
```

2. Install the required PHP dependencies (if any).

3. Create a `config.php` file in the project root directory and add your Telegram bot token and database details:

```php
<?php
$token = 'YOUR_TELEGRAM_BOT_TOKEN';
$conn = mysqli_connect('YOUR_DATABASE_HOST', 'YOUR_DATABASE_USERNAME', 'YOUR_DATABASE_PASSWORD', 'YOUR_DATABASE_NAME');
?>
```

4. Set the bot webhook with enter the following url in your browser address bar:
```sh
https://api.telegram.org/bot<YOUR_TELEGRAM_BOT_TOKEN>/setWebhook?url=<YOUR_HOST_URL>/index.php
```

5. Start the bot in Telegram and enjoy it.

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

## Bot Commands

The bot responds to various commands and callback queries:

- `/start` - Displays the welcome message and main keyboard.
- `random` - Sends a random joke.
- `custom` - Sends a joke containing the search term provided by the user.
- `info` - Displays the bot and API information.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

- [JokeAPI](https://github.com/official-joke-api) for providing the jokes.
- [Telegram](https://core.telegram.org/bots/api) for the Telegram bot API.

## Support

If you have any questions or feedback, please [open an issue](https://github.com/MehdiSlr/jokebot/issues/new) on GitHub.

## Author

Created by [Mehdi Salari](https://github.com/MehdiSlr) [@Meytttii](https://github.com/Meytttii).