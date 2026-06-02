<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "smart_canteen"
);

if(!$conn){
    die("Database Connection Failed");
}

?>