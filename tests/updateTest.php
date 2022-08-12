<?php
require_once "./vendor/autoload.php";
$ip = '127.0.0.1';
$port = '27017';
$username = "kanin";
$password = "123456";

$manager = new \Kanin\MyTools\KMongo($ip, $port, $username, $password);

$set = ['name'=>'xiaomin'];
$filter = ['_id'=>1];
$res = $manager->collection('blog.users')->filter($filter)->update($filter, $set);
var_dump($res);
