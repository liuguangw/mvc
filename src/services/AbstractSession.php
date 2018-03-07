<?php
namespace liuguang\mvc\services;

use liuguang\mvc\Application;
use liuguang\mvc\data\ObserverData;

/**
 * session接口
 *
 * @author liuguang
 *        
 */
abstract class AbstractSession
{

    /**
     *
     * @var ObserverData
     */
    protected $sessionData;

    protected $oldAttributes = [
        'lifeTime' => 2592000,
        'uid' => 0,
        'session_id' => ''
    ];

    protected $newAttributes = [];

    /**
     *
     * @var bool
     */
    protected $commited = false;

    /**
     * 是否已经销毁
     *
     * @var bool
     */
    protected $destroyed = false;

    protected $inputSessionKey = '';

    /**
     * 判断会话标识是否存在
     *
     * @param string $sessionId
     *            会话标识
     * @return bool
     */
    protected abstract function sessionIdExists(string $sessionId): bool;

    /**
     * 判断会话标识格式是否有效
     *
     * @param string $sessionId
     *            会话标识
     * @return bool
     */
    protected function isValidSessionId(string $sessionId): bool
    {
        return preg_match('/^[0-9a-f]{32}$/', $sessionId) != 0;
    }

    protected function makeSessionId(): string
    {
        return md5(time() . md5(rand(1, 1000)) . md5(uniqid()));
    }

    protected function loadSessionId(): void
    {
        $this->inputSessionKey = Application::$app->config->getValue('INPUT_SESSION_KEY', 'session');
        $sessionId = '';
        if (isset($_COOKIE[$this->inputSessionKey])) {
            $sessionId = $_COOKIE[$this->inputSessionKey];
        }
        if ($this->isValidSessionId($sessionId) && $this->sessionIdExists($sessionId)) {
            $this->oldAttributes['session_id'] = $sessionId;
        } else {
            // 随机生成新的会话标识
            do {
                $sessionId = $this->makeSessionId();
            } while ($this->sessionIdExists($sessionId));
            $this->newAttributes['session_id'] = $sessionId;
        }
    }

    /**
     * 判断是否为全新的会话
     *
     * @return bool
     */
    public function isNew(): bool
    {
        if (isset($this->newAttributes['session_id'])) {
            if ($this->oldAttributes['session_id'] == '') {
                return true;
            }
            return ($this->newAttributes['session_id'] == $this->oldAttributes['session_id']);
        }
        return false;
    }

    /**
     * 发送cookie
     *
     * @return void
     */
    protected function sendCookie(): void
    {
        $path = Application::$app->appContext . '/';
        setcookie($this->inputSessionKey, $this->getSessionId(), time() + $this->getLifeTime(), $path, null, false, true);
    }

    /**
     * 会话数据提交
     *
     * @return void
     */
    public abstract function commit(): void;

    /**
     * 判断会话数据是否已经提交
     *
     * @return bool
     */
    public function hasCommit(): bool
    {
        return $this->commited;
    }

    /**
     * 获取会话数据
     *
     * @return ObserverData
     */
    public function getSessionData(): ObserverData
    {
        return $this->sessionData;
    }

    /**
     * 获取会话对应的用户id
     *
     * @return int
     */
    public function getUid(): int
    {
        if (isset($this->newAttributes['uid'])) {
            return $this->newAttributes['uid'];
        } else {
            return $this->oldAttributes['uid'];
        }
    }

    /**
     * 设置会话的用户id
     *
     * @param int $uid
     *            用户id
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->newAttributes['uid'] = $uid;
    }

    /**
     * 设置生命周期
     *
     * @param int $lifeTime
     *            有效期单位秒
     * @return void
     */
    public function setLifeTime(int $lifeTime): void
    {
        $this->newAttributes['lifeTime'] = $lifeTime;
    }

    /**
     * 获取生命周期
     *
     * @return void
     */
    public function getLifeTime(): int
    {
        if (isset($this->newAttributes['lifeTime'])) {
            return $this->newAttributes['lifeTime'];
        } else {
            return $this->oldAttributes['lifeTime'];
        }
    }

    /**
     * 获取会话唯一标识
     *
     * @return string
     */
    public function getSessionId(): string
    {
        if (isset($this->newAttributes['session_id'])) {
            return $this->newAttributes['session_id'];
        } else {
            return $this->oldAttributes['session_id'];
        }
    }

    /**
     * 设置会话唯一标识
     *
     * @param string $sessionId            
     * @return void
     * @throws \Exception
     */
    public function setSessionId(string $sessionId): void
    {
        if ($this->isValidSessionId($sessionId)) {
            $this->newAttributes['session_id'] = $sessionId;
        } else {
            throw new \Exception('session标识符格式错误');
        }
    }

    /**
     * 销毁会话
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->destroyed = true;
    }
}