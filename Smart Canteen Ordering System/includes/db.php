<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "version6_canteen"
);

if(!$conn){
    die("Database Connection Failed");
}

?>