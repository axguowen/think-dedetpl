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

namespace axguowen;

use think\facade\Config;

class Dedetpl
{
	/**
     * 模板变量
     * @var array
     */
    protected $data = [];

    /**
     * 基础路径
     * @var string
     */
    protected $baseDir;

    /**
     * 模板存储对象
     * @var object
     */
    protected $storage;

    /**
     * 模板配置参数
     * @var array
     */
    protected $config = [
        // 模板路径
        'view_path'             => '',
        // 默认模板文件后缀
        'view_suffix'           => 'htm',
        // 模板文件分隔符
        'view_depr'             => DIRECTORY_SEPARATOR,
        // 模板引擎禁用函数列表
        'deny_func_list'        => ['echo', 'exit'],
        // 默认模板引擎是否禁用PHP原生代码
        'deny_runphp'           => false,
        // 标签命名空间
        'tag_namespace'         => 'itzjj',
        // 标签开始标记
        'tag_begin'             => '{',
        // 标签结束标记
        'tag_end'               => '}',
        // 是否去除模板文件里面的html空格与换行
        'strip_space'           => false,
        // 标签名称最大长度
        'tag_maxlen'            => 64,
        // 属性源码最大长度
        'attr_maxlen'           => 1024,
        // 标签名称和属性不区分大小写
        'insensitive'           => true,
        // 模板字符串过滤
        'replace_string'        => [],
    ];

    /**
     * 模板字符串源码
     * @var string
     */
    protected $source = '';

    /**
     * 解析到的标签
     * @var array
     */
    protected $tags = [];

    /**
     * 全部标签库列表
     * @var array
     */
    protected $tagLibs;
    
    /**
     * 架构函数
     * @access public
     * @param  array $config 配置参数
     */
    public function __construct($config = [])
    {
        // 合并配置文件中的配置
        $this->config = array_merge($this->config, Config::get('dedetpl', []));
        // 合并实例化时传入的配置参数
        $this->config = array_merge($this->config, $config);
        // 初始化模板编译存储器
        $this->storage = new \axguowen\dedetpl\driver\File();
    }

    /**
     * 设置模板引擎配置
     * @access public
     * @param  array $config 配置参数
     * @return $this
     */
    public function config($config)
    {
        // 合并配置参数
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 获取模板引擎配置项
     * @access public
     * @param  string $name 配置项
     * @return mixed
     */
    public function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * 模板变量获取
     * @access public
     * @param  string $name 变量名
     * @return mixed
     */
    public function getData($name = '')
    {
        // 如果为空
        if(empty($name)){
            return '';
        }
        // 如果为获取全部数组
        if ($name == 'array') {
            return $this->data;
        }
        // 获取当前模板变量
        $data = $this->data;
        // 遍历变量
        foreach (explode('.', $name) as $key => $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                $data = null;
                break;
            }
        }
        // 返回
        return $data;
    }

    /**
     * 设置标签的命名空间
     *
     * @access    public
     * @param     string   $tag_namespace       标签命名空间
     * @param     string   $tag_begin           开始标签
     * @param     string   $tag_end             结束标签
     * @return    object
     */
    public function setNameSpace($tag_namespace, $tag_begin='{', $tag_end='}')
    {
        // 转小写
        $tag_namespace = strtolower($tag_namespace);
        // 更新配置
        $this->config['tag_namespace'] = $tag_namespace;
        $this->config['tag_begin'] = $tag_begin;
        $this->config['tag_end'] = $tag_end;
        // 返回
        return $this;
    }

    /**
     * 重置成员变量
     *
     * @access    public
     * @return    $this
     */
    public function clear()
    {
        // 清除模板路径配置参数
        $this->baseDir = null;
        // 清除模板源码
        $this->source = '';
        // 清除解析到的标签集合
        $this->tags = [];
        // 返回
        return $this;
    }
    
