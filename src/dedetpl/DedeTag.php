<?php
// +----------------------------------------------------------------------
// | ThinkDedetpl [Dedetpl package for ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP织梦模板引擎
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace think\dedetpl;

class DedeTag
{
    /**
     * 属性解析配置参数
     * @var array
     */
    protected $config = [
        'attr_maxlen'       => 1024, // 属性源码最大长度
        'insensitive'       => true, // 属性名称不区分大小写
    ];
    
    /**
     * 标签ID
     * @var string
     */
    protected $id = 0;

    /**
     * 标签名称
     * @var string
     */
    protected $name = '';

    /**
     * 标签的值
     * @var string
     */
    protected $value = '';
    
    /**
     * 标签起始位置
     * @var int
     */
    protected $beginPos = 0;

    /**
     * 标签结束位置
     * @var int
     */
    protected $endPos = 0;

    /**
     * 标签是否已被解析并赋值
     * @var bool
     */
    protected $assigned = false;

    /**
     * 标签字符串源码
     * @var string
     */
    protected $source = '';

    /**
     * 标签属性集合
     * @var array
     */
    protected $attributes = [];

    /**
     * 标签内容体
     * @var string
     */
    protected $innerText = '';

    /**
     * 架构函数
     * @access public
     * @param int $id 标签ID
     * @param array $config 配置参数
     */
    public function __construct($id, $config = [])
    {
        // 指定ID
        $this->id = $id;
        // 合并配置参数
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 标签解析配置
     * @access public
     * @param array $config 配置参数
     * @return $this
     */
    public function config($config)
    {
        // 合并配置参数
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 获取标签解析配置项
     * @access public
     * @param  string   $name       配置项
     * @return mixed
     */
    public function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     *  载入标签源码
     *
     * @access public
     * @param string $source 标签源码字符串
     * @return void
     */
    public function setSource($source)
    {
        // 重置成员变量
        $this->clear();
        // 过滤
        $source = trim(preg_replace("/[ \r\n\t]{1,}/",' ',$source));
        
        //为了在function内能使用数组，这里允许对[ ]进行转义使用
        $source = str_replace('\]',']',$source);
        $source = str_replace('[','[',$source);
        /*
        $source = str_replace('\>','>',$source);
        $source = str_replace('<','>',$source);
        $source = str_replace('\}','}',$source);
        $source = str_replace('{','{',$source);
        */
        
        if(!empty($source) && strlen($source) <= $this->config['attr_maxlen']){
            $this->source = $source;
            $this->parse();
        }
        // 返回当前对象
        return $this;
    }

    /**
     * 获取ID
     * @access    public
     * @return    string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取标签名
     * @access    public
     * @return    string
     */
    public function getName()
    {
        return $this->config['insensitive'] ? strtolower($this->name) : $this->name;
    }

    /**
     * 获取值
     * @access    public
     * @return    string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 设置标签值
     * @access    public
     * @param     string  $value        内容体
     * @return    $this
     */
    public function setValue($value = '')
    {
        // 赋值
        $this->value = $value;
        // 标记为已赋值
        $this->assigned = true;
        // 返回
        return $this;
    }

    /**
     * 获取标签内容体
     * @access    public
     * @return    string
     */
    public function getInnerText()
    {
        return $this->innerText;
    }

    /**
     * 设置标签内容体
     * @access    public
     * @param     string  $innerText    内容体
     * @return    bool
     */
    public function setInnerText($innerText = '')
    {
        $this->innerText = $innerText;
        return $this;
    }

    /**
     * 获取标签起始位置
     * @access    public
     * @return    string
     */
    public function getBeginPos()
    {
        return $this->beginPos;
    }

    /**
     * 设置标签起始位置
     * @access    public
     * @param     int       $beginPos        起始位置
     * @return    bool
     */
    public function setBeginPos($beginPos = 0)
    {
        $this->beginPos = $beginPos;
        return $this;
    }

    /**
     * 获取标签结束位置
     * @access    public
     * @return    string
     */
    public function getEndPos()
    {
        return $this->endPos;
    }

    /**
     * 设置标签结束位置
     * @access    public
     * @param     int       $endPos        结束位置
     * @return    bool
     */
    public function setEndPos($endPos = 0)
    {
        $this->endPos = $endPos;
        return $this;
    }

    /**
     * 获取赋值状态
     * @access    public
     * @return    bool
     */
    public function assigned()
    {
        return $this->assigned;
    }

    /**
     * 判断标签是否存在指定属性
     * @access    public
     * @param     string  $attrName     属性名
     * @return    bool
     */
    public function hasAttr($attrName)
    {
        return isset($this->attributes[$attrName]);
    }

    /**
     * 获取标签的指定属性
     * @access      public
     * @param       string  $attrName   属性名
     * @param       mixed   $default    默认值
     * @param       string  $filter     过滤规则
     * @return      string
     */
    public function getAttr($attrName, $default = null, $filter = '')
    {
        // 为空
        if(empty($attrName)) return '';
        // 转换为字符串
        $attrName = (string) $attrName;
        // 解析指定的属性名
        if (strpos($attrName, '/')) {
            list($attrName, $type) = explode('/', $attrName);
        }
        // 如果没有这个属性则返回默认值
        if(!isset($this->attributes[$attrName])) return $default;
        // 获取数据
        $value = $this->attributes[$attrName];
        // 如果存在过滤器
        if(!empty($filter)){
            // 是函数
            if(preg_match('/^\/\S*\/$/',$filter)){
                $value = preg_replace($filter, '', $value);
            }
            else if(is_callable($filter)){
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            }
            else{
                // 默认值
                $value = $default;
            }
        }

        // 如果指定类型
        if (isset($type) && $value !== $default) {
            // 强制类型转换
            $value = \think\dedetpl\helper\Str::typeCast($value, $type);
        }
        // 返回
        return $value;
    }

    /**
     *  重置成员变量
     *
     * @access    private
     * @return    $this
     */
    private function clear()
    {
        // 清除成员变量
        $this->source = '';
        $this->attributes = [];
        return $this;
    }

    /**
     *  属性解析入口
     *
     * @access    private
     * @return    string
     */
    private function parse()
    {
        // 当前遍历到的字符
        $currentStr = '';
        // 临时属性名
        $attrName = '';
        // 临时属性值
        $attrValue = '';
        // 循环开始位置
        $startdd = -1;
        // 属性限定标志
        $ddtag = '';
        // 是否存在属性
        $hasAttribute = false;
        // 标签源码长度
        $sourceLen = strlen($this->source);

        // 解析获取标签名
        for($i=0; $i<$sourceLen; $i++)
        {
            if($this->source[$i] == ' '){
                $attrValues = explode('|', $attrValue);
                $this->name = $this->config['insensitive'] ? strtolower($attrValues[0]) : $attrValues[0];
                if(isset($attrValues[1]) && $attrValues[1] != ''){
                    $this->attributes['name'] = $attrValues[1];
                }
                $attrValue = '';
                $hasAttribute = true;
                break;
            }
            else{
                $attrValue .= $this->source[$i];
            }
        }

        //不存在属性列表的情况
        if(!$hasAttribute){
            $attrValues = explode('|', $attrValue);
            $this->name = $this->config['insensitive'] ? strtolower($attrValues[0]) : $attrValues[0];
            if(isset($attrValues[1]) && $attrValues[1] != ''){
                $this->attributes['name'] = $attrValues[1];
            }
            return ;
        }
        $attrValue = '';

        //如果字符串含有属性值，遍历源字符串,并获得各属性
        for($i; $i<$sourceLen; $i++)
        {
            $currentStr = $this->source[$i];
            //查找属性名称
            if($startdd == -1){
                if($currentStr != '='){
                    $attrName .= $currentStr;
                }
                else{
                    if($this->config['insensitive']){
                        $attrName = strtolower(trim($attrName));
                    }
                    else{
                        $attrName = trim($attrName);
                    }
                    $startdd=0;
                }
            }

            //查找属性的限定标志
            else if($startdd==0){
                switch($currentStr)
                {
                    case ' ':
                        break;
                    case '"':
                        $ddtag = '"';
                        $startdd = 1;
                        break;
                    case '\'':
                        $ddtag = '\'';
                        $startdd = 1;
                        break;
                    default:
                        $attrValue .= $currentStr;
                        $ddtag = ' ';
                        $startdd = 1;
                        break;
                }
            }
            else if($startdd==1){
                if($currentStr == $ddtag && (isset($this->source[$i-1]) && $this->source[$i-1] != '\\')){
                    $this->attributes[$attrName] = trim($attrValue);
                    $attrName = '';
                    $attrValue = '';
                    $startdd = -1;
                }
                else{
                    $attrValue .= $currentStr;
                }
            }
        }
        //for

        //最后一个属性的给值
        if($attrName != ''){
            $this->attributes[$attrName] = trim($attrValue);
        }
        //print_r($this->attributes);
    }
}
