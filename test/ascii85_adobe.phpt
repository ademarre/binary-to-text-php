--TEST--
ASCII85 Adobe encode
--DESCRIPTION--
Uses test vector pulled from example at http://en.wikipedia.org/wiki/Ascii85#Example
--FILE--
<?php
use Demarre\Encoding\Scheme;

require_once 'SplClassLoader.php';
$loader = new SplClassLoader('Demarre', __DIR__ . '/../lib');
$loader->register();

$test = 'Man is distinguished, not only by his reason, but by this singular passion from other animals, which is a lust of the mind, that by a perseverance of delight in the continued and indefatigable generation of knowledge, exceeds the short vehemence of any carnal pleasure.';
$scheme = Scheme::factory(Scheme::BASE85_ADOBE);
echo $scheme->format($scheme->encode($test), 75, Scheme::BASE85_ADOBE);
--EXPECT--
<~9jqo^BlbD-BleB1DJ+*+F(f,q/0JhKF<GL>Cj@.4Gp$d7F!,L7@<6@)/0JDEF<G%<+EV:2F!,
O<DJ+*.@<*K0@<6L(Df-\0Ec5e;DffZ(EZee.Bl.9pF"AGXBPCsi+DGm>@3BB/F*&OCAfu2/AKY
i(DIb:@FD,*)+C]U=@3BN#EcYf8ATD3s@q?d$AftVqCh[NqF<G:8+EV:.+Cf>-FD5W8ARlolDIa
l(DId<j@<?3r@:F%a+D58'ATD4$Bl@l3De:,-DJs`8ARoFb/0JMK@qB4^F!,R<AKZ&-DfTqBG%G
>uD.RTpAKYo'+CT/5+Cei#DII?(E,9)oF*2M7/c~>