    /**
     * 设置模板变量
     *
     * @access    public
     * @param     object  $data         模板变量
     * @return    void
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * 载入模板文件
     *
     * @access    public
     * @param     string   $filenam     模板文件路径
     * @return    string
     */
    public function loadTpl($filename)
    {
        // 如果未指定完整文件路径
        if ('' == pathinfo($filename, PATHINFO_EXTENSION)) {
            if (0 !== strpos($filename, '/')) {
                $filename = str_replace(['/', ':'], $this->config['view_depr'], $filename);
            } else {
                $filename = str_replace(['/', ':'], $this->config['view_depr'], substr($filename, 1));
            }
            $filename = $this->config['view_path'] . $filename . '.' . ltrim($this->config['view_suffix'], '.');
        }

        // 文件不存在
        if(!is_file($filename)){
            return $this->loadSource($filename . ' Not Found!');
        }
        
        // 模板源码
        $source = $this->storage->read($filename);
        // 载入模板源码
        $this->loadSource($source);
        // 指定当前模板文件
        $this->baseDir = dirname($filename);
        
        // 返回当前对象
        return $this;
    }

    /**
     * 载入模板源码
     *
     * @access    public
     * @param     string  $source       模板源码字符串
     * @return    void
     */
    public function loadSource($source)
    {
        // 重置成员变量
        $this->clear();
        // 设置目标字符串
        $this->source = $source;
        // 解析模板
        $this->parse();
        // 返回当前对象
        return $this;
    }

    /**
     * 获取指定名称的标签对象
     * 如果有多个同名的标签,则取没有被分配内容的第一个标签
     * 
     * @access    public
     * @param     string  $tagName      标签名
     * @return    array
     */
    public function getTag($tagName)
    {
        if($this->config['insensitive']){
            $tagName = strtolower($tagName);
        }
        foreach($this->tags as $tagid => $tag)
        {
            if($tag->getName() == $tagName && !$tag->assigned()){
                return $tag;
                break;
            }
        }
        return false;
    }

    /**
     *  获取指定名称的标签对象集合
     *
     * @access    public
     * @param     string  $tagName      字符串
     * @return    array
     */
    public function getTagsByName($tagName)
    {
        if($this->config['insensitive']){
            $tagName = strtolower($tagName);
        }
        // 符合条件的标签列表
        $tagList = [];
        foreach($this->tags as $tagid => $tag)
        {
            if($tag->getName() == $tagName){
                $tagList[] = $tag;
            }
        }
        return $tagList;
    }

    /**
     * 获取全部解析的标签
     * 
     * @access    public
     * @return    array
     */
    public function tags()
    {
        return $this->tags;
    }

    /**
     * 分配指定ID的标签的值
     *
     * @access    public
     * @param     string    $tagid          标签id
     * @param     string    $value          标签值
     * @param     string    $disRun         禁止运行函数
     * @return    void
     */
    public function assign($tagid, $value, $disRun = false)
    {
        if(isset($this->tags[$tagid])){
            // 未禁止运行函数
            if($disRun == false){
                // 有function属性
                if(!empty($this->tags[$tagid]->getAttr('function'))){
                    $value = $this->getEvalFunc($value, $this->tags[$tagid]->getAttr('function'));
                }
                // 有runphp属性
                elseif($this->tags[$tagid]->getAttr('runphp') == 'true'){
                    $value = $this->getRunPHP($value, $this->tags[$tagid]->getInnerText());
                }
            }
            // 设置标签值
            $this->tags[$tagid]->setValue($value);
        }
    }

