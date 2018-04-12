<?php
namespace liuguang\mvc\services;

use liuguang\mvc\data\ObserverData;
use liuguang\mvc\Application;
use liuguang\mvc\event\EventDispatcher;
use liuguang\mvc\event\common\UserActivityEvent;

class SessionHandler
{
    use EventDispatcher;

    /**
     *
     * @var ObserverData
     */
    protected $sessionAttributes;

    /**
     *
     * @var ObserverData
     */
    protected $sessionData;

    /**
     * 是否为新会话
     *
     * @var bool
     */
    protected $isNew;

    /**
     * 是否已经销毁
     *
     * @var bool
     */
    protected $destroyed;

    /**
     *
     * @var string
     */
    protected $cookieName;

    /**
     * session生命周期
     *
     * @var bool
     */
    protected $sessionLife;

    /**
     *
     * @var bool
     */
    protected $commited;

    /**
     * 从客户端获取会话标识
     *
     * @return string
     */
    protected function loadClientSessionId(): string
    {
        $sessionId = '';
        if (isset($_COOKIE[$this->cookieName])) {
            $sessionId = $_COOKIE[$this->cookieName];
        }
        if ($sessionId == '') {
            return $sessionId;
        }
        if ($this->isValidSessionId($sessionId)) {
            return $sessionId;
        } else {
            return '';
        }
    }

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

    /**
     * 随机生成会话id
     *
     * @return string
     */
    protected function makeSessionId(): string
    {
        return md5(time() . md5(rand(1, 1000)) . md5(uniqid()));
    }

    public function __construct()
    {
        $this->cookieName = Application::$app->config->getValue('INPUT_SESSION_KEY', 'session_id');
        // 默认15分钟有效期
        $this->sessionLife = 15 * 60;
        $sessionId = $this->loadClientSessionId();
        $this->initSessionData($sessionId);
        Application::$app->sessionStarted = true;
    }

    /**
     * 初始会话
     *
     * @param string $sessionId            
     * @return void
     */
    public function initSessionData(string $sessionId): void
    {
        $data = [
            'uid' => 0,
            'session_data' => [],
            'expired_at' => time() + $this->sessionLife,
            'updated_at' => time()
        ];
        $this->isNew = true;
        $this->destroyed = false;
        $this->commited = false;
        if (($sessionId != '') && $this->checkSessionExists($sessionId)) {
            $this->isNew = false;
            $data = $this->loadSessionData($sessionId);
        } else {
            do {
                $sessionId = $this->makeSessionId();
            } while ($this->checkSessionExists($sessionId));
        }
        $attributes = [
            'uid' => $data['uid'],
            'expired_at' => $data['expired_at'],
            'updated_at' => $data['updated_at'],
            'session_id' => $sessionId
        ];
        $sessionData = $data['session_data'];
        $this->sessionAttributes = new ObserverData($attributes);
        $this->sessionData = new ObserverData($sessionData);
    }

    /**
     * 检测会话id是否存在于服务器
     *
     * @param string $sessionId            
     * @throws \Exception
     * @return bool
     */
    protected function checkSessionExists(string $sessionId): bool
    {
        throw new \Exception('@todo');
    }

    /**
     * 加载已存在的会话数据
     *
     * @param string $sessionId            
     * @throws \Exception
     * @return array
     */
    protected function loadSessionData(string $sessionId): array
    {
        throw new \Exception('@todo');
    }

    /**
     * 删除会话数据
     *
     * @param string $sessionId            
     * @param int $uid            
     * @throws \Exception
     * @return void
     */
    protected function removeSessionData(string $sessionId, int $uid): void
    {
        throw new \Exception('@todo');
    }

    /**
     * 删除某用户的所有会话数据
     *
     * @param int $uid            
     * @throws \Exception
     * @return void
     */
    public function removeUserSession(int $uid): void
    {
        throw new \Exception('@todo');
    }

    /**
     * 保存当前会话数据
     *
     * @throws \Exception
     * @return void
     */
    protected function saveSessionData(bool $isNew): void
    {
        throw new \Exception('@todo');
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

    /**
     * 获取会话对应的用户id
     *
     * @return int
     */
    public function getUid(): int
    {
        return $this->sessionAttributes->getValue('uid');
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
        $this->sessionAttributes->setValue('uid', $uid);
    }

    /**
     * 获取会话对应的过期时间
     *
     * @return int
     */
    public function getExpiredAt(): int
    {
        return $this->sessionAttributes->getValue('expired_at');
    }

    /**
     * 设置会话的过期时间
     *
     * @param int $expiredAt
     *            过期时间
     * @return void
     */
    public function setExpiredAt(int $expiredAt): void
    {
        $this->sessionAttributes->setValue('expired_at', $expiredAt);
    }

    /**
     * 获取会话唯一标识
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionAttributes->getValue('session_id');
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

    protected function onSessionUpdated(): void
    {
        $updatedAt = time();
        $uid = $this->getUid();
        if ($uid != 0) {
            $eventArgs = new UserActivityEvent($uid, $updatedAt);
            $this->dispatchEvent($eventArgs);
        }
    }

    /**
     * 会话数据提交
     *
     * @param bool $sendCookie
     *            提交时是否发送cookie
     * @return void
     */
    public function commit(bool $sendCookie = true): void
    {
        if ($this->commited) {
            return;
        }
        $this->commited = true;
        if ($this->destroyed) {
            if (! $this->isNew) {
                $this->removeSessionData($this->getSessionId(), $this->getUid());
            }
        }
        $lastUpdatedAt = $this->sessionAttributes->getValue('updated_at');
        if ((time() - $lastUpdatedAt) > 300) {
            $this->sessionAttributes->setValue('updated_at', time());
        }
        $hasModify = ($this->sessionAttributes->hasChanged() || $this->sessionData->hasChanged());
        if ($this->isNew || $hasModify) {
            $this->onSessionUpdated();
            $this->saveSessionData($this->isNew);
            if ($sendCookie) {
                // 新会话、或者过期时间变化才发送cookie
                if ($this->isNew || isset($this->sessionAttributes->updateAttributes['expired_at'])) {
                    $this->sendCookieHeader();
                }
            }
        }
    }

    protected function sendCookieHeader(): void
    {
        $path = Application::$app->appContext . '/';
        $expiredAt = $this->sessionAttributes->getValue('expired_at');
        setcookie($this->cookieName, $this->getSessionId(), $expiredAt, $path, null, false, true);
    }
}