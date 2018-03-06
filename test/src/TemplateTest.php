<?php
use PHPUnit\Framework\TestCase;
use liuguang\mvc\http\action\ViewResult;
use liuguang\mvc\Application;
use liuguang\mvc\services\UrlAsset;
use liuguang\mvc\Container;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\Package;

/**
 * 模板引擎单元测试
 *
 * @author liuguang
 *        
 */
class TemplateTest extends TestCase
{

    /**
     * 获取模板处理之后的内容
     *
     * @param string $templateName            
     * @return string
     */
    private function getTemplateResult(string $templateName): string
    {
        Application::$app->config->setValue('VIEW_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates');
        $template = new ViewResult($templateName);
        $viewPath = $template->getViewPath();
        return file_get_contents($viewPath);
    }

    /**
     * 测试include标签
     *
     * @return void
     */
    public function testInclude(): void
    {
        $this->assertEquals('aa-hello world-bb-hello world-cc-hello world', $this->getTemplateResult('include/test'));
    }

    /**
     * 测试template标签
     *
     * @return void
     */
    public function testTemplate(): void
    {
        $this->assertEquals(<<<'RESULT'
aa<?php include \liuguang\mvc\http\action\ViewResult::dynamicView('template/test1'); ?>bb
RESULT
, $this->getTemplateResult('template/test'));
    }

    /**
     * 测试变量输出标签
     *
     * @return void
     */
    public function testVars(): void
    {
        $this->assertEquals('<?php echo $testA; ?>', $this->getTemplateResult('vars/testa'));
        $this->assertEquals(<<<'RESULT'
<?php echo str_replace(['&','<','>'],['&amp;','&lt;','&gt;'],$testB); ?>
RESULT
, $this->getTemplateResult('vars/testb'));
    }

    /**
     * 测试url标签
     *
     * @return void
     */
    public function testUrl(): void
    {
        Application::$app->container->addCallableMap(UrlAsset::class, function (Container $container) {
            return new class() extends UrlAsset {

                private $version = 'v12';

                private $path = '/public';

                private $versionStrategy = null;

                private function getVersionStrategy()
                {
                    if ($this->versionStrategy === null) {
                        $this->versionStrategy = new StaticVersionStrategy($this->version);
                    }
                    return $this->versionStrategy;
                }

                /**
                 *
                 * {@inheritdoc}
                 *
                 * @see \liuguang\mvc\services\UrlAsset::getDefaultPackage()
                 */
                public function getDefaultPackage(): Package
                {
                    return new PathPackage($this->path, $this->getVersionStrategy());
                }

                /**
                 *
                 * {@inheritdoc}
                 *
                 * @see \liuguang\mvc\services\UrlAsset::getNamedPackages()
                 */
                public function getNamedPackages(): array
                {
                    $path = $this->path;
                    $versionStrategy = $this->getVersionStrategy();
                    return [
                        'img' => new PathPackage($path . '/image', $versionStrategy),
                        'js' => new PathPackage($path . '/js', $versionStrategy)
                    ];
                }
            };
        }, '@urlAsset');
        $this->assertEquals('<a href="/public/path/to/a.html?v12">aa</a>', $this->getTemplateResult('url/testa'));
        $this->assertEquals('<img src="/public/image/path/to/b.png?v12" />', $this->getTemplateResult('url/testb'));
        $this->assertEquals('<script type="text/javascript" src="/public/js/path/to/c.js?v12"></script>', $this->getTemplateResult('url/testc'));
    }

    /**
     * 测试变量输出标签
     *
     * @return void
     */
    public function testPhp(): void
    {
        $this->assertEquals(<<<'RESULT'
<?php echo 'hello world !'; ?>
RESULT
, $this->getTemplateResult('php/test'));
    }

    /**
     * 测试block
     *
     * @return void
     */
    public function testBlock(): void
    {
        $this->assertEquals(<<<'RESULT'
hello world
aaa
bbb
ccc
RESULT
, $this->getTemplateResult('block/test'));
    }

    /**
     * 测试注释
     *
     * @return void
     */
    public function testInfo(): void
    {
        $this->assertEquals(<<<'RESULT'
aaa<?php /*this is a comment*/ ?>
bbb
ccc
RESULT
, $this->getTemplateResult('info/test'));
    }

    public function testCondition(): void
    {
        $this->assertEquals(<<<'RESULT'
<?php if( $a ) { ?>
aaa
<?php } elseif($b) { ?>
bbb
<?php } else { ?>
ccc
<?php } ?>
RESULT
, $this->getTemplateResult('condition/test1'));
        $this->assertEquals(<<<'RESULT'
<?php foreach($arr as $key => $value){ ?>
----
key : <?php echo $key; ?>
----
value : <?php echo $value; ?>
----
<?php } ?>
RESULT
, $this->getTemplateResult('condition/test2'));
        $this->assertEquals(<<<'RESULT'
<?php foreach($arr as $value){ ?>
----
value : <?php echo $value; ?>
----
<?php } ?>
RESULT
, $this->getTemplateResult('condition/test3'));
    }

    /**
     * 测试不转换标签
     *
     * @return void
     */
    public function testNoConvert(): void
    {
        $this->assertEquals(<<<'RESULT'
{$a}
RESULT
, $this->getTemplateResult('convert/test1'));
        $this->assertEquals(<<<'RESULT'
{php}echo 'hello';{/php}
RESULT
, $this->getTemplateResult('convert/test2'));
    }

    /**
     * 测试标签合并
     *
     * @return void
     */
    public function testMerge(): void
    {
        $this->assertEquals(<<<'RESULT'
<?php if( $a ) { 
 var_dump('aaa'); 
 } elseif($b) { 
 var_dump('bbb'); 
 } else { 
 var_dump('ccc'); 
 } ?>
RESULT
, $this->getTemplateResult('merge/test'));
    }
}

