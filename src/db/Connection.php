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

    /**
     *
     * @param string $configName
     *            配置别名
     */
    public function __construct(string $configName = '')
    {
        $this->pdo = $this->getPdo($configName);
        $this->builder = new QueryBuilder($this->pdo);
    }

    /**
     * 工厂方法
     *
     * @param string $configName
     *            配置名
     * @return Connection
     * @throws \Exception
     */
    private function getPdo(string $configName): \PDO
    {
        if ($configName == '') {
            $configName = Application::$app->config->getValue('DB_CONFIG');
        }
        $configPath = APP_CONFIG_PATH . '/./db/' . $configName . '.php';
        if (! is_file($configPath)) {
            throw new \Exception('数据库配置' . $configName . '不存在');
        }
        $dbConfig = include $configPath;
        $dbConfigMap = new DataMap($dbConfig);
        $dsn = $dbConfigMap->getValue('dsn');
        $username = $dbConfigMap->getValue('username', null);
        $passwd = $dbConfigMap->getValue('passwd', null);
        $options = $dbConfigMap->getValue('options', null);
        return new \PDO($dsn, $username, $passwd, $options);
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

    /**
     * 获取第一条记录
     *
     * @return array
     * @throws \Exception
     */
    public function fetch(): array
    {
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->fetchBySql($sql);
    }

    /**
     * 获取所有记录
     *
     * @return array
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        $sql = $this->builder->buildSql();
        $this->builder = new QueryBuilder($this->pdo);
        return $this->fetchAllBySql($sql);
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
        return $this->execSql($sql);
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
        return $this->execSql($sql);
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
        return $this->execSql($sql);
    }

    /**
     * 通过SQL语句获取一条记录
     *
     * @param string $sql            
     * @return array
     */
    public function fetchBySql(string $sql): array
    {
        $stm = $this->getQueryStatement($sql);
        return $stm->fetch();
    }

    /**
     * 通过SQL语句获取若干条记录
     *
     * @param string $sql            
     * @return array
     */
    public function fetchAllBySql(string $sql): array
    {
        $stm = $this->getQueryStatement($sql);
        return $stm->fetchAll();
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql            
     * @return int 受修改或删除 SQL 语句影响的行数
     * @throws \Exception
     */
    public function execSql(string $sql): int
    {
        $result = $this->pdo->exec($sql);
        if ($result === false) {
            $err = $this->errorInfo();
            throw new \Exception('['.$err[0].']'.$err[2]);
        }
        return $result;
    }

    /**
     * 执行SQL查询
     *
     * @param string $sql            
     * @return \PDOStatement
     * @throws \Exception
     */
    private function getQueryStatement(string $sql): \PDOStatement
    {
        $result = $this->pdo->query($sql);
        if ($result === false) {
            $err = $this->errorInfo();
            throw new \Exception('['.$err[0].']'.$err[2]);
        }
        return $result;
    }

    /**
     * 启动事务
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * 检查是否在一个事务内
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * 提交一个事务
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * 回滚一个事务
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * 获取错误信息
     *
     * @return array
     */
    public function errorInfo(): array
    {
        return $this->pdo->errorInfo();
    }
}