    /**
     * 渲染模板并取得解析结果
     *
     * @access public
     * @param string $template 模板文件
     * @param array $data 模板变量
     * @return void
     */
    public function fetch($template = '', $data = [])
    {
        // 如果指定数据且不为空
        if(!empty($data)){
            $this->setData($data);
        }
        // 如果指定了模板文件
        if(!empty($template)){
            $this->loadTpl($template);
        }
        // 解析结果
        $resultString = '';
        // 没有标签
        if(empty($this->tags)){
            return $this->source;
        }
        // 模板编译
        $this->compiler();
        // 下一个标签结束位置
        $nextTagEnd = 0;
        // 遍历标签
        foreach($this->tags as $tagid => $tag)
        {
            // 获取标签值
            $tagValue = $tag->getValue();
            if(is_array($tagValue)) $tagValue = json_encode($tagValue);
            $resultString .= substr($this->source, $nextTagEnd, $tag->getBeginPos() - $nextTagEnd);
            $resultString .= $tagValue;
            $nextTagEnd = $tag->getEndPos();
        }
        // 模板源码长度
        $sourceLen = strlen($this->source);
        if($sourceLen>$nextTagEnd){
            $resultString .= substr($this->source, $nextTagEnd, $sourceLen - $nextTagEnd);
        }

        // 模板过滤输出
        $replace = $this->config['replace_string'];
        $resultString = str_replace(array_keys($replace), array_values($replace), $resultString);
        // 返回
        return $resultString;
    }

    /**
     * 直接输出解析模板
     *
     * @access public
     * @param string $content 模板内容
     * @param array $data 模板变量
     * @return void
     */
    public function display($content, $data = [])
    {
        // 如果指定数据且不为空
        if(!empty($data)){
            $this->setData($data);
        }
        // 加载模板源码
        $this->loadSource($content);
        // 返回
        return $this->fetch();
    }

    /**
     * 把解析模板输出为文件
     *
     * @access    public
     * @param     string   $filename  要保存到的文件
     * @return    void
     */
    public function saveTo($filename)
    {
        $this->storage->write($filename, $this->fetch());
    }

