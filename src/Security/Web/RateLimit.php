<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Security\Web;

use FastSitePHP\Application;
use FastSitePHP\Data\KeyValue\StorageInterface;
use FastSitePHP\Lang\Time;
use FastSitePHP\Web\Response;

/**
 * Rate limiting can be used to limit the number of requests or actions that
 * a user makes in a given time frame.
 *
 * Some examples:
 *   - A Web API allowing users to submit no more than 1 request every second.
 *   - No more than 2 new accounts per day per IP address.
 *   - Limit users from sending more than 10 messages per hour.
 *
 * FastSitePHP's Rate Limit class is designed to provide an easy-to-use
 * interface for defining and enforcing rate limits.
 *
 * @link https://en.wikipedia.org/wiki/Rate_limiting
 * @link https://medium.com/smyte/rate-limiter-df3408325846
 * @link https://www.figma.com/blog/an-alternative-approach-to-rate-limiting/
 * @link https://konghq.com/blog/how-to-design-a-scalable-rate-limiting-algorithm/
 * @link https://blog.cloudflare.com/counting-things-a-lot-of-different-things/
 * @link https://stripe.com/blog/rate-limiters
 */
class RateLimit
{
    /**
     * Filter the request if it is allowed based on a rate limit. If the user's
     * rate limit is reached then a 429 [Too Many Requests] response is sent
     * and [exit()] is called to stop the script execution.
     *
     * The same options used for [allow()] are used here.
     *
     * @param array $options
     * @throws \Exception
     */
    public function filterRequest(Application $app, array $options)
    {
        list($allowed, $headers) = $this->allow($options);
        if (!$allowed) {
            // Build Error HTML
            $retry_after = $headers['Retry-After'];
            $message = 'You can retry this request after ' . Time::secondsToText($retry_after);
            $html = $app->errorPage('Too Many Requests', $message);

            // Send the 429 'Too Many Requests' response and stop script
            // [$app] is passed to the response to keep any custom response
            // headers defined by the app.
            $res = new Response($app);
            $res->statusCode(429);
            $res->header('Content-Type', 'text/html; charset=UTF-8');
            foreach ($headers as $header => $value) {
                $res->header($header, (string)$value);
            }
            $res->content($html);
            $res->send();
            exit();
        }
    }

    /**
     * Check if a request or action is allowed based on a rate limit.
     *
     * Required Options:
     *     - [storage]: Object - Instance of [FastSitePHP\Data\KeyValue\StorageInterface]
     *     - [id]: Id assigned to the user or request. For example the client's IP Address or a user id
     *
     * Common Optional Options:
     *     - [max_allowed] (int): Maximum number of requests allowed for the specified duration
     *     - [duration] (int): Time in sections
     *
     * Additional Options:
     *     - [key]: String value to prefix when saving a key-value-pair.
     *       This would be used if you are using the RateLimiter for multiuple actions in the same site.
     *     - [algo]: Algorithm to use ['fixed-window-counter' or 'token-bucket']. Defaults to 'fixed-window-counter'.
     *
     * @param array $options
     * @return array - list($allowed, $headers)
     * @throws \Exception
     */
    public function allow(array $options)
    {
        // Get Options
        $max_allowed = (isset($options['max_allowed']) ? $options['max_allowed'] : 1);
        $duration = (isset($options['duration']) ? $options['duration'] : 1);
        $storage = (isset($options['storage']) ? $options['storage'] : null);
        $id = (isset($options['id']) ? $options['id'] : null);
        $key = (isset($options['key']) ? $options['key'] : null);
        $algo = (isset($options['algo']) ? $options['algo'] : 'fixed-window-counter');

        // Validation Options
        if (filter_var($max_allowed, FILTER_VALIDATE_INT) === false) {
            throw new \Exception('Option [max_allowed] must be an int, received: ' . gettype($max_allowed));
        }
        if (filter_var($duration, FILTER_VALIDATE_INT) === false) {
            throw new \Exception('Option [duration] must be an int, received: ' . gettype($duration));
        }
        if ($id === null || !is_string($id) || trim($id) === '') {
            throw new \Exception('Option [id] is required and must be a non-empty string');
        }
        if ($storage === null || !is_object($storage) || !($storage instanceof StorageInterface)) {
            throw new \Exception('Option [storage] is required and must be an instance of [FastSitePHP\Data\KeyValue\StorageInterface]');
        }
        $max_allowed = (int)$max_allowed;
        $duration = (int)$duration;

        // Check request
        switch ($algo) {
            case 'fixed-window-counter':
                $key = ($key === null ? 'rate-limit-fwc-' : $key) . trim($id);
                return $this->fixedWindowCounter($max_allowed, $duration, $storage, $key);
            case 'token-bucket':
                $key = ($key === null ? 'rate-limit-tb-' : $key) . trim($id);
                return $this->tokenBucket($max_allowed, $duration, $storage, $key);
            default:
                throw new \Exception("Unknown Algorithm specified, valid options: ['fixed-window-counter', 'token-bucket']");
        }
    }

