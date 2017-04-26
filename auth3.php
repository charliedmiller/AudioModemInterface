<?php
if (isset($_POST['emessage']) ){
  $message =  $_POST['emessage'];
  echo "This page will respond with a hash of the decrypted message. You just sent ".$message;
  $original_hash = file_get_contents('./hash.txt', FILE_USE_INCLUDE_PATH);
  if($message == $original_hash)
  {
    echo " Authentication Successful";
  }
  else{
    echo " Authentication Unsuccessful";
  }
}
else{
  echo " You didn't send anything.";
}

?>