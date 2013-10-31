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

abstract class EncodingAbstract implements EncodingInterface
{
    protected $_chars;
    protected $_padFinalGroup;
    protected $_charmap;

    abstract public function encode($rawString);

    abstract public function decode($encodedString);

    /**
     * Return the passed string with whitespace removed.
     *
     * @param string $value  A whitespace laiden encoded string
     * @return string
     */
    public function clean($value)
    {
        $whitespace = array(
            ' ',
            "\r",
            EncodingInterface::EOL,
            "\t",
            "\0",
            "\f",
        );
        return str_replace($whitespace, '', $value);
    }

    /**
     * Splits an encoded string for readabilty.
     *
     * @param string $value  A single line encoded string
     * @param int $length  How many character per line
     * @return string
     */
    public function split($value, $length)
    {
        return rtrim(chunk_split($value, $length, EncodingInterface::EOL), EncodingInterface::EOL);
    }

    /**
     * Formats an encoded string for transmission.
     *
     * @param string $value  A single line encoded string
     * @param int $length  How many characters per line
     * @return string
     */
    public function format($value, $length = 0)
    {
        if ($length) {
            return $this->split($value, $length);
        }

        return $value;
    }
}
