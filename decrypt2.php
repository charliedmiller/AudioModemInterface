<?php
if (isset($_POST['emessage']) ){
  $message =  $_POST['emessage'];
  echo "This page will respond with a hash of the decrypted message. You just sent ".$message;
}
else{
  echo "You didn't send anything.";
}
?>