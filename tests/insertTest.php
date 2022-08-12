<?php
require_once "./vendor/autoload.php";
$ip = '127.0.0.1';
$port = '27017';
$username = "kanin";
$password = "123456";

$manager = new \Kanin\MyTools\KMongo($ip, $port, $username, $password);

////单条插入
//$document =['_id'=>1, 'name'=>'小明'];
//$res = $manager->setCollection('blog.users')->insert($document);
//var_dump($res);


$document = [
	['_id'=>2, 'name'=>'小明2'],
	['_id'=>3, 'name'=>'小明3'],
	['_id'=>4, 'name'=>'小明4'],
];
//多条插入
$res = $manager->collection('blog.users')->insertAll($document);
var_dump($res);
