<?php
namespace liuguang\mvc\event;

trait EventDispatcher {

    private $eventListenerMap = [];

    /**
     * 添加事件处理器
     *
     * @param string $eventClass
     *            事件类
     * @param callable $handler
     *            事件处理器
     * @return void
     */
    public function addEventListener(string $eventClass, callable $handler):void
    {
        if (! isset($this->eventListenerMap[$eventClass])) {
            $this->eventListenerMap[$eventClass] = [];
        }
        $this->eventListenerMap[$eventClass][] = $handler;
    }
    
    /**
     * 发送事件
     *
     * @param EventArgs $eventArgs
     *            事件参数
     * @return void
     */
    public function dispatchEvent(EventArgs $eventArgs)
    {
        if ($eventArgs->sender === null) {
            $eventArgs->sender = $this;
        }
        // 标记开始传播
        $eventArgs->propagationStopped = false;
        $eventClass = get_class($eventArgs);
        if (isset($this->eventListenerMap[$eventClass])) {
            $eventHandlers = $this->eventListenerMap[$eventClass];
            // 依次调用注册的事件处理器
            foreach ($eventHandlers as $handler) {
                if ($eventArgs->propagationStopped) {
                    return;
                }
                call_user_func($handler, $eventArgs);
            }
        }
    }
}