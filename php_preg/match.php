<?php
/*
抓取网站链接（http://music.baidu.com/tag/tagname），分析匹配对应的html内容，页面数据格式如下：
<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="刘珂矣 半壶纱">半壶纱</a>

之后生成php文件，格式为
<?php
return array();
?>
*/
class Fetch 
{

    public function getData($url) 
    {
        $str = $this->http($url);
        if($str) {
            $data  = $this->parseHtml($str);
        }
        return $data;
    }

    public function http($url) 
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);        
        var_dump($res);
        return $res; 
    }

    public function parseHtml($str) 
    {
        //preg_match_all('/(\d+).*title="([a-zA-Z\.\x{4e00}-\x{9fa5}]*|[a-zA-Z\.\x{4e00}-\x{9fa5}]*,[a-zA-Z\.\x{4e00}-\x{9fa5}]*)《[a-zA-Z\.Ⅱ\x{4e00}-\x{9fa5}]*》([a-zA-Z\.\x{4e00}-\x{9fa5}]*)/u', $str, $matches);
        preg_match_all('/(\d+).*title="([a-zA-Z,:\._\x{4e00}-\x{9fa5}]*)《[a-zA-Z\.\s():_Ⅱ\x{4e00}-\x{9fa5}]*》?([a-zA-Z\.\x{4e00}-\x{9fa5}]*)/u', $str, $matches);
        return [
                $matches[1],
                $matches[2],
                $matches[3],
            ];
    }
}

$url = 'http://music.baidu.com/tag/%E7%83%AD%E6%AD%8C';
$fetch = new Fetch();
$data = $fetch->getData($url);
var_dump($data);die;
file_put_contents('/tmp/test.php', '<?php  return '. var_export($data, true).'; ?>');
//end_code