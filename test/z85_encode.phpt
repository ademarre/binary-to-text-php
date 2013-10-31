--TEST--
Z85 Encode
--DESCRIPTION--
Tests test vector from http://rfc.zeromq.org/spec:32
--FILE--
<?php
use Demarre\Encoding\Scheme;

require_once 'SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/../lib');
$loader->register();

$scheme = Scheme::factory(Scheme::BASE85_Z);
$test = "\x86\x4F\xD2\x6F\xB5\x59\xF7\x5B";
echo $scheme->encode($test);
--EXPECT--
HelloWorld