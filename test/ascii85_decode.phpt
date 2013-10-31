--TEST--
ASCII85 decoding with z exception
--FILE--
<?php
use Demarre\Encoding\Scheme;

require_once 'SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/../lib');
$loader->register();

$scheme = Scheme::factory(Scheme::BASE85_ADOBE);
$test = "\0\0\0\0\x86\x4f\xD2";
$testResult = $scheme->encode($test);
echo $testResult;
assert($test == $scheme->decode($testResult));
--EXPECT--
zL/64