<?php

/**
 * This file is part of the league/oauth1-client library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Signature;

class HmacSha1Signature extends AbstractSignature implements Signature
{
    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return 'HMAC-SHA1';
    }

    /**
     * {@inheritdoc}
     */
    public function sign($url, array $parameters = [], $method = 'POST')
    {
        $baseString = $this->baseString($url, $method, $parameters);

        return base64_encode($this->hash($baseString));
    }

    /**
     * Generate a base string for a HMAC-SHA1 signature
     * based on the given a url, method, and any parameters.
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    private function baseString($url, $method = 'POST', array $parameters = [])
    {
        $baseString = rawurlencode($method).'&';

        $urlParts = $this->getUrlParts($url);

        $schemeHostPath = $urlParts['scheme'].'://'.$urlParts['host'].$urlParts['path'];

        $baseString .= rawurlencode($schemeHostPath).'&';

        $data = [];
        parse_str($urlParts['query'], $query);
        foreach (array_merge($query, $parameters) as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $data[rawurlencode($key)] = rawurlencode($value);
        }

        ksort($data);
        array_walk($data, function (&$value, $key) {
            $value = $key.'='.$value;
        });
        $baseString .= rawurlencode(implode('&', $data));

        return $baseString;
    }

    /**
     * Parses a given url into parts, ensurlng specific keys are set in the
     * resulting array.
     *
     * @param string $url
     *
     * @return array
     */
    private function getUrlParts($url)
    {
        $requiredParts = ['scheme', 'host', 'path', 'query'];

        $urlParts = parse_url($url);

        array_map(function ($part) use (&$urlParts) {
            if (!isset($urlParts[$part])) {
                $urlParts[$part] = '';
            }
        }, $requiredParts);

        return $urlParts;
    }

    /**
     * Hashes a string with the signature's key.
     *
     * @param string $string
     *
     * @return string
     */
    private function hash($string)
    {
        return hash_hmac('sha1', $string, $this->getKey(), true);
    }
}
