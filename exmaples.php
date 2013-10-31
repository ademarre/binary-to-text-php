<?php
require_once 'test/SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/lib');
$loader->register();

use Demarre\Encoding\Scheme;

// RFC 4648 base32 alphabet; case-insensitive
$base32 = Scheme::factory(Scheme::BASE32);
echo $base32->encode('encode this') . PHP_EOL;
// MVXGG33EMUQHI2DJOM======

// RFC 4648 base32hex alphabet
$base32hex = Scheme::factory(Scheme::BASE32_HEX_RFC_4648);
echo $base32hex->encode('encode this') . PHP_EOL;
// CLN66RR4CKG78Q39EC======

$octal = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::OCT);
echo $octal->encode('encode this') . PHP_EOL;
// 312671433366214510072150322711

$binary = Scheme::factory(Scheme::BIN);
echo $encoded = $binary->encode('encode this') . PHP_EOL;
// 0110010101101110011000110110111101100100011001010010000001110100011010000110100101110011
echo $binary->decode($encoded) . PHP_EOL;
// encode this

// session.hash_function = 0
// session.hash_bits_per_character = 5
// 128-bit session ID
$sessionId = 'q3c8n4vqpq11i0vr6ucmafg1h3';
// Decodes to 16 bytes
$phpBase32 = Demarre\Encoding\Scheme::factory(Demarre\Encoding\Scheme::BASE32_HEX_RFC_4648);
echo strlen($phpBase32->decode($sessionId)) . PHP_EOL;

// session.hash_function = 1
// session.hash_bits_per_character = 6
// 160-bit session ID
$sessionId = '7Hf91mVc,q-9W1VndNNh3evVN83';
// Decodes to 20 bytes
$phpBase64 = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64,
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64_PHP
));
echo strlen($phpBase64->decode($sessionId)) . PHP_EOL;

$tokenEncoder = new Demarre\Encoding\Base2n(6, true, true, true, false, '', array(
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64,
    Demarre\Encoding\Base2n::ALPHABET_SYM_BASE64_PHP
));
$binaryToken = openssl_random_pseudo_bytes(32); // PHP >= 5.3
echo $tokenEncoder->encode($binaryToken) . PHP_EOL;
// Example: U6M132v9FG-AHhBVaQWOg1gjyUi1IogNxuen0i3u3ep
