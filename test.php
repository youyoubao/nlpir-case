<?php
namespace ExecutableProgram;

//通过管道与其它可执行程序通信
class PipeInterface
{
    private static $instance; //每个C程序是单例模式

    private $handle;
    private $pipes;
    public static $programs;

    private function __construct($name)
    {
        $desc = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", "/tmp/error-output.txt", "a")
        );
        if (isset(self::$programs[$name]) && !empty(self::$programs[$name])) {
            $this->handle = proc_open(self::$programs[$name], $desc, $this->pipes);
        } else {
            return false;
        }
    }

    public function __destruct()
    {
        fclose($this->pipes['0']);
        fclose($this->pipes['1']);
        proc_close($this->handle);
    }

    public static function addPragram($name, $pragramPath)
    {
        self::$programs[$name] = $pragramPath;
    }
    
    /**
     * 初始化C程序
     * @param  [type] $name        实例名字
     * @param  [type] $pragramPath 可执行程序的地址
     * @return [type]              [description]
     */
    public static function getInstance($name)
    {
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new self($name);
        }
        return self::$instance[$name];
    }


    public function get($str)
    {
        $ret = '';
        if (!empty($str)) {
            fwrite($this->pipes['0'], trim($str)."\n");
            $ret =  trim(fgets($this->pipes[1]));
        }
        return $ret;
    }
}

PipeInterface::addPragram('doc', '/data/nlpir/demo/doc');
PipeInterface::addPragram('key', '/data/nlpir/demo/key');
PipeInterface::addPragram('summary', '/data/nlpir/demo/summary');

//需要处理的字符串
$content = str_replace(array("\n", "\t", " "), '', strip_tags(file_get_contents('article.txt')));

//实体获取组件
$docObj = PipeInterface::getInstance('doc');
//关键词获取组件
$keyObj = PipeInterface::getInstance('key');
//摘要获取组件
$summaryObj = PipeInterface::getInstance('summary');
 
echo "\n doc: ". $docObj->get($content);
echo "\n key: ". $keyObj->get($content);
echo "\n summary: ". $summaryObj->get($content);