Binary-to-Text Utilities for PHP
=================================

This library contains classes for encoding/decoding binary/text. These classes are contained in a [PSR-0][psr0] compliant namespace `Demarre\Encoding`.

Binary-to-text encoding is usually used to represent data in a notation that is safe for transport over text-based protocols, and there are several other practical uses. See the examples below.

Library classes include:

- `Base2n` for binary-to-text conversion with arbitrary encoding schemes that represent binary data in a base 2<sup>n</sup> notation.
- `Base85` for binary-to-text conversion using a general algorithm that encodes 4 bytes of data in 5 characters.

[rfc4648base64]:    http://tools.ietf.org/html/rfc4648#section-4 "RFC 4648 Base64 Specification"
[rfc4648base32]:    http://tools.ietf.org/html/rfc4648#section-6 "RFC 4648 Base32 Specification"
[psr0]:             http://www.php-fig.org/psr/psr-0/ "PHP Autoloading Standard"
[psr0autoloader]:   https://gist.github.com/jwage/221634 "PHP Autoloader Implementation"




Basic Usage
-----------

The library comes with a static class for constructing various common/known binary-to-text encoding schemes (`Scheme`), constructed using this class's factory method. By using the factory, you don't have to remember the various alphabets and padding rules for each scheme.

All examples that follow assume you have a [PSR-0][psr0] [autoloader][psr0autoloader] registered with the location of this library.

Let's instantiate a [Base32][rfc4648base32] encoder:

```php
use Demarre\Encoding\Scheme;

// RFC 4648 base32 alphabet; case-insensitive
$base32 = Scheme::factory(Scheme::BASE32);
$encoded = $base32->encode('encode this');
// MVXGG33EMUQHI2DJOM======
```

The factory supports the following schemes:

