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

use \InvalidArgumentException;

/**
 * Class for constructing well known encoding schemes
 *
 * @package binary-to-text-php
 */
class Scheme
{
    // standard/known encoding schemes

    const BIN                  = 1;
    const OCT                  = 2;
    const HEX                  = 3;

    // http://tools.ietf.org/html/rfc4648
    const BASE16_RFC_4648      = self::HEX;
    const BASE16               = self::HEX;
    const BASE32_RFC_4648      = 4;
    const BASE32               = self::BASE32_RFC_4648;
    const BASE32_HEX_RFC_4648  = 5;

    // http://philzimmermann.com/docs/human-oriented-base-32-encoding.txt
    const BASE32_Z             = 6;

    // http://www.crockford.com/wrmg/base32.html
    const BASE32_CROCKFORD     = 7;

    const BASE64_RFC_4648      = 8;
    const BASE64               = self::BASE64_RFC_4648;
    const BASE64_URL_RFC_4648  = 9;

    // https://github.com/ademarre/binary-mcf
    const BASE64_BCRYPT        = 10;

    // http://en.wikipedia.org/wiki/Ascii85
    const ASCII85              = 11;
    const BASE85               = self::ASCII85;

    // http://rfc.zeromq.org/spec:32/Z85
    const BASE85_Z             = 12;

    // http://tools.ietf.org/html/rfc1924
    const BASE85_RFC_1924      = 13;

    const BASE85_BTOA          = 14;
    const BASE85_ADOBE         = 15;

    /**
     * Construct a standard/known encoding scheme
     *
     * @param   int $type                 One of the class constants (enum) for encoding schemes
     * @throws  InvalidArgumentException  for an unrecognized type
     * @return  EncodingAbstract
     */
    public static function factory($type)
    {
        $scheme = null;

        switch ($type) {
            case self::BIN:
            case self::OCT:
                $bitsPerCharacter = ($type == self::BIN) ? 1 : 3;
                $chars = substr(EncodingInterface::ALPHABET_NUM, 0, 1 << $bitsPerCharacter);
                $scheme = new Base2n($bitsPerCharacter, $chars);
                break;
            case self::HEX:
                $scheme = new Base2n(4, true);
                break;
            case self::BASE32_RFC_4648:
            case self::BASE32_HEX_RFC_4648:
                $bitsPerCharacter = 5;
                $chars = ($type == self::BASE32_RFC_4648)
                    ? (EncodingInterface::ALPHABET_ALPHA . substr(
                        EncodingInterface::ALPHABET_NUM,
                        2,
                        1 << $bitsPerCharacter - strlen(EncodingInterface::ALPHABET_ALPHA)
                    ))
                    : (EncodingInterface::ALPHABET_NUM . substr(
                        EncodingInterface::ALPHABET_ALPHA,
                        0,
                        1 << $bitsPerCharacter - strlen(EncodingInterface::ALPHABET_NUM)
                    ));
                $scheme = new Base2n($bitsPerCharacter, $chars, false, true, true);
                break;
            case self::BASE32_Z:
                $bitsPerCharacter = 5;
                $chars = Base2n::ALPHABET_Z;
                $scheme = new Base2n(5, Base2n::ALPHABET_Z, false, true);
                break;
            case self::BASE32_CROCKFORD:
                $bitsPerCharacter = 5;
                $chars = EncodingInterface::ALPHABET_NUM . str_replace(
                    str_split(Base2n::ALPHABET_CROCKFORD_EXLC),
                    '',
                    EncodingInterface::ALPHABET_ALPHA
                );
                $scheme = new Base2n($bitsPerCharacter, $chars, false, true, true, Base2n::RFC_4648_PAD, array(
                    Base2n::TRANSLATE_CROCKFORD_FROM,
                    Base2n::TRANSLATE_CROCKFORD_TO
                ));
                break;
            case self::BASE64_RFC_4648:
                $scheme = new Base2n(6, true, true, true, true);
                break;
            case self::BASE64_URL_RFC_4648:
                $scheme = new Base2n(6, true, true, true, false, '', array(
                    Base2n::ALPHABET_SYM_BASE64,
                    Base2n::ALPHABET_SYM_BASE64_URL
                ));
                break;
            case self::BASE64_BCRYPT:
                $chars = EncodingInterface::ALPHABET_ALPHA . strtolower(EncodingInterface::ALPHABET_ALPHA)
                    . EncodingInterface::ALPHABET_NUM;
                $scheme = new Base2n(6, true, true, true, false, '', array(
                    $chars . Base2n::ALPHABET_SYM_BASE64,
                    Base2n::ALPHABET_SYM_BASE64_BCRYPT . $chars
                ));
                break;
            case self::ASCII85:
            case self::BASE85_BTOA:
            case self::BASE85_ADOBE:
                $chars = implode('', range(Base85::ALPHABET_ASCII_START, Base85::ALPHABET_ASCII_END));
                $exceptions = array();
                if ($type != self::ASCII85) {
                    $exceptions[Base85::EXCEPTION_Z] = 'z';

                    if ($type != self::BASE85_ADOBE) {
                        $exceptions[Base85::EXCEPTION_Y] = 'y';
                    }
                }
                $scheme = new Base85($chars, false, $exceptions);
                break;
            case self::BASE85_Z:
                $chars = EncodingInterface::ALPHABET_NUM . strtolower(EncodingInterface::ALPHABET_ALPHA)
                    . EncodingInterface::ALPHABET_ALPHA . Base85::ALPHABET_SYM_Z;
                $scheme = new Base85($chars, true);
                break;
            case self::BASE85_RFC_1924:
                $chars = EncodingInterface::ALPHABET_NUM . EncodingInterface::ALPHABET_ALPHA
                    . strtolower(EncodingInterface::ALPHABET_ALPHA) . Base85::ALPHABET_SYM_RFC_1924;
                $scheme = new Base85($chars);
                break;
            default:
                throw new InvalidArgumentException('$type must be a known encoding scheme');
        }

        return $scheme;
    }
}
