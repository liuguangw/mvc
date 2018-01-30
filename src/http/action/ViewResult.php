<?php
namespace liuguang\mvc\http\action;

use liuguang\mvc\data\DataMap;
use liuguang\mvc\Application;
use liuguang\mvc\event\common\ApplicationErrorEvent;

/**
 * 视图响应
 *
 * @author liuguang
 *        
 */
class ViewResult extends ActionResult
{

    /**
     * 模板源文件路径
     *
     * @var string
     */
    protected $tplSrcPath;

    /**
     * 模板目标文件路径
     *
     * @var string
     */
    protected $tplDistPath;

    /**
     * 布局文件路径
     *
     * @var string
     */
    protected $layoutPath;

    /**
     * 是否禁用模板缓存
     *
     * @var bool
     */
    protected $disableTplCache;

    /**
     * 模板变量
     *
     * @var DataMap
     */
    public $params;

    /**
     * 开始标签
     *
     * @var string
     */
    protected $startTag = '{';

    /**
     * 结束标签
     *
     * @var string
     */
    protected $endTag = '}';

    /**
     * url工具
     *
     * @var \liuguang\mvc\http\UrlAsset
     */
    public static $urlAsset = null;

    /**
     */
    public function __construct(string $viewName, ?string $layout = null, ?DataMap $params = null)
    {
        $app = Application::$app;
        $viewBasePath = MODULE_PATH . '/./' . str_replace('.', '/', $app->routeInfo->moduleName) . '/view';
        $this->tplSrcPath = $viewBasePath . '/src/' . $viewName . '.tpl';
        $this->tplDistPath = $viewBasePath . '/dist/' . $viewName . '.php';
        if ($layout === null) {
            $this->layoutPath = null;
        } else {
            $this->layoutPath = $viewBasePath . '/layout/' . $layout . '.tpl';
        }
        $this->disableTplCache = $app->config->getValue('DISABLE_TPL_CACHE');
        if ($params === null) {
            $params = DataMap::getNewInstance();
        }
        $this->params = $params;
    }

    /**
     * 用于动态加载
     *
     * @return string
     */
    public static function dynamicView(string $viewName): string
    {
        $view = new static($viewName);
        return $view->getViewPath();
    }

