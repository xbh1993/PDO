<?php

class image
{
    protected $width;
    protected $height;
    protected $image;

    public function __construct($width = 200, $height = 40)
    {
        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);
    }

    //创建验证码
    public function createCode($type=1,$length=4)
    {
        //创建要验证的数字
        $code = $this->createStr($type, $length);
        //设置字体大小
        $fontsize = mt_rand(18, 22);
        //字体高度
        $textHeight = imagefontheight($fontsize);
        //字体长度
        $textWidth = imagefontwidth($fontsize);
        //设置字体类型
        $filettf = './fonts/georgiaz.ttf';
        //创建背景色
        $backgroundcolor = imagecolorallocate($this->image, 255, 255, 255);
        //背景色填充到画布
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $backgroundcolor);
        //把要验证的字符写入画布
        for ($i = 0; $i < strlen($code); $i++) {
            $textcolor = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            $x = $textWidth + ($this->width) / (strlen($code)) * $i + 10;
            $y = ($this->height) / 2 + $textHeight;
            $angle = mt_rand(-15, 15);
            $text = substr($code, $i, 1);
            imagettftext($this->image, $fontsize, $angle, $x, $y, $textcolor, $filettf, $text);
        }
        //设置输出的格式
        header('content-Type:image/png');
        //输出图片
        imagepng($this->image);
        //销毁图像资源
        imagedestroy($this->image);
    }

    /**
     * @param int $type 类型 1数字类型 2字符类型 3 数字加字符类型
     * @param int $length 返回的字符长度
     * @return bool|int|string 返回的字符
     */
    protected function createStr($type = 1, $length = 4)
    {
        if ($type == 1) {
            $min = pow(10, $length - 1);
            $max = pow(10, $length) - 1;
            $string = mt_rand($min, $max);
        }
        if ($type == 2) {
            $strArr = array_merge(range('a', 'z'), range('A', 'Z'));
            shuffle($strArr);
            $str = implode('', $strArr);
            $string = substr($str, 0, $length);
        }
        if ($type == 3) {
            $strArr = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
            shuffle($strArr);
            $str = implode('', $strArr);
            $string = substr($str, 0, $length);
        }
        return $string;
    }
}