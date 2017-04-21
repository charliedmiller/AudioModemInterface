<?php
if (isset($_POST['decryptedHash']) ){
  $message =  $_POST['decryptedHash'];
  echo "This page will respond either yes or no. You just sent ".$message;
}
else{
  echo "You didn't send anything.";
}
?>