    /**
     * 获取模板源代码
     *
     * @return string
     */
    public function getTemplateSource(): string
    {
        $content = @file_get_contents($this->tplSrcPath);
        if ($content === false) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '读取模板文件' . $this->tplSrcPath . '失败'));
            return '<!--error-->';
        }
        // 与布局文件合并
        if ($this->layoutPath !== null) {
            $this->processLayout($content);
        }
        return $content;
    }

    /**
     * 获取合并后的模板内容
     *
     * @return string
     */
    protected function getMergedContent(): string
    {
        $content = $this->getTemplateSource();
        // 处理include合并
        // /
        // /{include mobile/header}
        // /
        $this->processIncludeTag($content);
        // 处理动态包含
        // /
        // /{template mobile/header}
        // /
        $this->processDynamicTag($content);
        // 处理扩展
        if ($this->hasExtendRule()) {
            $this->extendTemplate($content);
        }
        // 处理变量输出
        // /
        // /{$a}
        // /
        $this->processVars($content);
        // 处理变量输出(过滤特殊符号)
        // /
        // /{text $a}
        // /
        $this->processTextVars($content);
        // /处理url标签
        // /
        // /{url image}path/to/image.png{/url}
        // /
        $this->processUrlTag($content);
        // 处理php标签
        // /
        // /{php}echo hello world;{/php}
        // /
        $this->processPhpTag($content);
        // 处理block
        $this->processBlocks($content);
        // 处理不转换的标签
        // /
        // /{!}{$val}
        // /
        $this->processNoConvert($content);
        return $content;
    }

    /**
     * 用于判断是否有附加的模板语法(用于子类添加额外的模板标签)
     *
     * @return bool
     */
    protected function hasExtendRule(): bool
    {
        return false;
    }

    /**
     * 模板附加标签语法
     *
     * @param string $tplContent
     *            模板内容
     * @return void
     */
    protected function extendTemplate(string &$tplContent)
    {}

    /**
     * 构建视图模板
     *
     * @return void
     */
    protected function buildViewTemplate(): void
    {
        $distDir = dirname($this->tplDistPath);
        // 创建文件夹
        if (! is_dir($distDir)) {
            if (! mkdir($distDir, 0755, true)) {
                Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '创建模板目录' . $distDir . '失败'));
                return;
            }
        }
        $content = $this->getMergedContent();
        if (@file_put_contents($this->tplDistPath, $content) === false) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '模板文件' . $this->tplDistPath . '写入失败'));
        }
    }

    /**
     * 获取视图路径
     *
     * @return string
     */
    public function getViewPath(): string
    {
        if (is_file($this->tplDistPath)) {
            if ($this->disableTplCache) {
                $this->buildViewTemplate();
            }
        } else {
            $this->buildViewTemplate();
        }
        return $this->tplDistPath;
    }

    /**
     * 获取tag的正则表达式
     *
     * @param string $pattern
     *            中间标签
     * @return string
     */
    protected function getTagPattern(string $pattern): string
    {
        // {...}标签左侧为{!}时不执行转换
        return '/(?<!{!})' . preg_quote($this->startTag) . $pattern . preg_quote($this->endTag) . '/s';
    }

    /**
     * 将模板和布局文件合并
     *
     * @param string $content
     *            模板内容
     * @return void
     */
    protected function processLayout(string &$content): void
    {
        $layoutContent = @file_get_contents($this->layoutPath);
        if ($layoutContent === false) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '读取布局文件' . $this->layoutPath . '失败'));
        }
        $content = str_replace('{content}', $content, $layoutContent);
    }

    /**
     * 处理include标签
     *
     * @param string $content
     *            原模板内容
     * @return void
     */
    protected function processIncludeTag(string &$content): void
    {
        $pattern = $this->getTagPattern('include\s+(.+?)');
        while (preg_match($pattern, $content) != 0) {
            $content = preg_replace_callback($pattern, function ($match) {
                $viewResult = new ViewResult($match[1]);
                return $viewResult->getTemplateSource();
            }, $content);
        }
    }

    protected function processDynamicTag(string &$content)
    {
        $pattern = $this->getTagPattern('template\s+(.+?)');
        $content = preg_replace_callback($pattern, function ($match) {
            $viewResult = new ViewResult($match[1]);
            return '<?php include \\' . get_class($this) . '::dynamicView(\'' . $match[1] . '\'); ?>';
        }, $content);
    }

    /**
     * 处理变量输出
     *
     * @param string $content
     *            原模板内容
     * @return void
     */
    protected function processVars(string &$content): void
    {
        $pattern = $this->getTagPattern('(\$.+?)');
        $content = preg_replace_callback($pattern, function ($match) {
            return '<?php echo ' . $match[1] . '; ?>';
        }, $content);
    }

    /**
     * 处理变量输出(过滤HTML特殊符号)
     *
     * @param string $content
     *            原模板内容
     * @return void
     */
    protected function processTextVars(string &$content): void
    {
        $pattern = $this->getTagPattern('text\s+(\$.+?)');
        $content = preg_replace_callback($pattern, function ($match) {
            return '<?php echo str_replace([\'&\',\'<\',\'>\'],[\'&amp;\',\'&lt;\',\'&gt;\'],' . $match[1] . '); ?>';
        }, $content);
    }

    /**
     * 处理静态资源
     *
     * @param string $content
     *            原模板内容
     * @return void
     */
    protected function processUrlTag(string &$content): void
    {
        if (static::$urlAsset === null) {
            $urlAssetClassname = Application::$app->config->getValue('URL_ASSET');
            static::$urlAsset = new $urlAssetClassname();
        }
        $urlAsset = static::$urlAsset;
        $pattern = $this->getTagPattern('url(\s+(.+?))?' . preg_quote($this->endTag, '/') . '(.+?)' . preg_quote($this->startTag . '/url', '/'));
        $content = preg_replace_callback($pattern, function ($match) use ($urlAsset) {
            $matchName = $match[2];
            $matchPath = $match[3];
            if ($matchName == '') {
                return $urlAsset->getUrl($matchPath);
            } else {
                return $urlAsset->getUrl($matchPath, $matchName);
            }
        }, $content);
    }

    /**
     * 处理php标签
     *
     * @param string $content
     *            原模板内容
     * @return void
     */
    protected function processPhpTag(string &$content): void
    {
        $pattern = $this->getTagPattern('php' . preg_quote($this->endTag, '/') . '(.+?)' . preg_quote($this->startTag . '/php', '/'));
        $content = preg_replace_callback($pattern, function ($match) {
            return '<?php ' . $match[1] . ' ?>';
        }, $content);
    }

    /**
     * 处理block
     *
     * @param string $content            
     * @return void
     */
    protected function processBlocks(string &$content): void
    {
        $pattern = $this->getTagPattern('block\s+([_a-zA-Z][_a-zA-Z0-9]*)' . preg_quote($this->endTag, '/') . '\s*(.+?)\s*' . preg_quote($this->startTag . '/block', '/'));
        $blockCodes = [];
        $content = preg_replace_callback($pattern, function ($match) use (&$blockCodes) {
            $blockCodes[$match[1]] = $match[2];
            return '';
        }, $content);
        $displayPattern = $this->getTagPattern('display_block\s+([_a-zA-Z][_a-zA-Z0-9]*)');
        $content = preg_replace_callback($displayPattern, function ($match) use ($blockCodes) {
            if (isset($blockCodes[$match[1]])) {
                return $blockCodes[$match[1]];
            }
            return '';
        }, $content);
    }

    /**
     * 处理不转换标识
     *
     * @param string $content            
     * @return void
     */
    protected function processNoConvert(string &$content): void
    {
        $pattern = '/{!}(' . preg_quote($this->startTag) . '.+?' . preg_quote($this->endTag) . ')/s';
        $content = preg_replace($pattern, '\1', $content);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::outputContent()
     */
    protected function outputContent(): void
    {
        extract($this->params->toArray());
        include $this->getViewPath();
    }
}