- `BIN` - [base-2, binary][binary]
- `OCT` - [base-8, octal][octal]
- `HEX` - [base-16, hexadecimal][hexadecimal]
- `BASE32` - [base-32][base32] ([RFC 4648][rfc4648])
- `BASE32_HEX_RFC_4648` - Alertnative Base32 from [RFC 4648 Section 7][rfc4648base32hex]
- `BASE32_Z` - Alternative Base32 from [Zooko O'Whielacronx][z32]
- `BASE32_CROCKFORD` - Alternative Base32 from [Douglas Crockford][crockford32]
- `BASE64` - [base-64][base64] ([RFC 4648][rfc4648])
- `BASE64_URL_RFC_4648` - Alternative Base64 for URLs from [RFC 4648 Section 5][rfc4648base64url]
- `BASE64_BCRYPT` - Alternative Base64 for [bcrypt](https://github.com/ademarre/binary-mcf)
- `BASE85` - [base-85][base85]
- `BASE85_Z` - Alternative Base85 from [Pieter Hintjens](http://rfc.zeromq.org/spec:32/Z85)
- `BASE85_RFC_1924` - Alternative Base85 from [RFC 1924](http://tools.ietf.org/html/rfc1924)
- `BASE85_BTOA` - Extended Base85 with compression characters z and y
- `BASE85_ADOBE` - Extended Base85 with compression character z


### <code>Base2n</code> Class

It can handle non-standard variants of many standard encoding schemes such as [Base64][rfc4648base64] and [Base32][rfc4648base32]. Many binary-to-text encoding schemes use a fixed number of bits of binary data to generate each encoded character. Such schemes generalize to a single algorithm, implemented here.

#### Constructor Parameters

- `integer $bitsPerCharacter` **Required**. The number of bits to use for each encoded character; 1–8. The most practical range is 1–6. The encoding's radix is a power of 2: `2^$bitsPerCharacter`.
    1. [base-2, binary][binary]
    2. [base-4, quaternary][quaternary]
    3. [base-8, octal][octal]
    4. [base-16, hexadecimal][hexadecimal]
    5. [base-32][base32]
    6. [base-64][base64]
    7. base-128
    8. base-256

[binary]:       http://en.wikipedia.org/wiki/Binary_numeral_system "Binary Notation"
[quaternary]:   http://en.wikipedia.org/wiki/Quaternary_numeral_system "Base-2 Notation"
[octal]:        http://en.wikipedia.org/wiki/Octal "Octal Notation"
[hexadecimal]:  http://en.wikipedia.org/wiki/Base16 "Hexadecimal Notation"
[base32]:       http://en.wikipedia.org/wiki/Base32 "Base32 Encoding"
[base64]:       http://en.wikipedia.org/wiki/Base64 "Base64 Encoding"
[z32]:          http://philzimmermann.com/docs/human-oriented-base-32-encoding.txt
[crockford32]:  http://www.crockford.com/wrmg/base32.html
[base85]:       http://en.wikipedia.org/wiki/Ascii85 "Base86 Encoding"
[rfc4648base64url]: http://tools.ietf.org/html/rfc4648#section-5 "Modified Base64 for URLs"
[rfc4648base32hex]: http://tools.ietf.org/html/rfc4648#section-7 "Modified Base32 with Extended Hex Alphabet"

- `string|boolean $chars` This string specifies the base alphabet. Pass `true` to use native PHP alphabet functions. Must be `2^$bitsPerCharacter` long. Default: `0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_`

- `boolean $caseSensitive` To decode in a case-sensitive manner. Default: `false`

- `boolean $rightPadFinalBits` How to encode the last character when the bits remaining are fewer than `$bitsPerCharacter`. When `TRUE`, the bits to encode are placed in the most significant position of the final group of bits, with the lower bits set to `0`. When `FALSE`, the final bits are placed in the least significant position. For [RFC 4648][rfc4648] encodings, `$rightPadFinalBits`should be `true`. Default: `false`

[rfc4648]:  http://tools.ietf.org/html/rfc4648 "RFC 4648: Base16, Base32, Base64"

- `boolean $padFinalGroup` It's common to encode characters in groups. For example, Base64 (which is based on 6 bits per character) converts 3 raw bytes into 4 encoded characters. If insufficient bytes remain at the end, the final group will be padded with `=` to complete a group of 4 characters, and the encoded length is always a multiple of 4. Although the information provided by the padding is redundant, some programs rely on it for decoding; Base2n does not. Default: `false`

- `string $padCharacter` When `$padFinalGroup` is `true`, this is the pad character used. Default: `=`


#### <code>encode()</code> Parameters

- `string $rawString` **Required**. The data to be encoded.


#### <code>decode()</code> Parameters

- `string $encodedString` **Required**. The string to be decoded.
- `boolean $strict` When `true`, `null` will be returned if `$encodedString` contains an undecodable character. When `false`, unknown characters are simply ignored. Default: `false`


### <code>Base85</code> Class

It can handle encoding scheme that are [Base85][base85]. It shares the same `encode()` and `decode()` methods of `Base2n`, but the `decode()` function is always strict (it will return null when decoding invalid input).

Examples
--------

All examples that follow assume you have a [PSR-0][psr0] [autoloader][psr0autoloader] registered with the location of this library.

```php
// RFC 4648 base32 alphabet; case-insensitive
$base32 = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::BASE32);
$encoded = $base32->encode('encode this');
// MVXGG33EMUQHI2DJOM======
```

```php
// RFC 4648 base32hex alphabet
$base32hex = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::BASE32_HEX_RFC_4648);
$encoded = $base32hex->encode('encode this');
// CLN66RR4CKG78Q39EC======
```


Octal notation:

```php
$octal = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::OCT);
$encoded = $octal->encode('encode this');
// 312671433366214510072150322711
```


A convenient way to go back and forth between binary notation and its real binary representation:

```php
$binary = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::BIN);
$encoded = $binary->encode('encode this');
// 0110010101101110011000110110111101100100011001010010000001110100011010000110100101110011
$decoded = $binary->decode($encoded);
// encode this
```


PHP uses a both standard and non-standard binary-to-text encoding schemes to generate session identifiers from random hash digests. The most efficient way to store these session IDs in a database is to decode them back to their raw hash digests. PHP's encoding scheme is configured with the <code>[session.hash_bits_per_character][phphashbits]</code> php.ini setting. The decoded size depends on the hash function, set with <code>[session.hash_function][phphash]</code> in php.ini.

When `session.hash_bits_per_character` is 5, PHP uses the standard Base32 Hex encoding from RFC 4648.

```php
// session.hash_function = 0
// session.hash_bits_per_character = 5
// 128-bit session ID
$sessionId = 'q3c8n4vqpq11i0vr6ucmafg1h3';
// Decodes to 16 bytes
$phpBase32 = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::BASE32_HEX_RFC_4648);
$rawSessionId = $phpBase32->decode($sessionId);
```

When `session.hash_bits_per_character` is 6, PHP uses a non-standard Base64 alphabet. You can use the encoding classes to create your own special encoder.

```php
// session.hash_function = 1
// session.hash_bits_per_character = 6
// 160-bit session ID
$sessionId = '7Hf91mVc,q-9W1VndNNh3evVN83';
// Decodes to 20 bytes
$phpBase64 = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64,
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64_PHP
));
$rawSessionId = $phpBase64->decode($sessionId);
```

[phphashbits]: http://php.net/manual/en/session.configuration.php#ini.session.hash-bits-per-character "PHP session.hash_bits_per_character"
[phphash]: http://php.net/manual/en/session.configuration.php#ini.session.hash-function "PHP session.hash_function"


Generate random security tokens:
```php
$tokenEncoder = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64,
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64_PHP
));
$binaryToken = openssl_random_pseudo_bytes(32); // PHP >= 5.3
$token = $tokenEncoder->encode($binaryToken);
// Example: U6M132v9FG-AHhBVaQWOg1gjyUi1IogNxuen0i3u3ep
```


The rest of these examples are probably more fun than they are practical.


We can encode arbitrary data with a 7-bit encoding. (Note that this is not the same as the [7bit MIME content-transfer-encoding][7bit].)
```php
// This uses all 7-bit ASCII characters
$base128chars = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"
              . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F"
              . "\x20\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2A\x2B\x2C\x2D\x2E\x2F"
              . "\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3A\x3B\x3C\x3D\x3E\x3F"
              . "\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4A\x4B\x4C\x4D\x4E\x4F"
              . "\x50\x51\x52\x53\x54\x55\x56\x57\x58\x59\x5A\x5B\x5C\x5D\x5E\x5F"
              . "\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6A\x6B\x6C\x6D\x6E\x6F"
              . "\x70\x71\x72\x73\x74\x75\x76\x77\x78\x69\x7A\x7B\x7C\x7D\x7E\x7F";

$base128 = new Demarre\Encoding\Base2n(7, $base128chars);
$encoded = $base128->encode('encode this');
```
[7bit]: http://msdn.microsoft.com/en-us/library/ms526290(v=exchg.10).aspx "7bit MIME Content-Transfer-Encoding"


The following encoding guarantees that the most significant bit is set for every byte:
```php
// "High" base-128 encoding
$high128chars = "\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F"
              . "\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F"
              . "\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF"
              . "\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF"
              . "\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF"
              . "\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF"
              . "\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF"
              . "\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

$high128 = new Demarre\Encoding\Base2n(7, $high128chars);
$encoded = $high128->encode('encode this');
```


Let's create an encoding using exclusively non-printable control characters!
```php
// Base-32 non-printable character encoding
$noPrintChars = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"
              . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";

$nonPrintable32 = new Demarre\Encoding\Base2n(5, $noPrintChars);
$encoded = $nonPrintable32->encode('encode this');
```


Why not encode data using only whitespace? Here's a base-4 encoding using space, tab, new line, and carriage return:
```php
// Base-4 whitespace encoding
$whitespaceChars = " \t\n\r";

$whitespace = new Demarre\Encoding\Base2n(2, $whitespaceChars);
$encoded = $whitespace->encode('encode this');
// "\t\n\t\t\t\n\r\n\t\n \r\t\n\r\r\t\n\t \t\n\t\t \n  \t\r\t \t\n\n \t\n\n\t\t\r \r"

$decoded = $whitespace->decode(
    "\t\n\t\t\t\n\r\n\t\n \r\t\n\r\r\t\n\t \t\n\t\t \n  \t\r\t \t\n\n \t\n\n\t\t\r \r"
);
// encode this
```



A Note on Speed
---------------

Base2n with a custom alphabet is not slow, but it will never outperform an encoding function implemented in C. The Base2n class supports using native PHP functions for encoding/decoding for 4 and 6 bits-per-character by passing `true` as the second argument to its constructor (in place of a custom alphabet). You can also use the native encoding/decoding functions with a custom alphabet by passing the translations as the seventh constructor parameter.

These schemes have already been built into the `Scheme` factory, but are shown here for example.


```php
// RFC 4648 base64url with Base2n...
$base64url = new Demarre\Encoding\Base2n(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_', true, true, false);
$encoded = $base64url->encode("encode this \xBF\xC2\xBF");
// ZW5jb2RlIHRoaXMgv8K_

// RFC 4648 base64url with native functions...
$base64url = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64,
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64_URL
));
// ZW5jb2RlIHRoaXMgv8K_
```


Example of [decoding a Bcrypt hash][bmcf]:
```php
// Decode the salt and digest from a Bcrypt hash

$hash = '$2y$14$i5btSOiulHhaPHPbgNUGdObga/GC.AVG/y5HHY1ra7L0C9dpCaw8u';
$encodedSalt    = substr($hash, 7, 22);
$encodedDigest  = substr($hash, 29, 31);

// Using userland Base2n...
$bcrypt64 = new Base2n(6, './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', true, true);
$rawSalt    = $bcrypt64->decode($encodedSalt);   // 16 bytes
$rawDigest  = $bcrypt64->decode($encodedDigest); // 23 bytes

// Using Base2n with native...
$chars = Demarre\Encoding\EncodingInterface::ALPHABET_ALPHA 
    . strtolower(Demarre\Encoding\EncodingInterface::ALPHABET_ALPHA)
    . Demarre\Encoding\EncodingInterface::ALPHABET_NUM;
$bcrypt64 = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    $chars . Base2n::ALPHABET_SYM_BASE64,
    Base2n::ALPHABET_SYM_BASE64_BCRYPT . $chars
));
$rawSalt    = $bcrypt64->decode($encodedSalt);   // 16 bytes
$rawDigest  = $bcrypt64->decode($encodedDigest); // 23 bytes
```

[bmcf]: https://github.com/ademarre/binary-mcf "Binary Modular Crypt Format (BMCF)"

You can encode and decode hexadecimal:

```php
// Hexadecimal with userland Base2n...
$hexadecimal = new Demarre\Encoding\Base2n(4);
$encoded = $hexadecimal->encode('encode this'); // 656e636f64652074686973
$decoded = $hexadecimal->decode($encoded);      // encode this

// It's better to use Base2n with native...
$hexadecimal = new Demarre\Encoding\Base2n(4, true);
$encoded = $hexadecimal->encode('encode this'); // 656e636f64652074686973
$decoded = $hexadecimal->decode($encoded);      // encode this
```
