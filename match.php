<?php
/*
抓取网站链接（http://music.baidu.com/tag/tagname），分析匹配对应的html内容，页面数据格式如下：
<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="刘珂矣 半壶纱">半壶纱</a>

之后生成php文件，格式为
<?php
return array();
?>
*/
class Fetch {

    function getData($url) {
        $data = array();
        $str = $this->http($url);
        if($str) {
            $data  = $this->parseHtml($str);
        }
        return $data;
    }

    function http($url) {
        //No.1
        //开始写代码，根据所给链接抓取网站内容
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);        

        return $res; 
        //end_code
    }

    function parseHtml($str) {
        
        $ids = array();
        $titles = array();
        //No.2
        //开始写代码，解析页面内容，获得歌曲编号、歌曲名、艺人名字
        
        preg_match('/\/(\d+)"\w+="(\d+) \w+>(\w+)</', $str, $matches);

        return [
                $matches[1],
                $matches[2],
                $matches[3],
            ];
        //end_code
    }
}

$url = 'http://music.baidu.com/tag/%E7%83%AD%E6%AD%8C';
$fetch = new Fetch();
$data = $fetch->getData('<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="刘珂矣 半壶纱">半壶纱</a>');
var_dump($data);die;
//No.3
//开始写代码，生成格式为<？php return array(); ？>的php文件
$str = '<?php  return ['. $data[1] .','. $data[2] .','. data[3]. ']; ?>';
file_put_contents('/tmp/test.php', $str);
//end_code