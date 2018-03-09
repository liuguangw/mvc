<?php
use PHPUnit\Framework\TestCase;
use liuguang\mvc\db\QueryBuilder;
use liuguang\mvc\data\RawString;

class QueryBuilderTest extends TestCase
{

    private function getBuilder()
    {
        return new QueryBuilder(new \PDO('sqlite::memory:'));
    }

    public function testCommon()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->select([
            'username',
            'pass',
            'nickname as c'
        ]);
        $builder->where([
            'tag' => 1,
            'p1' => null,
            'k1' => 33
        ]);
        $builder->andWhere([
            'or',
            [
                '<',
                'p2',
                3
            ],
            [
                '>',
                'p2',
                6
            ]
        ]);
        $this->assertEquals('SELECT username, pass, nickname as c FROM user WHERE (tag = 1 AND p1 IS NULL AND k1 = 33) AND (p2 < 3 OR p2 > 6)', $builder->buildSql());
    }

    public function testQuote()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->select([
            'username',
            'nickname as c'
        ]);
        $builder->where([
            'tag' => 1,
            's' => 'sks\'k'
        ]);
        $this->assertEquals("SELECT username, nickname as c FROM user WHERE tag = 1 AND s = 'sks''k'", $builder->buildSql());
    }

    public function testLike()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->select([
            'username',
            'nickname as c'
        ]);
        $builder->where([
            'like',
            'a1',
            'hello'
        ]);
        $builder->andWhere([
            'like',
            'b1',
            '%30'
        ]);
        $this->assertEquals("SELECT username, nickname as c FROM user WHERE (a1 LIKE '%hello%') AND (b1 LIKE '%%%30%')", $builder->buildSql());
    }

    public function testIn()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->select([
            'username',
            'nickname as c'
        ]);
        $builder->where([
            'a' => 3,
            'b' => [
                'hello',
                'world',
                'this\'s a test'
            ]
        ]);
        $this->assertEquals("SELECT username, nickname as c FROM user WHERE a = 3 AND b IN ('hello', 'world', 'this''s a test')", $builder->buildSql());
    }

    public function testLimit()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->select([
            'uid',
            'nickname'
        ]);
        $builder->orderBy([
            'uid' => SORT_ASC,
            'nickname' => SORT_DESC
        ]);
        $builder->offset(5);
        $builder->limit(10);
        $this->assertEquals('SELECT uid, nickname FROM user ORDER BY uid ASC, nickname DESC OFFSET 5 LIMIT 10', $builder->buildSql());
    }

    public function testUpdate()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->setUpdateData([
            'nickname' => 'test',
            'age' => 18,
            'address' => null,
            's1' => new RawString('s1+3')
        ]);
        $builder->where([
            '>',
            'uid',
            1
        ]);
        $this->assertEquals('UPDATE user SET nickname=\'test\', age=18, address=NULL, s1=s1+3 WHERE uid > 1', $builder->buildSql());
    }

    public function testInsert1()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->setInsertData([
            'nickname' => 'test',
            'age' => 18,
            'address' => null,
            's1' => 666
        ]);
        $this->assertEquals("INSERT INTO user (nickname, age, address, s1) VALUES ('test', 18, NULL, 666)", $builder->buildSql());
    }

    public function testInsert2()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->setInsertData([
            [
                'nickname' => 'test1',
                'age' => 11,
                'address' => null,
                's1' => 666
            ],
            [
                'nickname' => 'test2',
                'age' => 12,
                'address' => null,
                's1' => 666
            ],
            [
                'nickname' => 'test3',
                'age' => 13,
                'address' => null,
                's1' => 666
            ],
            [
                'nickname' => 'test4',
                'age' => 14,
                'address' => null,
                's1' => 666
            ]
        ]);
        $this->assertEquals("INSERT INTO user (nickname, age, address, s1) VALUES ('test1', 11, NULL, 666), ('test2', 12, NULL, 666), ('test3', 13, NULL, 666), ('test4', 14, NULL, 666)", $builder->buildSql());
    }

    public function testDelete()
    {
        $builder = $this->getBuilder();
        $builder->table('user');
        $builder->setDeleteAction();
        $builder->where([
            '>',
            'uid',
            1
        ]);
        $this->assertEquals('DELETE FROM user WHERE uid > 1', $builder->buildSql());
    }
}

