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

namespace axguowen\dedetpl\driver;

class File
{
    /**
     * 写入文件
     * @access public
     * @param  string $cacheFile 缓存的文件名
     * @param  string $content 缓存的内容
     * @return void
     */
    public function write($cacheFile, $content)
    {
        // 检测模板目录
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 生成模板缓存文件
        if (false === file_put_contents($cacheFile, $content)) {
            throw new \Exception('file write error:' . $cacheFile, 11602);
        }
    }

    /**
     * 读取文件
     * @access public
     * @param  string  $cacheFile 缓存的文件名
     * @return void
     */
    public function read($cacheFile)
    {
        // 模板源码
        $source = '';
        // 读取模板文件获取模板源码
        $fp = @fopen($cacheFile, 'r');
        while($line = fgets($fp,1024))
        {
            $source .= $line;
        }
        fclose($fp);

        //载入模版缓存文件
        return $source;
    }
}
