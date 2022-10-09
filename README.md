# RawUpdateBot

Returns JSON of Telegram Bot API updates

Flow: Telegram Bot API `Update` object -> `RawUpdateBot` running on PHP server -> beautified JSON to the Telegram chat

## Usage

This bot is available as [@RawUpdateBot](https://t.me/RawUpdateBot) on Telegram. 

To use it in a 1-to-1 private chat, start a chat with the bot and click the START button.

To use it in channel / group chat, simply add the bot to the chat as member. Please note as the hosted bot above has [privacy mode](https://core.telegram.org/bots/features#privacy-mode) set to on, it might be required to promote the bot as admin for all messages to be processed.

However, if you have any reasons and would like to run your own instance, you can follow instructions below.

## Setup

1. Change the values of `BOT_TOKEN` and `WEBHOOK_URL` in `RawUpdateBot.php`
2. Upload `RawUpdateBot.php` to a PHP server
3. Run `php RawUpdateBot.php` to set webhook on Telegram, or `php RawUpdateBot.php delete` to delete webhook. (Alternatively, you can manually make HTTP requests: `https://api.telegram.org/bot<BOT_TOKEN>/setWebhook?url=<WEBHOOK_URL>` for setting webhook and `https://api.telegram.org/bot<BOT_TOKEN>/deleteWebhook` for deleting webhook)

## Supported Update Types

- message
- edited_message
- channel_post
- edited_channel_post
- my_chat_member
- chat_member
- chat_join_request

## Credits

- [Hellobot](https://core.telegram.org/bots/samples/hellobot) from Telegram Bot API documentation