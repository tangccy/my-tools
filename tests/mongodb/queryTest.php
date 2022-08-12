<?php
require_once "./vendor/autoload.php";
$ip = '127.0.0.1';
$port = '27017';
$username = "kanin";
$password = "123456";

$manager = new \Kanin\MyTools\KMongo($ip, $port, $username, $password);

//获取表总数
$count = $manager->collection('blog.users')->count();

//查询数据
$filter = ['_id' => ['$gt' => 1]];
$data = $manager->collection('blog.users')->limit(1, 1)->filter($filter)->getOne();
var_dump($count, $data);