    /**
     * 模板解析入口
     *
     * @access    private
     * @return    string
     */
    private function parse()
    {
        // 标签开始标记 '{'
        $tagBegin = $this->config['tag_begin'];
        // 标签结束标记 '}'
        $tagEnd = $this->config['tag_end'];
        // 标签开始位置
        $beginPos = 0;
        // 标签结束位置
        $endPos = 0;
        // 最后一个标签结束符位置
        $lastEndPos = strrpos($this->source, $tagEnd);
        // 包含命名空间的标签开始标记 '{itzjj:'
        $fullTagBegin = $tagBegin . $this->config['tag_namespace'] . ':';
        // 标签开始标记的长度
        $fullTagBeginLen = strlen($fullTagBegin);
        // 非自闭合标签的结束标记 '{/itzjj:'
        $openTagEnd =  $tagBegin . '/' . $this->config['tag_namespace'] . ':';
        // 自闭合标签的结束标记 '/}'
        $closeTagEnd = '/' . $tagEnd;
        // 自闭合标签的结束标签长度
        $closeTagEndLen = strlen($closeTagEnd);
        // 模板源码长度
        $sourceLen = strlen($this->source);
        // 长度不够
        if($sourceLen <= $fullTagBeginLen + 3){
            return;
        }

        // 遍历模板字符串，提取标签及其属性信息
        for($i=0; $i < $sourceLen; $i++)
        {
            // 匹配到的模板标签名
            $tplTagName = '';

            // 如果不进行此判断，将无法识别相连的两个标签
            if($i > 0){
                $ss = $i - 1;
            }
            else{
                $ss = 0;
            }
            // 标签开始位置
            $beginPos = strpos($this->source, $fullTagBegin, $ss);
            // 开头位置
            if($i==0){
                // 模板源码是否以标签开头
                $headerStr = substr($this->source, 0, $fullTagBeginLen);
                if($headerStr == $fullTagBegin){
                    $beginPos = 0;
                }
            }
            // 如果没有标签
            if($beginPos === false){
                break;
            }

            // 开始匹配标签名
            // 标签名字符串起始位置
            $tplTagNamePos = $beginPos + $fullTagBeginLen;
            // 标签名字最大位置
            $tplTagNameLen = $tplTagNamePos + $this->config['tag_maxlen'];

            // 是否是自闭合
            $isSelfClosing = false;
            // 遍历
            for($j = $tplTagNamePos; $j < $tplTagNameLen; $j++)
            {
                // 超出总长度
                if($j > $sourceLen - 1){
                    break;
                }
                // 遇到空白字符
                if(preg_match("/[ \t\r\n]/", $this->source[$j])){
                    // 进一步判断是否是自闭合标签
                    // 读取当前标签后面第一个标签结束字符
                    for($k = $j; $k <= $lastEndPos; $k++){
                        // 查找到第一个非转义的标签结束字符
                        if($this->source[$k] == $tagEnd && $this->source[$k-1] != '\\'){
                            // 如果前面带斜杠则为自闭合标签
                            if($this->source[$k-1] == '/'){
                                $isSelfClosing = true;
                            }
                            break;
                        }
                    }
                    break;
                }
                // 遇到斜杠且斜杠后面是标签结束字符
                if($this->source[$j] == '/' && $this->source[$j+1] == $tagEnd){
                    $isSelfClosing = true;
                    break;
                }
                // 遇到标签结束字符
                if($this->source[$j] == $tagEnd){
                    break;
                }
                $tplTagName .= $this->source[$j];
            }
        
            // 解析的标签名不为空
            if(!empty($tplTagName)){
                // 设置循环指针位置
                $i = $tplTagNamePos;
                // 设置标签结束位置
                $endPos = false;
                $elen = $closeTagEndLen;
                // 当前标签结束标记
                $fullTagEndWordThis = $openTagEnd . $tplTagName . $tagEnd;
                // 如果是自闭合标签
                if($isSelfClosing){
                    // 标签结束位置
                    $endPos = strpos($this->source, $closeTagEnd, $i);
                    $elen = $endPos + $closeTagEndLen;
                }
                // 如果是非自闭合标签
                else{
                    // 查找结束标签
                    $endPos = strpos($this->source, $fullTagEndWordThis, $i);
                    // 未找到结束标记
                    if($endPos === false){
                        echo '模板标签 [' . $tplTagName . '] 定义错误，未找到结束标签！标签位置： ' . $beginPos . ',<br />' . "\r\n";
                        break;
                    }
                    // 计算在结束标签区间中相同标签名的数量，
                    $tagCountsAll = substr_count($this->source, $fullTagBegin . $tplTagName, $beginPos, $endPos - $beginPos);
                    // 要排除的子标签
                    $tagCountsChild = substr_count($this->source, $fullTagBegin . $tplTagName . '/', $beginPos, $endPos - $beginPos);
                    // 数量
                    $tagCounts = $tagCountsAll - $tagCountsChild;
                    // 如果次数大于1则说明存在标签嵌套
                    if($tagCounts > 1){
                        // 获取实际结束标签位置
                        $endPos = \axguowen\dedetpl\helper\Str::strposNth($this->source, $fullTagEndWordThis, $i, $tagCounts);
                        // 未找到结束标记
                        if($endPos === false){
                            echo '模板标签 [' . $tplTagName . '] 定义错误，未找到结束标签！标签位置： ' . $beginPos . ',<br />' . "\r\n";
                            break;
                        }
                    }
                    // 计算标签结束位置
                    $elen = $endPos + strlen($fullTagEndWordThis);
                }

                // 设置循环指针
                $i = $elen;

                // 解析标签位置及属性
                // 属性字符串
                $attrStr = '';
                // 标签内容体
                $innerText = '';
                // 内容体开始
                $startInner = false;
                // 读取标签信息
                for($j = $tplTagNamePos; $j < $endPos; $j++)
                {
                    // 标签内容体未开始
                    if($startInner == false){
                        // 读取到标签结束字符则内容体开始
                        if($this->source[$j] == $tagEnd && $this->source[$j-1] != '\\'){
                            $startInner = true;
                            continue;
                        }
                        // 写入属性字符串
                        else{
                            $attrStr .= $this->source[$j];
                        }
                        
                    }
                    // 写入内容体字符串
                    else{
                        $innerText .= $this->source[$j];
                    }
                }
                //echo "<xmp>$attrStr</xmp>\r\n";
                
                // 构造当前标签ID
                $tagid = count($this->tags) + 1;
                // 实例化标签对象并写入到标签数组
                $dedeTag = new \axguowen\dedetpl\DedeTag($tagid, $this->config);
                $dedeTag->setSource($attrStr)
                        ->setInnerText($innerText)
                        ->setBeginPos($beginPos)
                        ->setEndPos($i);
                $this->tags[$tagid] = $dedeTag;
            }
            else{
                $i = $beginPos + $fullTagBeginLen;
                break;
            }
        }
    }

