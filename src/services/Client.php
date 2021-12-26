<?php
/**
 * Craft Fetch plugin for Craft CMS 3.x
 *
 * @link      https://anti.as
 * @copyright Copyright (c) 2021 Lasse Mejlvang Tvedt
 */

namespace anti\fetch\services;

use anti\fetch\Fetch as Plugin;
use anti\fetch\models\Settings;


use Craft;
use craft\base\Component;
use craft\helpers\ConfigHelper;
use craft\helpers\Json as JsonHelper;

class Client extends Component
{
    // Private properties
    // =========================================================================
    private $_defaultOptions = [
        'timeout'         => 30,
        'connect_timeout' => 2,
        'allow_redirects' => true
    ];

    // Public Methods
    // =========================================================================
    /**
     * Shorthand method to create a get request
     */
    public function get($url, $options = [], $cache = true)
    {
        return $this->_request('GET', $url, $options, $cache);
    }

    /**
     * Sends a request and returns the response and error if any.
     * If the server returns a JSON response, it will be returned as an object.
     *
     * @param string $method The request's method (GET or POST)
     * @param string $url The URL to perform the request to
     * @param array $options An options array
     * @param mixed $cache Try fetching results from cache.
     *
     * @return object { "statusCode": 200, "body": { ... }, "error": "If any..." }
     */
    private function _request($method, $url, $options = [], $cache = true)
    {
        if ($cache) {
          $cacheKey = $this->_getCacheId($method, $url, $options);
          $cacheService = Craft::$app->getCache();

          if (($cachedContent = $cacheService->get($cacheKey)) !== false) {
            return $cachedContent;
          }

          $elementsService = Craft::$app->getElements();
          $elementsService->startCollectingCacheTags();
        }

        // Get data
        $data = $this->_fetchData($method, $url, $options);

        // Cache it?
        if ($cache) {
          if(isset($data['error']) && $data['error']) {
            // Cache errors for 5 minutes
            $expire = ConfigHelper::durationInSeconds(300);
          } else if ($cache !== true) {
            $expire = ConfigHelper::durationInSeconds($cache);
          } else {
            $expire = null;
          }

          $dep = $elementsService->stopCollectingCacheTags();
          $dep->tags[] = 'fetch';
          $cacheService->set($cacheKey, $data, $expire, $dep);
        }

        return $data;
    }

    private function _fetchData($method, $url, $options)
    {
        // If cache is empty or bypassed, we send the request
        $requestOptions = array_merge($this->_defaultOptions, $options);

        try {
            // Potentially long-running request, so close session
            // to prevent session blocking on subsequent requests.
            Craft::$app->getSession()->close();

            // Send the request
            $response = static::_client()->request($method, $url, $requestOptions);
            
            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'body' => JsonHelper::decodeIfJson($response->getBody())
            ];
        } catch(\Exception $e) {
            return [
                'error' => true,
                'reason' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a cache identifier based on the method, the url and the parameters
     */
    private function _getCacheId($method, $url, $options)
    {
        return 'fetch_' . $method . '_' . $url . '_' . md5(json_encode($options));
    }

    /**
     * Create a new instance of HTTP client
     */
    private static function _client()
    {
        static $client;

        if (!$client) {
            $client = Craft::createGuzzleClient();
        }

        return $client;
    }

}
