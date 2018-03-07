<?php
namespace liuguang\mvc\session;

use liuguang\mvc\services\AbstractSession;
use liuguang\mvc\Application;
use liuguang\mvc\data\ObserverData;

/**
 * 基于缓存的session实现
 *
 * @author liuguang
 *        
 */
class CacheSession extends AbstractSession
{

    /**
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cacheDriver;

    public function __construct()
    {
        $this->cacheDriver = Application::$app->getService('cache');
        $this->loadSessionId();
        $oldSessionId = $this->oldAttributes['session_id'];
        $dataArray = [];
        if (! $this->isNew()) {
            // 加载会话数据
            $cacheItemKey = $this->getCacheItemKey($oldSessionId);
            $cacheData = $this->cacheDriver->get($cacheItemKey, []);
            $this->oldAttributes['uid'] = $cacheData['uid'];
            $dataArray = $cacheData['data'];
        }
        $this->sessionData = new ObserverData($dataArray);
    }

    private function getCacheItemKey($sessionId): string
    {
        return 'session.' . $sessionId;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractSession::sessionIdExists()
     */
    protected function sessionIdExists(string $sessionId): bool
    {
        $cacheItemKey = $this->getCacheItemKey($sessionId);
        return $this->cacheDriver->has($cacheItemKey);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractSession::commit()
     */
    public function commit(): void
    {
        if ($this->hasCommit()) {
            return;
        }
        $this->commited = true;
        $sessionId = $this->getSessionId();
        $cacheItemKey = $this->getCacheItemKey($sessionId);
        if ($this->destroyed) {
            if (! $this->isNew()) {
                // 非新会话销毁
                $this->cacheDriver->delete($cacheItemKey);
                $this->newAttributes['session_id'] = '-';
                $this->sendCookie();
            }
            return;
        }
        $sessionIdChanged = false;
        $oldSessionId = $this->oldAttributes['session_id'];
        if (($oldSessionId != '') && ($sessionId != $oldSessionId)) {
            $sessionIdChanged = true;
            // 如果会话标识发生改变，则删除旧key
            $oldItemKey = $this->getCacheItemKey($oldSessionId);
            $this->cacheDriver->delete($oldItemKey);
        }
        if ($this->isNew() || $sessionIdChanged || isset($this->newAttributes['lifeTime'])) {
            // 首次会话、会话id改变、生命周期变化时发送cookie
            $this->sendCookie();
        }
        // 首次会话、会话id改变、用户id变化、数据变化时保存/更新缓存数据
        if ($this->isNew() || $sessionIdChanged || isset($this->newAttributes['uid']) || $this->sessionData->getHasChanged()) {
            $cacheData = [
                'uid' => $this->getUid(),
                'data' => $this->sessionData->toArray()
            ];
            $this->cacheDriver->set($cacheItemKey, $cacheData, $this->getLifeTime());
        }
    }

    public function __destruct()
    {
        if (! $this->hasCommit()) {
            $this->commit();
        }
    }
}

