<?php
namespace liuguang\mvc;

use liuguang\mvc\event\common\ApplicationErrorEvent;

class ErrorHandler
{
    /**
     * 
     * @param ApplicationErrorEvent $evt
     */
    public function handleError(ApplicationErrorEvent $evt){
       echo '<pre>';
       var_dump($evt->errorInfo);
       echo '</pre>';
    }
}

