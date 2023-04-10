<?php
require 'vendor/autoload.php';

$loyverse = new Pashkevich\Loyverse\Loyverse('75088e447ec14b6396e7a6a254fdd30d');

$getItemsArray = json_encode($loyverse->item('76872535-b3f2-41d6-bef7-62fe39a7dac2'));

echo $getItemsArray;
?>