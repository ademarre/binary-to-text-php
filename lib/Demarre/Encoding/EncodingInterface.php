<?php
/**
 * Binary-to-text PHP Utilities
 *
 * @package     binary-to-text-php
 * @link        https://github.com/kabel/binary-to-text-php
 * @author      Kevin Abel
 * @copyright   2013 Kevin Abel
 * @license     http://opensource.org/licenses/MIT  MIT
 */

namespace Demarre\Encoding;

interface EncodingInterface
{
    const ALPHABET_NUM    = '0123456789';
    const ALPHABET_ALPHA  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    const EOL  = "\n";

    /**
     * Encode a string
     *
     * @param string  $rawString  Binary data to encode
     * @return string
     */
    public function encode($rawString);

    /**
     * Decode a string
     *
     * @param   string  $encodedString  Data to decode
     * @return  string
     */
    public function decode($encodedString);
}
