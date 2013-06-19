Binary-to-Text Utilities for PHP
=================================

For now, the only class in this repository is **Base2n**.

Base2n is for binary-to-text conversion with arbitrary encoding schemes that represent binary data in a base 2<sup>n</sup> notation. It can handle non-standard variants of many standard encoding schemes such as [Base64][rfc4648base64] and [Base32][rfc4648base32]. Many binary-to-text encoding schemes use a fixed number of bits of binary data to generate each encoded character. Such schemes generalize to a single algorithm, implemented here.

[rfc4648base64]:    http://tools.ietf.org/html/rfc4648#section-4 "RFC 4648 Base64 Specification"
[rfc4648base32]:    http://tools.ietf.org/html/rfc4648#section-6 "RFC 4648 Base32 Specification"

Binary-to-text encoding is usually used to represent data in a notation that is safe for transport over text-based protocols, and there are several other practical uses. See the examples below.



Basic Base2n Usage
------------------

With Base2n, you define your encoding scheme parametrically. Let's instantiate a [Base32][rfc4648base32] encoder:

```php
// RFC 4648 base32 alphabet; case-insensitive
$base32 = new Base2n(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', FALSE, TRUE, TRUE);
$encoded = $base32->encode('encode this');
// MVXGG33EMUQHI2DJOM======
```


### Constructor Parameters

- <code>integer $bitsPerCharacter</code> **Required**. The number of bits to use for each encoded character; 1–8. The most practical range is 1–6. The encoding's radix is a power of 2: <code>2^$bitsPerCharacter</code>.
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

- <code>string $chars</code> This string specifies the base alphabet. Must be <code>2^$bitsPerCharacter</code> long. Default: <code>0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_</code>

- <code>boolean $caseSensitive</code> To decode in a case-sensitive manner. Default: <code>FALSE</code>

- <code>boolean $rightPadFinalBits</code> How to encode the last character when the bits remaining are fewer than <code>$bitsPerCharacter</code>. When <code>TRUE</code>, the bits to encode are placed in the most significant position of the final group of bits, with the lower bits set to <code>0</code>. When <code>FALSE</code>, the final bits are placed in the least significant position. For [RFC 4648][rfc4648] encodings, <code>$rightPadFinalBits</code>should be <code>TRUE</code>. Default: <code>FALSE</code>

[rfc4648]:  http://tools.ietf.org/html/rfc4648 "RFC 4648: Base16, Base32, Base64"

- <code>boolean $padFinalGroup</code> It's common to encode characters in groups. For example, Base64 (which is based on 6 bits per character) converts 3 raw bytes into 4 encoded characters. If insufficient bytes remain at the end, the final group will be padded with <code>=</code> to complete a group of 4 characters, and the encoded length is always a multiple of 4. Although the information provided by the padding is redundant, some programs rely on it for decoding; Base2n does not. Default: <code>FALSE</code>

- <code>string $padCharacter</code> When <code>$padFinalGroup</code> is <code>TRUE</code>, this is the pad character used. Default: <code>=</code>


### <code>encode()</code> Parameters

- <code>string $rawString</code> **Required**. The data to be encoded.


### <code>decode()</code> Parameters

- <code>string $encodedString</code> **Required**. The string to be decoded.
- <code>boolean $strict</code> When <code>TRUE</code>, <code>NULL</code> will be returned if <code>$encodedString</code> contains an undecodable character. When <code>FALSE</code>, unknown characters are simply ignored. Default: <code>FALSE</code>



Examples
--------

PHP does not provide any Base32 encoding functions. By setting <code>$bitsPerCharacter</code> to 5 and specifying your desired alphabet in <code>$chars</code>, you can handle any variant of Base32:

```php
// RFC 4648 base32 alphabet; case-insensitive
$base32 = new Base2n(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', FALSE, TRUE, TRUE);
$encoded = $base32->encode('encode this');
// MVXGG33EMUQHI2DJOM======
```

```php
// RFC 4648 base32hex alphabet
$base32hex = new Base2n(5, '0123456789ABCDEFGHIJKLMNOPQRSTUV', FALSE, TRUE, TRUE);
$encoded = $base32hex->encode('encode this');
// CLN66RR4CKG78Q39EC======
```


Octal notation:

```php
$octal = new Base2n(3);
$encoded = $octal->encode('encode this');
// 312671433366214510072150322711
```


A convenient way to go back and forth between binary notation and its real binary representation:

```php
$binary = new Base2n(1);
$encoded = $binary->encode('encode this');
// 0110010101101110011000110110111101100100011001010010000001110100011010000110100101110011
$decoded = $binary->decode($encoded);
// encode this
```


PHP uses a proprietary binary-to-text encoding scheme to generate session identifiers from random hash digests. The most efficient way to store these session IDs in a database is to decode them back to their raw hash digests. PHP's encoding scheme is configured with the <code>[session.hash_bits_per_character][phphashbits]</code> php.ini setting. The decoded size depends on the hash function, set with <code>[session.hash_function][phphash]</code> in php.ini.

```php
// session.hash_function = 0
// session.hash_bits_per_character = 5
// 128-bit session ID
$sessionId = 'q3c8n4vqpq11i0vr6ucmafg1h3';
// Decodes to 16 bytes
$phpBase32 = new Base2n(5, '0123456789abcdefghijklmnopqrstuv');
$rawSessionId = $phpBase32->decode($sessionId);
```

