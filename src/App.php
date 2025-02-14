<?php

declare(strict_types=1);

namespace TtormtGptBot;

/** 
 * Main app class. Starts the bot.
*/ 
class App {
    private TelegramClient $tClient;
    
    public function __construct()
    {
        $this->tClient = new TelegramClient(); 
    }

    public function run() {
        $this->tClient->run();
    }
}