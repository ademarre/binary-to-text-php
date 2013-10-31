<?php
/**
 * Binary-to-text PHP Utilities
 *
 * @package     binary-to-text-php
 * @link        https://github.com/ademarre/binary-to-text-php
 * @author      Andre DeMarre
 * @copyright   2009-2013 Andre DeMarre
 * @license     http://opensource.org/licenses/MIT  MIT
 */

namespace Demarre\Encoding;

use \InvalidArgumentException;
use \UnexpectedValueException;

/**
 * Class for binary-to-text encoding with a base of 2^n
 *
 * The Base2n class is for binary-to-text conversion. It employs a
 * generalization of the algorithms used by many encoding schemes that
 * use a fixed number of bits to encode each character. In other words,
 * the base is a power of 2.
 *
 * Earlier versions of this class were named
 * FixedBitNotation and FixedBitEncoding.
 *
 * @package binary-to-text-php
 */
class Base2n extends EncodingAbstract
{
    const PAD_RFC_4648 = '=';

    const NATIVE_SUPPORT = '4,6';

    protected $_bitsPerCharacter;
    protected $_radix;
    protected $_rightPadFinalBits;
    protected $_padCharacter;
    protected $_caseSensitive;
    protected $_useNative;
    protected $_translate;

    /**
     * Constructor
     *
     * @param   integer $bitsPerCharacter   Bits to use for each encoded character
     * @param   string  $chars              Base character alphabet or true to use native alphabet
     * @param   boolean $caseSensitive      To decode in a case-sensitive manner
     * @param   boolean $rightPadFinalBits  How to encode last character
     * @param   boolean $padFinalGroup      Add padding to end of encoded output
     * @param   string  $padCharacter       Character to use for padding
     * @param   array   $translate          The string translations to apply to native alphabet
     *
     * @throws  InvalidArgumentException    for incompatible parameters
     */
    public function __construct(
        $bitsPerCharacter,
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_',
        $caseSensitive = true,
        $rightPadFinalBits = false,
        $padFinalGroup = false,
        $padCharacter = self::PAD_RFC_4648,
        $translate = array()
    ) {
        // Ensure validity of $bitsPerCharacter
        if (!is_int($bitsPerCharacter)) {
            throw new InvalidArgumentException('$bitsPerCharacter must be an integer');
        }

        // Check for signature to use native functions
        if ($chars === true) {
            // Ensure there is native support for $bitPerCharacter
            if (!in_array($bitsPerCharacter, explode(',', self::NATIVE_SUPPORT))) {
                throw new InvalidArgumentException(sprintf('There is no native support for %d bits per character', $bitsPerCharacter));
            }

            $this->_bitsPerCharacter  = $bitsPerCharacter;
            $this->_useNative         = true;
            $this->_rightPadFinalBits = $rightPadFinalBits;
            $this->_padFinalGroup     = $padFinalGroup;
            $this->_translate         = $translate;
            return;
        }

        // Ensure validity of $chars
        if (!is_string($chars) || ($charLength = strlen($chars)) < 2) {
            throw new InvalidArgumentException('$chars must be a string of at least two characters');
        }

        // Ensure validity of $padCharacter
        if ($padFinalGroup) {
            if (!is_string($padCharacter) || !isset($padCharacter[0])) {
                throw new InvalidArgumentException('$padCharacter must be a string of one character');
            }

            if ($caseSensitive) {
                $padCharFound = strpos($chars, $padCharacter[0]);
            } else {
                $padCharFound = stripos($chars, $padCharacter[0]);
            }

            if ($padCharFound !== false) {
                throw new InvalidArgumentException('$padCharacter can not be a member of $chars');
            }
        }

        if ($bitsPerCharacter < 1) {
            // $bitsPerCharacter must be at least 1
            throw new InvalidArgumentException('$bitsPerCharacter can not be less than 1');

        } elseif ($charLength < 1 << $bitsPerCharacter) {
            // Character length of $chars is too small for $bitsPerCharacter
            // Find greatest acceptable value of $bitsPerCharacter
            $bitsPerCharacter = 1;
            $radix = 2;

            while ($charLength >= ($radix <<= 1) && $bitsPerCharacter < 8) {
                $bitsPerCharacter++;
            }

            $radix >>= 1;
            throw new InvalidArgumentException(
                    '$bitsPerCharacter can not be more than ' . $bitsPerCharacter
                  . ' given $chars length of ' . $charLength
                  . ' (max radix ' . $radix . ')');

        } elseif ($bitsPerCharacter > 8) {
            // $bitsPerCharacter must not be greater than 8
            throw new InvalidArgumentException('$bitsPerCharacter can not be greater than 8');

        } else {
            $radix = 1 << $bitsPerCharacter;
        }

        $this->_chars             = $chars;
        $this->_bitsPerCharacter  = $bitsPerCharacter;
        $this->_radix             = $radix;
        $this->_rightPadFinalBits = $rightPadFinalBits;
        $this->_padFinalGroup     = $padFinalGroup;
        $this->_padCharacter      = $padCharacter[0];
        $this->_caseSensitive     = $caseSensitive;
        $this->_useNative         = false;
        $this->_translate         = $translate;
    }

