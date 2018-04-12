<?php
namespace liuguang\mvc\session;

use liuguang\mvc\services\SessionHandler as BaseSessionHandler;
use liuguang\mvc\Application;

/**
 * 基于缓存的session实现
 *
 * @author liuguang
 *        
 */
class CacheSession extends BaseSessionHandler
{

    /**
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cacheDriver;

    public function __construct()
    {
        $this->cacheDriver = Application::$app->getService('cache');
        parent::__construct();
    }

    private function getSessionKey(string $sessionId): string
    {
        return 'sid.' . $sessionId . '.data';
    }

    private function getSetsKey(int $uid): string
    {
        return 'uid.' . $uid . '.sids';
    }

    protected function checkSessionExists(string $sessionId): bool
    {
        $sessionItemKey = $this->getSessionKey($sessionId);
        if (! $this->cacheDriver->has($sessionItemKey)) {
            return false;
        }
        $data = $this->loadSessionData($sessionId);
        if ($data['expired_at'] < time()) {
            return false;
        }
        return true;
    }

    protected function loadSessionData(string $sessionId): array
    {
        $sessionItemKey = $this->getSessionKey($sessionId);
        return $this->cacheDriver->get($sessionItemKey, []);
    }

    protected function removeSessionData(string $sessionId, int $uid): void
    {
        $sessionItemKey = $this->getSessionKey($sessionId);
        $userSidsKey = $this->getSetsKey($uid);
        $this->cacheDriver->delete($sessionItemKey);
        $userSids = $this->cacheDriver->get($userSidsKey, []);
        foreach ($userSids as $key => $value) {
            if ($value == $sessionId) {
                unset($userSids[$key]);
            }
        }
        $this->cacheDriver->set($userSidsKey, $userSids);
    }

    public function removeUserSession(int $uid): void
    {
        $userSidsKey = $this->getSetsKey($uid);
        $userSids = $this->cacheDriver->get($userSidsKey, []);
        foreach ($userSids as $tmpSid) {
            $sessionItemKey = $this->getSessionKey($tmpSid);
            $this->cacheDriver->delete($sessionItemKey);
        }
        $this->cacheDriver->delete($userSidsKey);
    }

    protected function saveSessionData(bool $isNew): void
    {
        $uid = $this->getUid();
        $sessionId = $this->getSessionId();
        if ($isNew) {
            $userSidsKey = $this->getSetsKey($uid);
            $userSids = $this->cacheDriver->get($userSidsKey, []);
            if (! in_array($sessionId, $userSids)) {
                $userSids[] = $sessionId;
                $this->cacheDriver->set($userSidsKey, $userSids);
            }
        }
        $sessionItemKey = $this->getSessionKey($sessionId);
        $data = [
            'uid' => $uid,
            'session_data' => $this->sessionData->toArray(),
            'expired_at' => $this->getExpiredAt(),
            'updated_at' => $this->sessionAttributes->getValue('updated_at')
        ];
        $this->cacheDriver->set($sessionItemKey, $data, $this->getExpiredAt() - time());
    }
}

