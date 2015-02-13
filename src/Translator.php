<?php
/**
 * This file is part of Herbie.
 *
 * (c) Thomas Breuss <www.tebe.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Herbie;

class Translator
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $language;

    /**
     * @var array
     */
    private $messages;

    /**
     * @param Alias $alias
     * @param Config $config
     * @param string $language
     */
    public function __construct(Alias $alias, Config $config, $language)
    {
        $this->alias = $alias;
        $this->config = $config;
        $this->language = $language;
        $this->messages = [];
    }

    /**
     * Initializer
     */
    public function init()
    {
        $this->loadFiles();
    }

    /**
     * @param string $category
     * @param string $message
     * @param array $params
     * @return string
     */
    public function t($category, $message, array $params = [])
    {
        return $this->translate($category, $message, $params);
    }

    /**
     * @param string $category
     * @param string $message
     * @param array $params
     * @return string
     */
    public function translate($category, $message, array $params = [])
    {
        if(isset($this->messages[$this->language][$category][$message])) {
            $message = $this->messages[$this->language][$category][$message];
        }
        if(empty($params)) {
            return $message;
        }
        return strtr($message, $params);
    }

    /**
     * @return void
     */
    public function loadFiles()
    {
        // load application messages
        $path = $this->alias->get(sprintf('@app/herbie/src/messages/%s.php', $this->language));
        if(file_exists($path)) {
            $this->messages[$this->language]['app'] = require_once($path);
        }

        // load plugin messages
        $pluginList = $this->config->get('plugins.enable', []);
        foreach ($pluginList as $pluginKey) {
            $path = $this->alias->get(sprintf('@plugin/%s/messages/%s.php', $pluginKey, $this->language));
            if(file_exists($path)) {
                $this->messages[$this->language][$pluginKey] = require_once($path);
            }
        }
    }

}
