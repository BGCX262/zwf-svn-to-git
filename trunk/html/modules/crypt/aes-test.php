<?php

include_once('aes.php');

/* Iv test */
$text = '1111111111111111';
$iv = '2a3s4d5f6g7h8j9l';

echo Aes128::encrypt($text, Aes128::DEFAULT_SALT, $iv), "<br/>",
	Aes128::decrypt(
	Aes128::encrypt($text, Aes128::DEFAULT_SALT, $iv),
	Aes128::DEFAULT_SALT, $iv
);

die();

//*/





/* Performance test */
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 1000;
$text = '1111111111111111';
$s0 = 0;

echo "<h3>Static method</h3>",
     "Average from $iterations iterations<br/>\n";


$s = microtime(true);
for ($i=0; $i<$iterations; ++$i ) {
	Aes128::encrypt($text);
	if ( $i == 0 ) { $s0 = microtime(true) - $s; }
}
echo "Encrypting: ", ((microtime(true) - $s) / $iterations), " (ms) avg; first run: $s0 (ms)<br/>\n";


$text = Aes128::encrypt($text);
$s = microtime(true);
for ($i=0; $i<$iterations; ++$i ) {
	Aes128::decrypt($text);
	if ( $i == 0 ) { $s0 = microtime(true) - $s; }
}
echo "Decrypting: ", ((microtime(true) - $s) / $iterations), " (ms) avg; first run: $s0 (ms)<br/>\n";

echo "<h3>Ouput testing</h3>\n";


echo Aes128::decrypt( Aes128::encrypt('1111111111111111') ), "<br/>";
echo Aes128::encrypt('1111111111111111'), "<br/>";


echo var_dump(Aes128::decrypt( Aes128::encrypt('6451') ));echo "<br/>";
echo Aes128::encrypt('6451');

//*/

