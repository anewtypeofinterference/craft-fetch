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

    // Time, in seconds, during which we store the responses in cache
    private $_cacheTtl = 3600;

    // Public Methods
    // =========================================================================
    /**
     * Shorthand method to create a get request
     */
    public function get($url, $options = [], $fromCache = true, $cacheTtl = null)
    {
        return $this->_request('GET', $url, $options, $fromCache);
    }

    /**
     * Sends a request and returns the response and error if any.
     * If the server returns a JSON response, it will be returned as an object.
     *
     * @param string $method The request's method (GET or POST)
     * @param string $url The URL to perform the request to
     * @param array $options An options array
     * @param boolean $fromCache Try fetching results from cache.
     *
     * @return object { "statusCode": 200, "body": { ... }, "error": "If any..." }
     */
    private function _request($method, $url, $options = [], $fromCache = true, $cacheTtl = null)
    {
        // Obtain the cache id
        $cacheId = $this->_getCacheId($method, $url, $options);

        if($fromCache) {
            // Check if the response has already been cached
            if( $cachedResult = Craft::$app->getCache()->get($cacheId) ) {
                return $cachedResult;
            }
        }

        // If cache is empty or bypassed, we send the request
        $requestOptions = array_merge($this->_defaultOptions, $options);

        try {
            // Potentially long-running request, so close session
            // to prevent session blocking on subsequent requests.
            Craft::$app->getSession()->close();

            // Send the request
            $response = static::_client()->request($method, $url, $requestOptions);
            $result = [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'body' => JsonHelper::decodeIfJson($response->getBody())
            ];

            // Store in cache
            Craft::$app->getCache()->set($cacheId, $result, $cacheTtl ? $cacheTtl : $this->_cacheTtl);

            return $result;
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
