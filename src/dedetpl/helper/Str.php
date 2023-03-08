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

namespace axguowen\dedetpl\helper;

class Str
{
    /**
     * 获取字符串在另一个字符串中第n次出现的位置
     * @param string    $str        字符串
     * @param string    $find       要查找的字符串
     * @param string    $start      起始位置
     * @param string    $n          第几次出现
     * @return string
     */
    public static function strposNth($str, $find, $start = 0, $n = 1)
    {
        // 位置
        $pos_val = 0;
        // 遍历
        for ($i=0;$i<$n;$i++){
            // 不是第一次循环则不用考虑开始位置
            if($i > 0){
                $start = 0;
            }
            $pos = strpos($str, $find, $start);
            // 如果不存在
            if($pos == false){
                $pos_val = false;
                break;
            }
            // 重新构造字符串
            $str = substr($str, $pos + 1);
            // 获取位置
            $pos_val = $pos_val + $pos + 1;
        }
        if($pos_val === false){
            return false;
        }
        return $pos_val - 1;
    }

    /**
     * 强制类型转换
     * @access public
     * @param  mixed  $data
     * @param  string $type
     * @return mixed
     */
    public static function typeCast($data, $type)
    {
        switch (strtolower($type)) {
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
                $data = (string) $data;
                break;
            // 默认
            default:
                break;
        }
        // 返回
        return $data;
    }
}
