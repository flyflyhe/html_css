<?php

$file = "/waili.mp4";
header("Content-type: video/mp4");
header("Content-Length: ". filesize($file));
readfile($file);