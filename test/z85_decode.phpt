--TEST--
Z85 Decode
--DESCRIPTION--
Tests test vector from C implementation https://github.com/zeromq/rfc/blob/master/src/spec_32.c
--FILE--
<?php
use Demarre\Encoding\Scheme;

require_once 'SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/../lib');
$loader->register();

$scheme = Scheme::factory(Scheme::BASE85_Z);
$test = "\x8E\x0B\xDD\x69\x76\x28\xB9\x1D\x8F\x24\x55\x87\xEE\x95\xC5\xB0\x4D\x48\x96\x3F\x79\x25\x98\x77\xB4\x9C\xD9\x06\x3A\xEA\xD3\xB7";
$testResult = $scheme->encode($test);
echo $testResult;
assert($test == $scheme->decode($testResult));
--EXPECT--
JTKVSB%%)wK0E.X)V>+}o?pNmC{O&4W4b!Ni{Lh6