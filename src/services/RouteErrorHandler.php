<?php
namespace liuguang\mvc\services;

use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\Application;

class RouteErrorHandler implements IRouteErrorHandler
{

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\IRouteErrorHandler::handleError()
     */
    public function handleError(RouteErrorEvent $evt): void
    {
        $errorHandler = Application::$app->container->getInstance(IErrorHandler::class);
        $errorHandler->handleError($evt);
    }
}

