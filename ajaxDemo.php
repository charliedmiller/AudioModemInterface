<?php

// $numberToRespond = (int)$_POST['numberToAdd'] + 1;
// echo $numberToRespond;

    if (isset($_POST['numberToAdd']) )
    {
        if (is_numeric($_POST['numberToAdd'])){
        	$numberToRespond = $_POST['numberToAdd'] + 1;
        	echo $numberToRespond;
        }
        else{
        	echo "You didn't send a number";
        } 
    }
    else
    {
        echo "You didn't send anything.";
    }
?>