<?php
/**
 * GSManager
 *
 * This is a mighty and platform independent software for administrating game servers of various kinds.
 * If you need help with installing or using this software, please visit our website at: www.gsmanager.de
 * If you have licensing enquiries e.g. related to commercial use, please contact us at: sales@gsmanager.de
 *
 * @copyright Greenfield Concept UG (haftungsbeschränkt)
 * @license GSManager EULA <https://www.gsmanager.de/eula.php>
 * @version 1.2.1
**/

namespace GSM\Plugins\Chatlog;

use GSM\Daemon\Core\Utils;

/**
 * Chatlog plugin
 *
 * logs the chat to the log folder
 *
 */
class Chatlog extends Utils {

    /**
     * Handles the writing of logfiles
     *
     * @var \GSM\Daemon\Libraries\Logging\LogHandler
     */
    private $loghandler;

    /**
     * Inits the plugin
     *
     * This function initiates the plugin. This means that it register commands
     * default values, and events. It's important that every plugin has this function
     * Otherwise the plugin exists but can't be used
     */
    public function initPlugin() {
        parent::initPlugin();
        $this->config->setDefault('chatlog', 'enabled', false);
        $this->config->setDefault('chatlog', 'string', '<TIME> <PLAYER_NAME> (<PLAYER_GUID>) wrote: <MESSAGE>');
        $this->config->setDefault('chatlog', 'logname', 'chat.log');
    }

    public function enable() {
        parent::enable();
        $this->events->register('playerSay', [$this, 'onPlayerSay']);
        $this->loghandler = new \GSM\Daemon\Libraries\Logging\LogHandler("plugins/", "chatlog");
        $this->loghandler->setEcho(false);
    }

    public function disable() {
        parent::disable();
        $this->events->unregister('playerSay', [$this, 'onPlayerSay']);
        unset($this->loghandler);
    }

    public function onPlayerSay($guid, $text, $executed) {
        $search = [
          '<TIME>',
          '<PLAYER_NAME>',
          '<PLAYER_GUID>',
          '<PLAYER_PID>',
          '<MESSAGE>',
          '<MESSAGE_COLOR>'
        ];

        $replace = [
          date('Y-m-d H:i:s'),
          $this->players[$guid]->getName(),
          $guid,
          $this->players[$guid]->getPID(),
          $this->mod->removecolor($text),
          $text,
        ];

        $logline = str_replace($search, $replace, $this->config->get('chatlog', 'string'));
        $this->loghandler->write($logline, false);
    }
}
