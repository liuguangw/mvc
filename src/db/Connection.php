<?php
namespace liuguang\mvc\db;

use liuguang\mvc\Application;
use liuguang\mvc\data\DataMap;

/**
 * 数据库连接类
 *
 * @author liuguang
 *        
 */
class Connection
{

    /**
     * 数据库连接对象
     *
     * @var \PDO
     */
    private $pdo;

    /**
     *
     * @var QueryBuilder
     */
    private $builder;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->builder = new QueryBuilder($pdo);
    }

    /**
     * 工厂方法
     *
     * @param string $configName
     *            配置名
     * @return Connection
     * @throws \Exception
     */
    public static function getInstance(string $configName = ''): Connection
    {
        if ($configName == '') {
            $configName = Application::$app->config->getValue('DB_CONFIG');
        }
        $configPath = APP_CONFIG_PATH . '/./' . $configName . '.php';
        if (! is_file($configPath)) {
            throw new \Exception('数据库配置' . $configName . '不存在');
        }
        $dbConfig = include $configPath;
        $dbConfigMap = new DataMap($dbConfig);
        $dsn = $dbConfigMap->getValue('dsn');
        $username = $dbConfigMap->getValue('username', null);
        $passwd = $dbConfigMap->getValue('passwd', null);
        $options = $dbConfigMap->getValue('options', null);
        $pdo = new \PDO($dsn, $username, $passwd, $options);
        return new static($pdo);
    }

    /**
     *
     * @param string[] $fields            
     * @return Connection
     */
    public function select(array $fields): Connection
    {
        $this->builder->select($fields);
        return $this;
    }

    /**
     *
     * @param string $tableName
     *            数据表名
     * @return Connection
     */
    public function table(string $tableName): Connection
    {
        $this->builder->table($tableName);
        return $this;
    }

    public function join(string $joinTable, string $joinTableField, string $tableField): Connection
    {
        $this->builder->join($joinTable, $joinTableField, $tableField);
        return $this;
    }

    public function leftJoin(string $joinTable, string $joinTableField, string $tableField): Connection
    {
        $this->builder->leftJoin($joinTable, $joinTableField, $tableField);
        return $this;
    }

    public function rightJoin(string $joinTable, string $joinTableField, string $tableField): Connection
    {
        $this->builder->rightJoin($joinTable, $joinTableField, $tableField);
        return $this;
    }

    public function innerJoin(string $joinTable, string $joinTableField, string $tableField): Connection
    {
        $this->builder->innerJoin($joinTable, $joinTableField, $tableField);
        return $this;
    }

    public function fullJoin(string $joinTable, string $joinTableField, string $tableField): Connection
    {
        $this->builder->fullJoin($joinTable, $joinTableField, $tableField);
        return $this;
    }

    public function where($condition): Connection
    {
        $this->builder->where($condition);
        return $this;
    }

    public function andWhere($condition): Connection
    {
        $this->builder->andWhere($condition);
        return $this;
    }

    public function orWhere($condition): Connection
    {
        $this->builder->orWhere($condition);
        return $this;
    }

    public function orderBy($fields): Connection
    {
        $this->builder->orderBy($fields);
        return $this;
    }

    public function groupBy($groupField): Connection
    {
        $this->builder->groupBy($groupField);
        return $this;
    }

    public function offset($offset): Connection
    {
        $this->builder->offset($offset);
        return $this;
    }

    public function limit($limit): Connection
    {
        $this->builder->limit($limit);
        return $this;
    }

    private function getQueryStatement(): \PDOStatement
    {
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->pdo->query($sql);
    }

    /**
     * 获取第一条记录
     *
     * @return array
     * @throws \Exception
     */
    public function fetch(): array
    {
        $stm = $this->getQueryStatement();
        return $stm->fetch();
    }

    /**
     * 获取所有记录
     *
     * @return array
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        $stm = $this->getQueryStatement();
        return $stm->fetchAll();
    }

    /**
     * 更新表
     *
     * @param array $data            
     * @return int
     */
    public function update(array $data): int
    {
        $this->builder->setUpdateData($data);
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->pdo->exec($sql);
    }

    /**
     * 插入表数据
     *
     * @param array $data            
     * @return int
     */
    public function insert(array $data): int
    {
        $this->builder->setInsertData($data);
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->pdo->exec($sql);
    }

    /**
     * 删除表数据
     *
     * @return int
     */
    public function delete(): int
    {
        $this->builder->setDeleteAction();
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->pdo->exec($sql);
    }
}