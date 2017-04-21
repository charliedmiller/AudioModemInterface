<?php
if (isset($_POST['username']) ){
  $message =  $_POST['username'];
  echo "This page will respond with a challenge. You just sent ".$message;
}
else{
  echo "You didn't send anything.";
}
?>