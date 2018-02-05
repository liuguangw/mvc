<?php
namespace liuguang\mvc\services;

use liuguang\mvc\event\common\ApplicationErrorEvent;
use liuguang\mvc\http\action\ContentResult;
use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\services\IErrorHandler;
use liuguang\mvc\Application;

class ErrorHandler implements IErrorHandler
{

    private $templateContent = null;

    /**
     *
     * @param ApplicationErrorEvent $evt            
     */
    public function handleError(ApplicationErrorEvent $evt): void
    {
        $httpCode = 500;
        if ($evt instanceof RouteErrorEvent) {
            $httpCode = $evt->httpErrorCode;
        }
        $exception = $evt->errorInfo;
        $errorCode = $exception->getCode();
        $errorType = get_class($exception);
        $message = $exception->getMessage();
        $traceArr = $exception->getTrace();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $this->showErrorMsg($httpCode, $errorCode, $errorType, $message, $traceArr, $file, $line);
    }

    /**
     * 显示错误消息
     *
     * @param int $code
     *            错误代码
     * @param string $errorType
     *            错误类型
     * @param string $message
     *            错误消息
     * @param array $traceArr
     *            错误追踪数组
     * @return void
     */
    private function showErrorMsg(int $httpCode, int $errorCode, string $errorType, string $message, array $traceArr, string $file, int $line)
    {
        if (empty($traceArr)) {
            $traceArr = debug_backtrace();
        }
        $content = $this->getErrContent();
        $mainTitle = 'HTTP 错误 ' . $httpCode;
        $errmsgPrefix = '<span style="color:red;">[' . $errorType . ']</span>[' . $errorCode . '] ';
        $errFile = '<span style="color:red;">[file]</span> ' . $file . ' on line <b>' . $line . '</b>';
        $showSource = Application::$app->config->getValue('ERROR_HANDLER_SHOW_SOURCE');
        $mainSource = '';
        $showMainSourceFn = 'javascript:void(0);';
        if ($showSource) {
            $mainSource = 'filesArr.push(' . json_encode($file) . ');
linesArr.push(' . $line . ');
contentArr.push(' . json_encode(htmlspecialchars(file_get_contents($file))) . ');';
            $showMainSourceFn = 'show_source_main();';
        }
        $content = str_replace([
            '{mainTitle}',
            '{errMsg}',
            '{errFile}',
            '{errTrace}',
            '{prettify.css}',
            '{prettify.js}',
            '{main_source}',
            '{showMainSourceFn}'
        ], [
            $mainTitle,
            $errmsgPrefix . htmlspecialchars($message),
            $errFile,
            $this->getTableHtml($traceArr),
            $showSource ? file_get_contents(Application::$app->mvcSourcePath . '/../static/prettify.css') : '',
            $showSource ? file_get_contents(Application::$app->mvcSourcePath . '/../static/prettify.js') : '',
            $mainSource,
            $showMainSourceFn
        ], $content);
        $actionResult = new ContentResult($content);
        $actionResult->statusCode = $httpCode;
        $actionResult->executeResult();
        exit();
    }

    private function getErrContent(): string
    {
        if ($this->templateContent === null) {
            $errFile = Application::$app->mvcSourcePath . '/../static/error.html';
            $this->templateContent = file_get_contents($errFile);
        }
        return $this->templateContent;
    }

    /**
     * 将调试跟踪数组转换为table
     *
     * @param array $traceArr
     *            调试跟踪数组
     * @return string
     */
    private function getTableHtml(array &$traceArr): string
    {
        $showSource = Application::$app->config->getValue('ERROR_HANDLER_SHOW_SOURCE');
        $tableHtml = '<table>
<tr><th>#stack</th><th>func</th><th>args</th><th>location</th></tr>';
        $stackIndex = 0;
        $filesArr = $linesArr = $contentArr = [];
        while (! empty($traceArr)) {
            $debugInfo = array_pop($traceArr);
            if ($showSource) {
                $tableHtml .= '<tr onclick="show_source(' . $stackIndex . ');">';
            } else {
                $tableHtml .= '<tr>';
            }
            $tableHtml .= ('<td>#' . (++ $stackIndex) . '</td>');
            $func = '';
            if (isset($debugInfo['class'])) {
                $func .= $debugInfo['class'];
            }
            if (isset($debugInfo['type'])) {
                $func .= htmlspecialchars($debugInfo['type']);
            }
            if (isset($debugInfo['function'])) {
                $func .= $debugInfo['function'];
            }
            $tableHtml .= ('<td>' . $func . '</td>');
            $argsArr = [];
            if (isset($debugInfo['args'])) {
                $argsArr = $debugInfo['args'];
            }
            $argTypes = [];
            foreach ($argsArr as $arg) {
                if (is_int($arg))
                    $argTypes[] = 'int';
                elseif (is_string($arg))
                    $argTypes[] = 'string';
                elseif (is_bool($arg))
                    $argTypes[] = 'bool';
                elseif (is_array($arg))
                    $argTypes[] = 'array';
                elseif (is_callable($arg))
                    $argTypes[] = 'callable';
                elseif (is_object($arg))
                    $argTypes[] = get_class($arg);
                elseif ($arg === null)
                    $argTypes[] = 'null';
                else
                    $argTypes[] = 'unknown';
            }
            $args = '(' . implode(', ', $argTypes) . ')';
            $tableHtml .= ('<td>' . $args . '</td>');
            $loaction = '';
            if (isset($debugInfo['file'])) {
                $loaction .= ('<b>' . $debugInfo['file'] . '</b>');
                if ($showSource) {
                    $filesArr[] = $debugInfo['file'];
                    $contentArr[] = htmlspecialchars(file_get_contents($debugInfo['file']));
                    if (isset($debugInfo['line'])) {
                        $linesArr[] = intval($debugInfo['line']);
                    } else {
                        $linesArr[] = 1;
                    }
                }
            } else {
                if ($showSource) {
                    $filesArr[] = '';
                    $contentArr[] = '';
                    $linesArr[] = 1;
                }
            }
            if (isset($debugInfo['line'])) {
                $loaction .= (' on line <b>' . $debugInfo['line'] . '</b>');
            }
            
            $tableHtml .= ('<td>' . $loaction . '</td>');
            $tableHtml .= ('</tr>' . PHP_EOL);
        }
        $tableHtml .= '</table>';
        if ($showSource) {
            $tableHtml .= ('<script type="text/javascript">
var filesArr=' . json_encode($filesArr) . ', linesArr=' . json_encode($linesArr) . ', contentArr=' . json_encode($contentArr) . ';
</script>');
        }
        return $tableHtml;
    }
}