```php
// session.hash_function = 1
// session.hash_bits_per_character = 6
// 160-bit session ID
$sessionId = '7Hf91mVc,q-9W1VndNNh3evVN83';
// Decodes to 20 bytes
$phpBase64 = new Base2n(6, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-,');
$rawSessionId = $phpBase64->decode($sessionId);
```

[phphashbits]: http://php.net/manual/en/session.configuration.php#ini.session.hash-bits-per-character "PHP session.hash_bits_per_character"
[phphash]: http://php.net/manual/en/session.configuration.php#ini.session.hash-function "PHP session.hash_function"


Generate random security tokens:
```php
$tokenEncoder = new Base2n(6, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-,');
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

$base128 = new Base2n(7, $base128chars);
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

$high128 = new Base2n(7, $high128chars);
$encoded = $high128->encode('encode this');
```


Let's create an encoding using exclusively non-printable control characters!
```php
// Base-32 non-printable character encoding
$noPrintChars = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"
              . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";

$nonPrintable32 = new Base2n(5, $noPrintChars);
$encoded = $nonPrintable32->encode('encode this');
```


Why not encode data using only whitespace? Here's a base-4 encoding using space, tab, new line, and carriage return:
```php
// Base-4 whitespace encoding
$whitespaceChars = " \t\n\r";

$whitespace = new Base2n(2, $whitespaceChars);
$encoded = $whitespace->encode('encode this');
// "\t\n\t\t\t\n\r\n\t\n \r\t\n\r\r\t\n\t \t\n\t\t \n  \t\r\t \t\n\n \t\n\n\t\t\r \r"

$decoded = $whitespace->decode(
    "\t\n\t\t\t\n\r\n\t\n \r\t\n\r\r\t\n\t \t\n\t\t \n  \t\r\t \t\n\n \t\n\n\t\t\r \r"
);
// encode this
```



Counterexamples
----------------

Base2n is not slow, but it will never outperform an encoding function implemented in C. When one exists, use it instead.


PHP provides the <code>[base64_encode()][base64_encode]</code> and <code>[base64_decode()][base64_decode]</code> functions, and you should always use them for standard Base64. When you need to use a modified alphabet, you can translate the encoded output with <code>[strtr()][strtr]</code> or <code>[str_replace()][str_replace]</code>.

[base64_encode]: http://php.net/base64_encode "PHP base64_encode() Function"
[base64_decode]: http://php.net/base64_decode "PHP base64_decode() Function"
[strtr]: http://php.net/strtr "PHP strtr() Function"
[str_replace]: http://php.net/str_replace "PHP str_replace() Function"

A common variant of Base64 is [modified for URLs and filenames][rfc4648base64url], where <code>+</code> and <code>/</code> are replaced with <code>-</code> and <code>_</code>, and the <code>=</code> padding is omitted. It's better to handle this variant with native PHP functions:

```php
// RFC 4648 base64url with Base2n...
$base64url = new Base2n(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_', TRUE, TRUE, FALSE);
$encoded = $base64url->encode("encode this \xBF\xC2\xBF");
// ZW5jb2RlIHRoaXMgv8K_

// RFC 4648 base64url with native functions...
$encoded = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode("encode this \xBF\xC2\xBF"));
// ZW5jb2RlIHRoaXMgv8K_
```

[rfc4648base64url]: http://tools.ietf.org/html/rfc4648#page-7 "Modified Base64 for URLs"


Native functions get slightly more cumbersome when every position in the alphabet has changed, as seen in this example of [decoding a Bcrypt hash][bmcf]:
```php
// Decode the salt and digest from a Bcrypt hash

$hash = '$2y$14$i5btSOiulHhaPHPbgNUGdObga/GC.AVG/y5HHY1ra7L0C9dpCaw8u';
$encodedSalt    = substr($hash, 7, 22);
$encodedDigest  = substr($hash, 29, 31);

// Using Base2n...
$bcrypt64 = new Base2n(6, './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', TRUE, TRUE);
$rawSalt    = $bcrypt64->decode($encodedSalt);   // 16 bytes
$rawDigest  = $bcrypt64->decode($encodedDigest); // 23 bytes

// Using native functions...
$bcrypt64alphabet = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
$base64alphabet   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
$rawSalt    = base64_decode(strtr($encodedSalt,   $bcrypt64alphabet, $base64alphabet)); // 16 bytes
$rawDigest  = base64_decode(strtr($encodedDigest, $bcrypt64alphabet, $base64alphabet)); // 23 bytes
```

[bmcf]: https://github.com/ademarre/binary-mcf "Binary Modular Crypt Format (BMCF)"

You can encode and decode hexadecimal with <code>[bin2hex()][bin2hex]</code> and <code>[pack()][pack]</code>:

```php
// Hexadecimal with Base2n...
$hexadecimal = new Base2n(4);
$encoded = $hexadecimal->encode('encode this'); // 656e636f64652074686973
$decoded = $hexadecimal->decode($encoded);      // encode this

// It's better to use native functions...
$encoded = bin2hex('encode this'); // 656e636f64652074686973
$decoded = pack('H*', $encoded);   // encode this
// As of PHP 5.4 you can use hex2bin() instead of pack()
```

[bin2hex]:  http://php.net/bin2hex  "PHP bin2hex() Function"
[pack]:     http://php.net/pack     "PHP pack() Function"
