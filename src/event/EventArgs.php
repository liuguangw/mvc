<?php
namespace liuguang\mvc\event;

class EventArgs
{

    /**
     * 事件的发送者
     *
     * @var object
     *
     */
    public $sender = null;

    /**
     * 是否停止事件传播
     *
     * @var bool
     */
    public $propagationStopped;
}