    /**
     * 编译模板文件内容
     *
     * @access    private
     * @return    string
     */
    private function compiler()
    {
        // 获取标签库列表
        $tagLibs = $this->loadTagLibs();
        // 遍历标签集合
        foreach($this->tags as $tagid => $tag)
        {
            // 读取标签名
            $tagName = $tag->getName();

            // 如果标签命名空间为field或者标签名为field则直接赋值
            if($this->config['tag_namespace'] == 'field' || $tagName == 'field'){
                // 读取标签name属性
                $vname = $this->config['tag_namespace'] == 'field' ? $tagName : $tag->getAttr('name');
                // 获取对应字段的值
                $tagValue = $this->getData($vname);
                $defaultValue = $tag->getAttr('default');
                // 如果为空且存在默认值
                if(!is_null($defaultValue) && '' === $tagValue){
                    $tagValue = $defaultValue;
                }
                // 标签赋值
                $this->assign($tagid, $tagValue);
                // 结束本次循环
                continue;
            }

            // 构造解析内置标签方法
            $methodBuildIn = 'parse' . \think\helper\Str::studly($tagName);
            // 如果是内置标签
            if (method_exists($this, $methodBuildIn)) {
                $this->$methodBuildIn($tag);
                continue;
            }

            // 处理模板自定义标签
            // 如果是自定义标签在标签库数组中
            if(isset($tagLibs[$tagName]) && class_exists($tagLibs[$tagName])){
                // 获取完整类名
                $tagClass = $tagLibs[$tagName];
                // 实例化标签库解析类
                $tagLib = new $tagClass($tag, $this->data);
                // 标签赋值
                $this->assign($tagid, $tagLib->fetch());
            }
        }
    }

    /**
     * 解析一个全局标签
     * @access protected
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象实例
     * @return \axguowen\dedetpl\DedeTag
     */
    protected function parseGlobal($tag)
    {
        // 标签值
        $tagValue = $GLOBALS;
        // 默认值
        $defaultValue = $tag->getAttr('default');
        // 获取指定标签的name属性
        $name = trim($tag->getAttr('name'));
        // 如果为空
        if(empty($name)){
            // 标签赋值
            return $tag->setValue('');
        }
        // 获取数据
        foreach (explode('.', $name) as $key => $val) {
            if(isset($tagValue[$val])){
                $tagValue = $tagValue[$val];
            }
            else{
                $tagValue = null;
                break;
            }
        }
        // 如果为空且存在默认值
        if(!is_null($defaultValue) && '' === $tagValue){
            $tagValue = $defaultValue;
        }
        // 标签赋值
        return $tag->setValue($tagValue);
    }

