<?php

namespace Kanin\MyTools;

use Kanin\MyTools\Exception\KMongoException;
use Kanin\MyTools\MongoDB\WhereHandle;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\WriteResult;

class KMongo {

	/**
	 * mongodb实例
	 * @var \MongoDB\Driver\Manager
	 */
	protected $manager;

	/**
	 * 集合 相当于mysql的表
	 * @var string
	 */
	protected $collection = '';

	/**
	 * limit限制
	 * @var array
	 */
	protected $limitOptions = [];

	/**
	 * 搜索条件
	 * @var array
	 */
	protected $filter = [];

	/**
	 * 库名
	 * @var string
	 */
	protected $dbName = '';

	/**
	 * 集合名
	 * @var string
	 */
	protected $collName = '';

	/**
	 * 查询的字段
	 * @var array
	 */
	protected $field = [];

	/**
	 * 排序
	 * @var array
	 */
	protected $sort = [];

	/**
	 * 初始化
	 * @param string $ip
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($ip,  $port,  $username = '',  $password = '') {
		$auth = '';
		if ($username && $password) {
			$auth = "{$username}:$password@";
		}
		$host = "mongodb://{$auth}{$ip}:{$port}";
		$this->manager = new Manager($host);
	}

	/**
	 * 设置集合
	 * @param $collection
	 * @return $this
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function collection($collection): KMongo {
		if (!$this->explodeColl($collection)) {
			throw new KMongoException('集合格式错误');
		}
		$this->collection = $collection;
		return $this;
	}

	/**
	 * 设置查询条件
	 * @param $filter
	 * @return $this
	 */
	public function filter($filter): KMongo {
		$this->filter = $filter;
		return $this;
	}

	/**
	 * @param $document
	 * @return \MongoDB\Driver\WriteResult
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function insert($document) {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		$bulk = new BulkWrite;
		$bulk->insert($document);
		$writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
		return $this->manager->executeBulkWrite($this->collection, $bulk, $writeConcern);
	}

	/**
	 * 批量插入
	 * @param $documentArr
	 * @return \MongoDB\Driver\WriteResult
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function insertAll($documentArr) {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		$bulk = new BulkWrite;
		foreach ($documentArr as $document) {
			$bulk->insert($document);
		}
		return $this->manager->executeBulkWrite($this->collection, $bulk);
	}

	/**
	 * 更新
	 * @param array $data update的更新对象和一些更新的操作符（如$，$inc…）等，也可以理解为sql update查询set子句后面的更新内容
	 * @param array $updateOptions ['multi' => false, 'upsert' => false, ...] 额外的一些选项
	 * |++++ multi 可选，MongoDB默认是false，只更新找到的第一条记录，如果这个参数为true，就把按条件查出来的多条记录全部更新
	 * |++++ upsert 可选。如果不存在update的记录，是否插入objNew：true为插入，默认是false，不插入
	 * |++++ ... 还有一些具体看文档 https://www.php.net/manual/zh/mongodb-driver-bulkwrite.update.php
	 * @return false|\MongoDB\Driver\WriteResult
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function update(array $data, array $updateOptions = []) {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		if (!$this->filter) {
			return false;
		}
		$bulk = new BulkWrite;
		$bulk->update($this->filter, $data, $updateOptions);
		$writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
		return $this->manager->executeBulkWrite($this->collection, $bulk, $writeConcern);
	}

	/**
	 * 查询条数
	 * @param int $skip 起始
	 * @param int $limit 条数
	 * @return $this
	 */
	public function limit(int $skip, int $limit): KMongo {
		$this->limitOptions['skip'] = $skip;
		$this->limitOptions['limit'] = $limit;
		return $this;
	}

