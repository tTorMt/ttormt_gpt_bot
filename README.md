# Telegram Bot to OpenAI API Simple Connector.

### What can it do?
1. **Converse with the OpenAI API through Telegram:** The bot allows users to interact with the OpenAI API directly via Telegram.
2. **Write Conversation History:** It maintains a log of all conversations for future reference.
3. **Send Text Messages and Photos with Captions:** Users can send both text messages and photos with captions to the bot.
4. **Customize Responses:** Users can change how the bot responds to them.

### Requirements.

1. **PHP 8+**
2. **MySql 8+**
3. **Web Server (Apache or Nginx).**
4. **Composer 1.10+**

### Dependencies
1. **Guzzle HTTP client** https://github.com/guzzle/guzzle
2. **Telegram Bot Api Wrapper** https://github.com/TelegramBot/Api
3. **OpenAI API Wrapper** https://github.com/openai-php/client

### Installation

1. **Copy or Git Pull the Project:** Place the project in your web server directory.
2. **Configure the Web Server:** Set the public_html directory as the public directory.
3. **Create the Database:** Use the utils/schema.sql file to create the database.
4. **Set Up API Keys and Database Credentials:** Write your API keys, database username, and password in the secret/secret.php file.
5. **Configure Database Connection:** Enter the database name and host in the config/conf.ini file.
6. **Set the Webhook:** Set hook.php as the webhook for the Telegram Bot API. Refer to the Telegram Bot API documentation for details.
7. **Authorize Users and Customize Behavior:** Add authorized user IDs and customize assistant behavior in the **user** table.