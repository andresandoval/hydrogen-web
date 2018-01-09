<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 28/11/2017
 * Time: 23:26
 */


$arr = ["nine" => 1, "dos" => 2, "cuat" => 4, "nine" => 7, "nine" => 9];

var_dump($arr);

$arr2 = array_filter($arr, function($v){return $v > 4;});

var_dump($arr2);
