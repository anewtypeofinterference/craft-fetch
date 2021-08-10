<?php
/**
 * Craft Fetch plugin for Craft CMS 3.x
 *
 * @link      https://anti.as
 * @copyright Copyright (c) 2021 Lasse Mejlvang Tvedt
 */
namespace anti\fetch\twig\variables;

use anti\fetch\Fetch as Plugin;

/**
 * @author    Lasse Mejlvang Tvedt
 * @package   craft-fetch
 * @since     1.0.0
 */
class Fetch
{
    /**
     * Get request
     *
     * @param string $email
     * @param string $id
     *
     * @return mixed
     * @throws DeprecationException
     */
    public function get($url, $options = [], $fromCache = true)
    {
        return Plugin::$plugin->client->get($url, $options, $fromCache);
    }

}