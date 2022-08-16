# my-tools
常用工具类封装，目前已经封装mongodb

## 安装
```shell
composer require kanin/my-tools
```

## KMmongo使用
> 具体每个参数要到方法里面去看

### 实例化
```php 
$ip = '127.0.0.1';
$port = '27017';
$username = "kanin";
$password = "123456";

//实例化
$manager = new \Kanin\MyTools\KMongo($ip, $port, $username, $password);

```

### 插入

```php
//单条插入
$document =['_id'=>1, 'name'=>'小明'];
$res = $manager->setCollection('blog.users')->insert($document);

//多条插入
$document = [
	['_id'=>2, 'name'=>'小明2'],
	['_id'=>3, 'name'=>'小明3'],
	['_id'=>4, 'name'=>'小明4'],
];
$res = $manager->collection('blog.users')->insertAll($document);

```

### 更新

```php
$set = ['name'=>'xiaomin'];
$filter = ['_id'=>1];
$res = $manager->collection('blog.users')->filter($filter)->update($filter, $set);

```


### 查询

```php
//count统计-获取表文档总条数
$count = $manager->collection('blog.users')->count();

//过滤条件
$filter = ['_id' => ['$gt' => 1]];

//单条查询
$data = $manager->collection('blog.users')->limit(1, 1)->filter($filter)->getOne();

//批量查询
$data = $manager->collection('blog.users')->limit(1, 1)->filter($filter)->getAll();

//原始查询 (数据没转换) 上面的两个查询都是基于这个
$data = $manager->collection('blog.users')->limit(1, 1)->filter($filter)->get();

/**
 * where 仿orm操作  类似于mysql的查询组合  _id>2 and (_id=3 or _id=4)
 * 目前支持的操作符
 * =        等于
 * <        小于
 * <=       小于等于
 * >        大于
 * >=       大于等于
 * <>或!=   不等于 
 * in   in查询
 * nin  not in 查询
 * 
 * 展示指定字段
 * field(['email'=>1, 'name'=>1,'_id'=>0])
 * 1是展示 0是排除 
 * 要注意一下，用email=1, name=0是不行的，mongo官方会提示逻辑错误，除了_id可以自由设置0或1，其他字段要么全是1，要么全是0
 * 
 * 排序
 * sort($field, $desc)
 * $desc 1是升序，-1是降序  只能填1或者-1
*/
$collection = 'blog.users';
$find = $manager->collection($collection)
	->where('_id', '>', 2)
	->whereOr('_id', '=', 3)
	->whereOr('_id', '=', 4)
	->field(['name'=>1, 'email'=>1, '_id'=>0])
	->sort('_id', -1)
	->getAll();



```

### 删除
```php  
//过滤条件
$filter = ['_id' => ['$gt' => 1]];
$manager->collection('blog.users')->filter($filter)->delete();
```
