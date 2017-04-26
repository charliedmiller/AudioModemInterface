<?php
if (isset($_POST['username'])){
  //file_put_contents('./testfile.txt',htmlspecialchars($_POST['data']), FILE_USE_INCLUDE_PATH);
    $search = $_POST['username']; 
    // Read from file
    $lines = file('users.txt');
    $found = false;
    $line_number = false;
    $lastline = 1;
    while (list($key, $line) = each($lines) and !$line_number) {
          $line_number = (strpos($line, $search) !== FALSE) ? $key + 1 : $line_number;
          if(strpos($line, $search) !== false) {
              $found = true;            
          }
          $lastline = $lastline + 1;
    }
    
      echo $line_number ."<br>";
    
    if ($found == false){
      echo "User does not exist and has been added.";
      file_put_contents('./users.txt', $search . "\n", FILE_APPEND);
      
      //generate key pair
      $keysize = 1024;
      $ssl = openssl_pkey_new (array('private_key_bits' => $keysize));
      //openssl_pkey_export_to_file($ssl, './private2.pem');
      openssl_pkey_export($ssl, $privkey);
      $pubKey = openssl_pkey_get_details($ssl);
      $pubKey = $pubKey["key"];
      file_put_contents("./public{$lastline}.pem", $pubKey);
      file_put_contents("./private{$lastline}.pem", $privkey);
      
      
    }else{
      //generate testfile
      $myfile = fopen("testfile.txt", "w");
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      fwrite($myfile, $randomString);
      
      $pubkey = file_get_contents("./public{$line_number}.pem", FILE_USE_INCLUDE_PATH);
      $passhash = md5_file('./testfile.txt');
      file_put_contents('./hash.txt',$passhash, FILE_USE_INCLUDE_PATH);
      $test = file_get_contents('./testfile.txt', FILE_USE_INCLUDE_PATH);
      openssl_public_encrypt($test, $encrypted, $pubkey);
      file_put_contents('./encrypted_data_before_encoding.txt', $encrypted, FILE_USE_INCLUDE_PATH);
      $data = base64_encode($encrypted);
      file_put_contents('./encdata.txt', $data, FILE_USE_INCLUDE_PATH);
      
      echo "Hello $search, your public key is " .$pubkey ."<br>";
      echo "Your encrypted challenge is " .$data;
    }
    
  //$message =  $_POST['username'];
  //echo "This page will respond with a challenge. You just sent ".$message;
}
else{
  echo "You didn't send anything.";
}
?>