    public function encode($rawString)
    {
        if ($this->_useNative) {
            return $this->encodeNative($rawString);
        }

        // Unpack string into an array of bytes
        $bytes = unpack('C*', $rawString);
        $byteCount = count($bytes);

        $encodedString = '';
        $byte = array_shift($bytes);
        $bitsRead = 0;

        $chars             = $this->_chars;
        $bitsPerCharacter  = $this->_bitsPerCharacter;
        $rightPadFinalBits = $this->_rightPadFinalBits;
        $padFinalGroup     = $this->_padFinalGroup;
        $padCharacter      = $this->_padCharacter;

        $charsPerByte = 8 / $bitsPerCharacter;
        $encodedLength = $byteCount * $charsPerByte;

        // Generate encoded output; each loop produces one encoded character
        for ($c = 0; $c < $encodedLength; $c++) {

            // Get the bits needed for this encoded character
            if ($bitsRead + $bitsPerCharacter > 8) {
                // Not enough bits remain in this byte for the current character
                // Save the remaining bits before getting the next byte
                $oldBitCount = 8 - $bitsRead;
                $oldBits = $byte ^ ($byte >> $oldBitCount << $oldBitCount);
                $newBitCount = $bitsPerCharacter - $oldBitCount;

                if (!$bytes) {
                    // Last bits; match final character and exit loop
                    if ($rightPadFinalBits) $oldBits <<= $newBitCount;
                    $encodedString .= $chars[$oldBits];

                    if ($padFinalGroup) {
                        // Array of the lowest common multiples of $bitsPerCharacter and 8, divided by 8
                        $lcmMap = array(1 => 1, 2 => 1, 3 => 3, 4 => 1, 5 => 5, 6 => 3, 7 => 7, 8 => 1);
                        $bytesPerGroup = $lcmMap[$bitsPerCharacter];
                        $pads = $bytesPerGroup * $charsPerByte - ceil((strlen($rawString) % $bytesPerGroup) * $charsPerByte);
                        $encodedString .= str_repeat($padCharacter, $pads);
                    }

                    break;
                }

                // Get next byte
                $byte = array_shift($bytes);
                $bitsRead = 0;

            } else {
                $oldBitCount = 0;
                $newBitCount = $bitsPerCharacter;
            }

            // Read only the needed bits from this byte
            $bits = $byte >> 8 - ($bitsRead + ($newBitCount));
            $bits ^= $bits >> $newBitCount << $newBitCount;
            $bitsRead += $newBitCount;

            if ($oldBitCount) {
                // Bits come from seperate bytes, add $oldBits to $bits
                $bits = ($oldBits << $newBitCount) | $bits;
            }

            $encodedString .= $chars[$bits];
        }

        return $encodedString;
    }

