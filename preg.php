<?php

$str = '<a href="http://music.baidu.com/song/121353608" target="_blank" class="" data-provider="" title="刘珂矣 半壶纱">半壶纱</a>';

$test = 'title="wwww">';
preg_match('/title="(\w+)"\w+/', $test, $result);

var_dump($result);