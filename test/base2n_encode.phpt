--TEST--
Base2n encoding (2, 8, 16, 32, 64)
--DESCRIPTION--
Tests vectors from RFC 4648 
--FILE--
<?php
use Demarre\Encoding\Scheme;

require_once 'SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/../lib');
$loader->register();

$schemes = array(
    'BASE64'      => Scheme::factory(Scheme::BASE64),
    'BASE32'      => Scheme::factory(Scheme::BASE32),
    'BASE32-HEX'  => Scheme::factory(Scheme::BASE32_HEX_RFC_4648),
    'BASE16'      => Scheme::factory(Scheme::HEX),
    'BASE8'       => Scheme::factory(Scheme::OCT),
    'BASE2'       => Scheme::factory(Scheme::BIN),
);
$testVector = 'foobar';
foreach ($schemes as $label => $scheme) {
    for ($i = 0; $i <= strlen($testVector); $i++) {
        $test = substr($testVector, 0, $i);
        printf('%s("%s") = "%s"' . PHP_EOL, $label, $test, $scheme->encode($test));
    }
}
--EXPECT--
BASE64("") = ""
BASE64("f") = "Zg=="
BASE64("fo") = "Zm8="
BASE64("foo") = "Zm9v"
BASE64("foob") = "Zm9vYg=="
BASE64("fooba") = "Zm9vYmE="
BASE64("foobar") = "Zm9vYmFy"
BASE32("") = ""
BASE32("f") = "MY======"
BASE32("fo") = "MZXQ===="
BASE32("foo") = "MZXW6==="
BASE32("foob") = "MZXW6YQ="
BASE32("fooba") = "MZXW6YTB"
BASE32("foobar") = "MZXW6YTBOI======"
BASE32-HEX("") = ""
BASE32-HEX("f") = "CO======"
BASE32-HEX("fo") = "CPNG===="
BASE32-HEX("foo") = "CPNMU==="
BASE32-HEX("foob") = "CPNMUOG="
BASE32-HEX("fooba") = "CPNMUOJ1"
BASE32-HEX("foobar") = "CPNMUOJ1E8======"
BASE16("") = ""
BASE16("f") = "66"
BASE16("fo") = "666f"
BASE16("foo") = "666f6f"
BASE16("foob") = "666f6f62"
BASE16("fooba") = "666f6f6261"
BASE16("foobar") = "666f6f626172"
BASE8("") = ""
BASE8("f") = "312"
BASE8("fo") = "314671"
BASE8("foo") = "31467557"
BASE8("foob") = "31467557302"
BASE8("fooba") = "31467557304601"
BASE8("foobar") = "3146755730460562"
BASE2("") = ""
BASE2("f") = "01100110"
BASE2("fo") = "0110011001101111"
BASE2("foo") = "011001100110111101101111"
BASE2("foob") = "01100110011011110110111101100010"
BASE2("fooba") = "0110011001101111011011110110001001100001"
BASE2("foobar") = "011001100110111101101111011000100110000101110010"