    /**
     * @param   string  $encodedString  Data to decode
     * @param   boolean $strict         Returns null if $encodedString contains an undecodable character
     * @return  string
     */
    public function decode($encodedString, $strict = false)
    {
        if (!is_string($encodedString) || !strlen($encodedString)) {
            // Empty string, nothing to decode
            return '';
        }

        if ($this->_useNative) {
            return $this->decodeNative($encodedString);
        }

        if (!empty($this->_translate)) {
            $encodedString = strtr($encodedString, $this->_translate[1], $this->_translate[0]);
        }

        $chars             = $this->_chars;
        $bitsPerCharacter  = $this->_bitsPerCharacter;
        $radix             = $this->_radix;
        $rightPadFinalBits = $this->_rightPadFinalBits;
        $padFinalGroup     = $this->_padFinalGroup;
        $padCharacter      = $this->_padCharacter;
        $caseSensitive     = $this->_caseSensitive;

        // Get index of encoded characters
        if ($this->_charmap) {
            $charmap = $this->_charmap;
        } else {
            $charmap = array();

            for ($i = 0; $i < $radix; $i++) {
                $charmap[$chars[$i]] = $i;
            }

            $this->_charmap = $charmap;
        }

        // The last encoded character is $encodedString[$lastNotatedIndex]
        $lastNotatedIndex = strlen($encodedString) - 1;

        // Remove trailing padding characters
        if ($padFinalGroup) {
            while ($encodedString[$lastNotatedIndex] == $padCharacter) {
                $encodedString = substr($encodedString, 0, $lastNotatedIndex);
                $lastNotatedIndex--;
            }
        }

        $rawString = '';
        $byte = 0;
        $bitsWritten = 0;

        // Convert each encoded character to a series of unencoded bits
        for ($c = 0; $c <= $lastNotatedIndex; $c++) {

            if (!isset($charmap[$encodedString[$c]]) && !$caseSensitive) {
                // Encoded character was not found; try other case
                if (isset($charmap[$cUpper = strtoupper($encodedString[$c])])) {
                    $charmap[$encodedString[$c]] = $charmap[$cUpper];

                } elseif (isset($charmap[$cLower = strtolower($encodedString[$c])])) {
                    $charmap[$encodedString[$c]] = $charmap[$cLower];
                }
            }

            if (isset($charmap[$encodedString[$c]])) {
                $bitsNeeded = 8 - $bitsWritten;
                $unusedBitCount = $bitsPerCharacter - $bitsNeeded;

                // Get the new bits ready
                if ($bitsNeeded > $bitsPerCharacter) {
                    // New bits aren't enough to complete a byte; shift them left into position
                    $newBits = $charmap[$encodedString[$c]] << $bitsNeeded - $bitsPerCharacter;
                    $bitsWritten += $bitsPerCharacter;

                } elseif ($c != $lastNotatedIndex || $rightPadFinalBits) {
                    // Zero or more too many bits to complete a byte; shift right
                    $newBits = $charmap[$encodedString[$c]] >> $unusedBitCount;
                    $bitsWritten = 8; //$bitsWritten += $bitsNeeded;

                } else {
                    // Final bits don't need to be shifted
                    $newBits = $charmap[$encodedString[$c]];
                    $bitsWritten = 8;
                }

                $byte |= $newBits;

                if ($bitsWritten == 8 || $c == $lastNotatedIndex) {
                    // Byte is ready to be written
                    $rawString .= pack('C', $byte);

                    if ($c != $lastNotatedIndex) {
                        // Start the next byte
                        $bitsWritten = $unusedBitCount;
                        $byte = ($charmap[$encodedString[$c]] ^ ($newBits << $unusedBitCount)) << 8 - $bitsWritten;
                    }
                }

            } elseif ($strict) {
                // Unable to decode character; abort
                return null;
            }
        }

        return $rawString;
    }

    /**
     * Encode a string using native PHP functions
     *
     * @param   string  $rawString        Binary data to encode
     * @throws  UnexpectedValueException  for unsupported bits per character
     * @return  string
     */
    protected function encodeNative($rawString)
    {
        $encodedString = '';

        switch ($this->_bitsPerCharacter) {
            case 6:
                $encodedString = base64_encode($rawString);

                if (!$this->_padFinalGroup) {
                    $encodedString = rtrim($encodedString, self::PAD_RFC_4648);
                }

                if (!empty($this->_translate)) {
                    $encodedString = strtr($encodedString, $this->_translate[0], $this->_translate[1]);
                }

                break;
            case 4:
                $encodedString = bin2hex($rawString);
                break;
            default:
                $message = 'No native function exists to %s %d bits per character';
                throw new UnexpectedValueException(sprintf($message, 'encode', $this->_bitsPerCharacter));
        }

        return $encodedString;
    }

    /**
     * Decode a string using native PHP functions
     *
     * @param   string  $encodedString    Data to decode
     * @throws  UnexpectedValueException  for unsupported bits per character
     * @return  string
     */
    protected function decodeNative($encodedString)
    {
        $rawString = '';

        switch ($this->_bitsPerCharacter) {
            case 6:
                if (!empty($this->_translate)) {
                    $rawString = strtr($rawString, $this->_translate[1], $this->_translate[0]);
                }

                $rawString = base64_decode($encodedString);
                break;
            case 4:
                if (!function_exists('hex2bin')) {
                    $rawString = pack('H*', $encodedString);
                } else {
                    $rawString = hex2bin($encodedString);
                }
                break;
            default:
                $message = 'No native function exists to %s %d bits per character';
                throw new UnexpectedValueException(sprintf($message, 'decode', $this->_bitsPerCharacter));
        }

        return $rawString;
    }
}