    /**
     * 解析一个遍历标签
     * @access protected
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象实例
     * @return \axguowen\dedetpl\DedeTag
     */
    protected function parseForeach($tag)
    {
        // 标签内容体
        $innerText = $tag->getInnerText();
        // 内容体为空
        if(empty($innerText)){
            // 标签赋值
            return $tag->setValue('');
        }
        // 获取指定标签的name属性
        $name = trim($tag->getAttr('name'));
        // 如果为空
        if(empty($name)){
            // 标签赋值
            return $tag->setValue('');
        }
        // 定义数据
        $data = $this->data;
        // 获取指定的数据
        foreach (explode('.', $name) as $key => $val) {
            if(isset($data[$val])){
                $data = $data[$val];
            }
            else{
                $data = null;
                break;
            }
        }
        // 如果数据为空
        if(empty($data)){
            // 标签赋值
            return $tag->setValue('');
        }

        // 获取键名
        $key = $tag->getAttr('key');
        // 键名为空
        if(empty($key)){
            $key = 'key';
        }

        // 获取值名
        $id = $tag->getAttr('id');
        // 值名为空
        if(empty($id)){
            $id = 'val';
        }

        // 实例化模板引擎
        $template = new static($this->config);
        // 将标签内容体载入模板引擎
        $template->loadSource($innerText);
        // 标签值
        $resultStr = '';
        // 遍历数据
        foreach($data as $$key => $$id){
            // 当前数据
            $item = [];
            // 父级数据
            $item['$parent'] = $data;
            // 键名
            $item[$key] = $$key;
            // 键值
            $item[$id] = $$id;
            // 获取解析内容
            $resultStr .= $template->setData($item)->fetch();
        }
        // 标签赋值
        return $tag->setValue($resultStr);
    }

    /**
     * 解析一个循环标签
     * @access protected
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象实例
     * @return \axguowen\dedetpl\DedeTag
     */
    protected function parseVolist($tag)
    {
        // 获取emplty属性
        $emplty = trim($tag->getAttr('emplty', ''));

        // 标签内容体
        $innerText = $tag->getInnerText();
        // 内容体为空
        if(empty($innerText)){
            // 标签赋值
            return $tag->setValue($emplty);
        }

        // 获取name属性
        $name = trim($tag->getAttr('name'));
        // 如果为空
        if(empty($name)){
            // 标签赋值
            return $tag->setValue($emplty);
        }
        // 定义数据
        $data = $this->data;
        // 获取指定的数据
        foreach (explode('.', $name) as $key => $val) {
            if(isset($data[$val])){
                $data = $data[$val];
            }
            else{
                $data = null;
                break;
            }
        }

        // 获取key属性
        $key = $tag->getAttr('key');
        // 如果为空
        if(empty($key)){
            $key = 'i';
        }

        // 获取id属性
        $id = trim($tag->getAttr('id'));
        // 如果为空
        if(empty($id)){
            $id = 'item';
        }

        // 获取offset属性
        $offset = intval($tag->getAttr('offset'));

        // 获取length属性
        $length = $tag->getAttr('length');

        // 待遍历的数组
        $list = [];

        // 设置了输出数组长度
        if (0 != $offset || !is_null($length)) {
            $list = array_slice($data, $offset, $length, true);
        } else {
            $list = $data;
        }

        // 如果数据为空
        if(empty($list)){
            // 标签赋值
            return $tag->setValue($emplty);
        }

        // 实例化模板引擎
        $template = new static($this->config);
        // 将标签内容体载入模板引擎
        $template->loadSource($innerText);
        // 标签值
        $resultStr = '';
        // 索引
        $index = 0;
        // 遍历数据
        foreach($list as $$key => $$id){
            // 当前数据
            $item = [];
            // 索引自增
            $index++;
            // 设置索引值
            $item['$index'] = $index;
            // 父级数据
            $item['$parent'] = $data;
            // 键名
            $item[$key] = $$key;
            // 键值
            $item[$id] = $$id;
            // 获取解析内容
            $resultStr .= $template->setData($item)->fetch();
        }
        // 标签赋值
        return $tag->setValue($resultStr);
    }

    /**
     * 解析一个PHP标签
     * @access protected
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象实例
     * @return \axguowen\dedetpl\DedeTag
     */
    protected function parsePhp($tag)
    {
        // 获取标签内容体
        $innerText = trim($tag->getInnerText());
        // 如果为空
        if(empty($innerText)){
            // 标签赋值
            return $tag->setValue('');
        }
        // 返回内容
        $resultStr = '';
        // 执行代码并获取返回结果
        ob_start();
        $data = $this->data;
        @eval($innerText);
        $resultStr = ob_get_clean();
        // 标签赋值
        return $tag->setValue($resultStr);
    }

