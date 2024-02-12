<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\TelegramBot;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: TelegramBot
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class TelegramBot extends SynapseBaseEntity
{
    /**
     * Send message to agent
     *
     * @param \Qore\App\SynapseNodes\Components\User\User $_agent 
     * @param string $_message 
     *
     * @return
     */
    public function send(User $_agent, string $_message): TelegramBot
    {
        if ($chat = $_agent->getOption('chat', false)) {
            # - Create Telegram API object
            $telegram = new Telegram($this['token'], $this['name']);

            $result = Request::sendMessage([
                'chat_id' => $chat,
                'text'    => $_message,
                'parse_mode' => 'HTML',
            ]);
        }

        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
