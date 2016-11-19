<?php

$str = '<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="刘珂矣 半壶纱">半壶纱</a>';

$test = '<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="he jinxue">test</a>';
preg_match('/(\d+).*title="([\x{4e00}-\x{9fa5}]*) ([\x{4e00}-\x{9fa5}]*)/u', $str, $result);

var_dump($result);