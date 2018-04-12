<?php
namespace liuguang\mvc\event\common;

use liuguang\mvc\event\EventArgs;

/**
 * 用户活动事件
 *
 * @author liuguang
 *        
 */
class UserActivityEvent extends EventArgs
{

    public $uid;

    public $updatedAt;

    public function __construct(int $uid, int $updatedAt)
    {
        $this->uid = $uid;
        $this->updatedAt = $updatedAt;
    }
}

