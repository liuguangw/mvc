<?php
namespace liuguang\mvc\db;

/**
 * 语句构造器
 *
 * @author liuguang
 *        
 */
class QueryBuilder
{

    /**
     *
     * @var \PDO
     */
    private $pdo;

    /**
     *
     * @var array
     */
    private $fields;

    /**
     *
     * @var string
     */
    private $tableName;

    /**
     *
     * @var array
     */
    private $joinArray;

    /**
     *
     * @var string
     */
    private $whereStr;

    /**
     *
     * @var array
     */
    private $orderByFields;

    /**
     *
     * @var string
     */
    private $groupField;

    /**
     *
     * @var int
     */
    private $offsetVal = 0;

    /**
     *
     * @var int
     */
    private $limitVal = 0;

    /**
     *
     * @var array
     */
    private $updateData = [];

    /**
     *
     * @var array
     */
    private $insertData = [];

    /**
     *
     * @var bool
     */
    private $isDelete = false;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     *
     * @param string[] $fields            
     * @return void
     */
    public function select(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     *
     * @param string $tableName
     *            数据表名
     * @return void
     */
    public function table(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    private function processJoin(string $joinType, string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->joinArray[] = [
            $joinType,
            $joinTable,
            $joinTableField,
            $tableField
        ];
    }

    public function join(string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->processJoin('join', $joinTable, $joinTableField, $tableField);
    }

    public function leftJoin(string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->processJoin('left join', $joinTable, $joinTableField, $tableField);
    }

    public function rightJoin(string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->processJoin('right join', $joinTable, $joinTableField, $tableField);
    }

    public function innerJoin(string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->processJoin('inner join', $joinTable, $joinTableField, $tableField);
    }

    public function fullJoin(string $joinTable, string $joinTableField, string $tableField): void
    {
        $this->processJoin('full join', $joinTable, $joinTableField, $tableField);
    }

    public function buildWhereStr(): string
    {
        return $this->whereStr;
    }

    /**
     *
     * @param string|array|callable $condition
     *            查询条件
     * @return void
     */
    public function where($condition): void
    {
        if (is_string($condition) || is_array($condition)) {
            $this->whereStr = $this->buildCondition($condition);
        } elseif (is_callable($condition)) {
            $newBuilder = new static($this->pdo);
            call_user_func($condition, $newBuilder);
            $this->whereStr = $newBuilder->whereStr;
        }
    }

    public function andWhere($condition): void
    {
        if (empty($this->whereStr)) {
            $this->where($condition);
            return;
        }
        if (is_string($condition) || is_array($condition)) {
            $this->whereStr = '(' . $this->whereStr . ') AND (' . $this->buildCondition($condition) . ')';
        } elseif (is_callable($condition)) {
            $newBuilder = new static($this->pdo);
            call_user_func($condition, $newBuilder);
            $this->whereStr = '(' . $this->whereStr . ') AND (' . $newBuilder->whereStr . ')';
        }
    }

    public function orWhere($condition): void
    {
        if (empty($this->whereStr)) {
            $this->where($condition);
            return;
        }
        if (is_string($condition) || is_array($condition)) {
            $this->whereStr = '(' . $this->whereStr . ') OR (' . $this->buildCondition($condition) . ')';
        } elseif (is_callable($condition)) {
            $newBuilder = new static($this->pdo);
            call_user_func($condition, $newBuilder);
            $this->whereStr = '(' . $this->whereStr . ') OR (' . $newBuilder->whereStr . ')';
        }
    }

    public function orderBy($fields): void
    {
        $this->orderByFields = $fields;
    }

    public function groupBy($groupField): void
    {
        $this->groupField = $groupField;
    }

    /**
     *
     * @param mixed $value            
     * @return string
     */
    private function formatValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        } elseif (is_string($value)) {
            return $this->pdo->quote($value);
        } elseif (is_array($value)) {
            if (isset($value[0])) {
                $result = [];
                if (is_string($value[0])) {
                    foreach ($value as $v) {
                        $result[] = $this->pdo->quote($v);
                    }
                } else {
                    foreach ($value as $v) {
                        $result[] = strval($v);
                    }
                }
                return '(' . implode(', ', $result) . ')';
            } else {
                return '()';
            }
        } else {
            return strval($value);
        }
    }

    /**
     *
     * @param array|string $condition            
     * @return string
     */
    private function buildCondition($condition): string
    {
        if (empty($condition)) {
            return '';
        }
        if (is_string($condition)) {
            return $condition;
        }
        reset($condition);
        if (is_int(key($condition))) {
            $firstValue = strtoupper(current($condition));
            if (($firstValue == 'AND') || ($firstValue == 'OR')) {
                return $this->buildRelationCondition($firstValue, array_slice($condition, 1));
            } elseif (in_array($firstValue, [
                '>',
                '<',
                '=',
                '!=',
                '<>',
                '>=',
                '<=',
                'LIKE',
                'IN',
                'NOT IN'
            ])) {
                if ($condition[2] === null) {
                    if ($firstValue == '=') {
                        return $condition[1] . ' IS NULL';
                    } elseif ($firstValue == '!=') {
                        return $condition[1] . ' IS NOT NULL';
                    }
                }
                if ($firstValue == 'LIKE') {
                    return $condition[1] . ' LIKE ' . $this->formatValue('%' . str_replace('%', '%%', $condition[2]) . '%');
                }
                return $condition[1] . ' ' . $firstValue . ' ' . $this->formatValue($condition[2]);
            } else {
                return $firstValue;
            }
        }
        //
        
        $result = [];
        foreach ($condition as $cKey => $cValue) {
            if ($cValue === null) {
                $result[] = $cKey . ' IS NULL';
            } elseif (is_array($cValue)) {
                $result[] = $cKey . ' IN ' . $this->formatValue($cValue);
            } else {
                $result[] = $cKey . ' = ' . $this->formatValue($cValue);
            }
        }
        return implode(' AND ', $result);
        // ['a=1'] a=1
        // ['a'=>1] a=1
        // [ 'c' => [1, 2, 3] ] c in (1, 2, 3)
        // ['a'=>1,'b'=>2] a=1 and b=2
        // ['>','a',1] a>1
    }

