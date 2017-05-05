<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

//echo "hello";

if($_GET['getBits'] == 1){
	$all_bits_array = file_get_contents('./tx_bits.txt', FILE_USE_INCLUDE_PATH);
	//$all_bits_array = 5
	echo $all_bits_array;
}
elseif (isset($_POST['sentBits'])) {
	$set_bits = $_POST['sentBits'];
	file_put_contents('./tx_bits.txt',$set_bits );
	echo "Put the bits in";
}
else{
	echo "You requested this page without anything relevant";
}


?>