<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class TelegramMessagePlugin
 * @package Grav\Plugin
 */
class TelegramMessagePlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onFormProcessed'      => ['onFormProcessed', 0],
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageContentRaw' => ['onPageContentRaw', 0]
        ]);
    }

    public function onFormProcessed(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        //dump($form);
//        dump($action);
        //dump($params);
//        dump($this);

        switch ($action) {
            case 'email':
                // Prepare Twig variables
                $vars = array(
                    'form' => $form
                );
/* ========================================================================= */
                $msg = $this->buildTelegramMessage($params, $vars);
                $function = function () use ($msg) {
                $token = $this->grav['config']->get('plugins.telegram-message.bot_token');
                $chat_id = $this->grav['config']->get('plugins.telegram-message.chat_id');
                $data = [
                    'text' => " o.O \n".$msg,
                    'chat_id' => $chat_id
                ];
                file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );
                };
                $function();
/* ========================================================================= */
                break;
        }
    }

// CF ----------------------------------------------------------------------
    protected function buildTelegramMessage(array $params, array $vars = array())
    {
        /** @var Twig $twig */
        $twig = $this->grav['twig'];

        $params += array(
            'body' => $this->config->get('plugins.email.body', '{% include "forms/data.html.twig" %}'),
        );
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'body':
                    $message = $twig->processString($value, $vars);
            }
        }
        return $message;
    }

}