    private function buildRelationCondition(string $relation, array $conditionArr): string
    {
        $result = [];
        foreach ($conditionArr as $condition) {
            $result[] = $this->buildCondition($condition);
        }
        return implode(' ' . $relation . ' ', $result);
    }

    public function offset(int $offset): void
    {
        $this->offsetVal = $offset;
    }

    public function limit(int $limit): void
    {
        $this->limitVal = $limit;
    }

    public function setUpdateData(array $updateData): void
    {
        $this->updateData = $updateData;
    }

    public function setInsertData(array $insertData): void
    {
        $this->insertData = $insertData;
    }

    public function setDeleteAction(): void
    {
        $this->isDelete = true;
    }

    private function buildSelectSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->fields) . ' FROM ' . $this->tableName;
        if (! empty($this->joinArray)) {
            foreach ($this->joinArray as $joinInfo) {
                $sql .= (' ' . $joinInfo[0] . ' ' . $joinInfo[1] . ' ON ' . $joinInfo[1] . '.' . $joinInfo[2] . '=' . $this->tableName . '.' . $joinInfo[3]);
            }
        }
        if (! empty($this->whereStr)) {
            $sql .= (' WHERE ' . $this->buildWhereStr());
        }
        if (! empty($this->groupField)) {
            $sql .= (' GROUP BY ' . $this->groupField);
        }
        if (! empty($this->orderByFields)) {
            $orderInfo = [];
            foreach ($this->orderByFields as $fieldName => $sortType) {
                if ($sortType == SORT_ASC) {
                    $orderInfo[] = $fieldName . ' ASC';
                } else {
                    $orderInfo[] = $fieldName . ' DESC';
                }
            }
            $sql .= (' ORDER BY ' . implode(', ', $orderInfo));
        }
        if ($this->limitVal > 0) {
            $sql .= (' OFFSET ' . $this->offsetVal . ' LIMIT ' . $this->limitVal);
        }
        return $sql;
    }

    /**
     * 构建update语句
     *
     * @return string
     */
    private function buildUpdateSql(): string
    {
        $sql = 'UPDATE ' . $this->tableName . ' SET ';
        $fieldArr = [];
        foreach ($this->updateData as $key => $value) {
            if ($value === null) {
                $fieldArr[] = ($key . '=NULL');
            } elseif (is_string($value)) {
                $fieldArr[] = ($key . '=' . $this->pdo->quote($value));
            } else {
                $fieldArr[] = ($key . '=' . strval($value));
            }
        }
        $sql .= implode(', ', $fieldArr);
        if (! empty($this->whereStr)) {
            $sql .= (' WHERE ' . $this->buildWhereStr());
        }
        return $sql;
    }

    private function formatInsertValue(array $insertArray): string
    {
        $result = [];
        foreach ($insertArray as $value) {
            if ($value === null) {
                $result[] = 'NULL';
            } elseif (is_string($value)) {
                $result[] = $this->pdo->quote($value);
            } else {
                $result[] = strval($value);
            }
        }
        return '(' . implode(', ', $result) . ')';
    }

    private function buildInsertSql(): string
    {
        $sql = 'INSERT INTO ' . $this->tableName;
        reset($this->insertData);
        $firstElement = current($this->insertData);
        // 键名数组
        if (is_array($firstElement)) {
            $insertKeys = array_keys($firstElement);
            $valueStrArr = [];
            foreach ($this->insertData as $value) {
                $valueStrArr[] = $this->formatInsertValue($value);
            }
            $valueStr = implode(', ', $valueStrArr);
        } else {
            $insertKeys = array_keys($this->insertData);
            $valueStr = $this->formatInsertValue($this->insertData);
        }
        $sql .= (' (' . implode(', ', $insertKeys) . ') VALUES ' . $valueStr);
        return $sql;
    }

    private function buildDeleteSql(): string
    {
        $sql = 'DELETE FROM ' . $this->tableName;
        if (! empty($this->whereStr)) {
            $sql .= (' WHERE ' . $this->buildWhereStr());
        }
        return $sql;
    }

    public function buildSql(): string
    {
        $sql = '';
        if (! empty($this->fields)) {
            $sql = $this->buildSelectSql();
        } elseif (! empty($this->updateData)) {
            $sql = $this->buildUpdateSql();
        } elseif (! empty($this->insertData)) {
            $sql = $this->buildInsertSql();
        } elseif ($this->isDelete) {
            $sql = $this->buildDeleteSql();
        }
        return $sql;
    }
}