    /**
     * 解析一个文件引入标签
     * @access protected
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象实例
     * @return \axguowen\dedetpl\DedeTag
     */
    protected function parseInclude($tag)
    {
        // 获取指定标签的filename属性
        $filename = $tag->getAttr('file');
        // 路径为空
        if(empty($filename)){
            // 标签赋值
            $tag->setValue('');
        }

        // 构造引入文件路径
        $incfile = $this->baseDir . '/' . $filename;
        if(!file_exists($this->baseDir . '/' . $filename)){
            return '无法在这个位置找到： ' . $filename;
        }

        // 文件内容
        $source = '';
        // 是否需要编译
        $compile = $tag->getAttr('compile');
        // 声明不需要编译
        if(strtolower($compile) == 'false'){
            // 直接读取文件内容
            $fp = @fopen($incfile, 'r');
            while($line = fgets($fp,1024)) $source .= $line;
            fclose($fp);
        }
        // 默认编译
        else{
            $template = new static($this->config);
            $source = $template->setData($this->data)->loadTpl($incfile)->fetch();
        }
        // 标签赋值
        return $tag->setValue($source);
    }

    /**
     * 运行PHP代码
     * @access protected
     * @param string $value 字段值
     * @param string $innerText 标签内容体
     * @return string
     */
    protected function getRunPHP($value, $innerText = '')
    {
        // 模板禁用runphp功能
        if($this->config['deny_runphp']){
            return '模板引擎已禁用 runphp 功能';
        }
        // 内容体为空
        if(empty($innerText)){
            return '';
        }
        // 设置me变量
        $meValue = $value;
        // 构造php代码
        $phpcode = preg_replace('/\'@me\'|\"@me\"|@me/i', '$meValue', $innerText);
        @eval($phpcode); //or die("<xmp>$phpcode</xmp>");
        // 返回
        return empty($meValue) ? '' : $meValue;
    }

    /**
     * 处理某字段的函数
     * @access protected
     * @param string $value 字段值
     * @param string $functionName 函数名称
     * @return string
     */
    protected function getEvalFunc($value, $functionName)
    {
        // 如果是执行指定函数
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $functionName)){
            // 属于模板禁用函数
            if (in_array($functionName, $this->config['deny_func_list'])) {
                return '模板引擎已禁用 ' . $functionName . ' 方法';
            }
            if(!is_callable($functionName)){
                return $functionName . ' 方法不可用';
            }
            return call_user_func($functionName, $value);
        }
        // 如果是方法源码
        $meValue = $value;
        $functionName = str_replace('{"','["',$functionName);
        $functionName = str_replace('"}','"]',$functionName);
        $functionName = preg_replace('/\'@me\'|\"@me\"|@me/i','$meValue',$functionName);
        $functionName = '$meValue = ' . $functionName;
        @eval($functionName . ';'); //or die("<xmp>$functionName</xmp>");
        // 返回
        return empty($meValue) ? '' : $meValue;
    }

    /**
     * 加载标签库列表
     * @access protected
     * @return void
     */
    protected function getTagLibs()
    {
        // 如果未读取过标签库则加载标签库
        if(is_null($this->tagLibs)){
            // 读取
            $this->tagLibs = $this->loadTagLibs();
        }
        // 返回
        return $this->tagLibs;
    }

    /**
     * 加载标签库列表
     * @access protected
     * @return void
     */
    protected function loadTagLibs()
    {
        // 从配置文件读取标签库
        $this->tagLibs = Config::get('dedetpl.taglibs', []);
        // 返回
        return $this->tagLibs;
    }

    /**
     * 模板引擎参数赋值
     * @access public
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }
}