    /**
     * Rate Limiting - Fixed Window Counter Algorithm
     *
     * @param int $max_allowed
     * @param int $duration
     * @param StorageInterface $storage
     * @param string $key
     * @return array
     */
    private function fixedWindowCounter($max_allowed, $duration, StorageInterface $storage, $key)
    {
        $now = time();
        $allowed = true;
        $retry_after = null;

        // Get saved value
        $expires = null;
        $remaining = null;
        $value = $storage->get($key);
        if ($value !== null) {
            $pattern = '/^(\d+):(\d+)$/';
            if (preg_match($pattern, $value, $matches)) {
                $expires = (int)$matches[1];
                $remaining = (float)$matches[2];
            }
        }

        // Check Request
        if ($expires === null || $expires < $now) {
            // Reset to (max - 1) because the counter just started or has expired
            $expires = $now + $duration;
            $remaining = $max_allowed - 1;
        } elseif ($remaining <= 0) {
            // Limit reached
            $allowed = false;
            $retry_after = max(1, $expires - $now);
        } else {
            // Decrease counter
            $remaining--;
        }

        // Build Headers
        $headers = array();
        if ($retry_after !== null) {
            $headers['Retry-After'] = $retry_after;
        }
        $plural1 = ($max_allowed === 1 ? '' : 's');
        $plural2 = ($duration === 1 ? '' : 's');
        $headers['X-RateLimit-Limit'] = "${max_allowed} Request${plural1} per ${duration} Second${plural2}";
        $headers['X-RateLimit-Remaining'] = $remaining;
        $headers['X-RateLimit-Reset'] = $expires;

        // Save status and return result
        $value = $expires . ':' . $remaining;
        $storage->set($key, $value);
        return array($allowed, $headers);
    }

    /**
     * Rate Limiting - Token Bucket Algorithm
     * Based on code from https://stackoverflow.com/a/668327
     *
     * @link https://en.wikipedia.org/wiki/Token_bucket
     * @param int $max_allowed
     * @param int $duration
     * @param StorageInterface $storage
     * @param string $key
     * @return array
     */
    private function tokenBucket($max_allowed, $duration, StorageInterface $storage, $key)
    {
        $now = time();
        $allowed = true;
        $retry_after = null;

        // Get saved value
        $prev_request = null;
        $requests_allowed = null;
        $value = $storage->get($key);
        if ($value !== null) {
            $pattern = '/^(\d+):([0-9.]+)$/';
            if (preg_match($pattern, $value, $matches)) {
                $prev_request = (int)$matches[1];
                $requests_allowed = (float)$matches[2];
            }
        }

        // Check Request
        if ($prev_request === null) {
            // New Request
            $requests_allowed = $max_allowed - 1;
        } else {
            // Calculate based on previous request
            $time_passed = $now - $prev_request;
            $allowed_per_sec = $max_allowed / $duration;
            $requests_allowed += $time_passed * $allowed_per_sec;

            if ($requests_allowed > $max_allowed) {
                // Reset to (max - 1) because the bucket has expired
                $requests_allowed = $max_allowed - 1;
            } elseif ($requests_allowed < 1.0) {
                // Limit reached
                $allowed = false;
                $requests_allowed = 0;
                // Round-up to next int so 1.2 results with 2
                $retry_after = ceil((1 - $requests_allowed) * ($duration / $max_allowed));
            } else {
                // Decrease counter
                $requests_allowed--;
            }
        }

        // Build Headers
        $headers = array();
        if ($retry_after !== null) {
            $headers['Retry-After'] = $retry_after;
        }

        // Save status and return result
        $value = $now . ':' . $requests_allowed;
        $storage->set($key, $value);
        return array($allowed, $headers);
    }
}
