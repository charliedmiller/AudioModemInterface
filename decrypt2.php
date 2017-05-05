<?php
if (isset($_POST['decryptedHash']) ){
  $search = $_POST['username']; 
  $lines = file('users.txt');
  while (list($key, $line) = each($lines) and !$line_number) {
          $line_number = (strpos($line, $search) !== FALSE) ? $key + 1 : $line_number;
  }
  
  echo $line_number;
  
  $dec = base64_decode($_POST['decryptedHash']);
  file_put_contents('./dec.txt', $dec, FILE_USE_INCLUDE_PATH);
  //$pre_encrypt = file_get_contents('./encrypted_data_before_encoding.txt');
  //$priv_key = openssl_get_privatekey('./private.pem');
 
  //system('openssl rsautl -decrypt -inkey private.pem -in dec.txt -out decrypt.txt');
  //$decrypted = file_get_contents('./decrypt.txt');
  
  
  $fp=fopen("./private{$line_number}.pem","r");
  $priv_key=fread($fp,8192);
  fclose($fp); 
  
  $res = openssl_pkey_get_private($priv_key);
  
  openssl_private_decrypt($dec, $decrypted, $res);
  
  $decrypted_hash = md5($decrypted);
  
  file_put_contents('./decdata.txt', $decrypted_hash, FILE_USE_INCLUDE_PATH);
  
  echo "This is the decryption" ."<br> " .$decrypted_hash;
  
}
else{
  echo "You didn't send anything.";
}
?>