	/**
	 * 查询
	 * @param array $options 补充选项
	 * @return false|\MongoDB\Driver\Cursor
	 * @throws \MongoDB\Driver\Exception\Exception
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function get(array $options = []) {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		if (!$this->filter) {
			return false;
		}
		if ($this->limitOptions) {
			$options['limit'] = $this->limitOptions['limit'];
			$options['skip'] = $this->limitOptions['skip'];
		}
		//查询字段
		if (!empty($this->field)) {
			$options['projection'] = $this->field;
		}
		//排序
		if (!empty($this->sort)) {
			$options['sort'] = $this->sort;
		}
		$query = new Query($this->filter, $options);
		return $this->manager->executeQuery($this->collection, $query);
	}

	/**
	 * 查询单条记录
	 * @param array $options
	 * @return array|mixed
	 * @throws false|\MongoDB\Driver\Exception\Exception|\Kanin\MyTools\Exception\KMongoException
	 */
	public function getOne(array $options = []) {
		$data = $this->get($options);
		return $data ? $data->toArray()[0] : [];
	}

	/**
	 * 查询单条记录
	 * @param array $options
	 * @return array
	 * @throws false|\MongoDB\Driver\Exception\Exception|\Kanin\MyTools\Exception\KMongoException
	 */
	public function getAll(array $options = []): array {
		$data = $this->get($options);
		return $data ? $data->toArray() : [];
	}

	/**
	 * 解析出指定mongo数据库和集合
	 * @param $tbl
	 * @return bool
	 */
	public function explodeColl($tbl): bool {
		list($this->dbName, $this->collName) = explode('.', $tbl);
		return $this->dbName && $this->collName;
	}

	/**
	 * @throws \MongoDB\Driver\Exception\Exception
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function count(): int {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		$options = ['count' => $this->collName];
		if ($this->filter) {
			$options['query'] = $this->filter;
		}
		if ($this->limitOptions) {
			$options['skip'] = $this->limitOptions['skip'];
			$options['limit'] = $this->limitOptions['limit'];
		}
		$cmd = new Command($options);
		$cursor = $this->manager->executeCommand($this->dbName, $cmd);
		$ret = current($cursor->toArray());
		return (is_object($ret) && $ret->ok == 1) ? $ret->n : 0;
	}

	/**
	 * 删除文档
	 * @param bool $onlyFirst
	 * @return \MongoDB\Driver\WriteResult
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function delete(bool $onlyFirst = false) {
		if (!$this->explodeColl($this->collection)) {
			throw new KMongoException('集合格式错误');
		}
		$bulk = new BulkWrite;
		$bulk->delete($this->filter, ['limit' => (int)$onlyFirst]);   // limit 为 1 时，删除第一条匹配数据，为 0 时删除所有匹配的数据
		$writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
		return $this->manager->executeBulkWrite($this->collection, $bulk, $writeConcern);
	}

	/**
	 * 查询
	 * @param $field
	 * @param $opt
	 * @param $val
	 * @return \Kanin\MyTools\KMongo
	 */
	public function where($field, $opt, $val): KMongo {
		$whereHandle = new WhereHandle();
		$this->filter[$field] = $whereHandle->handle($opt, $val);
		return $this;
	}

	/**
	 * 或查询
	 * @param $field
	 * @param $opt
	 * @param $val
	 * @return \Kanin\MyTools\KMongo
	 */
	public function whereOr($field, $opt, $val): KMongo {
		$whereHandle = new WhereHandle();
		$this->filter['$or'][] = [$field => $whereHandle->handle($opt, $val)];
		return $this;
	}

	/**
	 * 展示字段
	 * @param array $field 字段：['name'=>1,'email'=>1, '_id'=0]
	 * @return \Kanin\MyTools\KMongo
	 */
	public function field(array $field = []): KMongo {
		$this->field = $field;
		return $this;
	}

	/**
	 * 排序
	 * @param string $field 字段
	 * @param int $desc 排序方式  1是升序  -1是倒序
	 * @return \Kanin\MyTools\KMongo
	 * @throws \Kanin\MyTools\Exception\KMongoException
	 */
	public function sort(string $field, int $desc = 1): KMongo {
		if (!in_array($desc, [1, -1], true)) {
			throw new KMongoException('sort() 的 $desc 排序类型只能是1或者-1');
		}
		$this->sort[$field] =  $desc;
		return $this;
	}
}