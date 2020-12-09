<?php

//判断所请求的资源是否存在
$header = get_headers('http://www.baidu.com/');
print_r($header[0]);
echo "<br>", "<br>";

$html = file_get_contents('http://www.baidu.com/');
print_r($http_response_header); //自动创建保存HTTP响应的报头
echo "<br>", "<br>";

$fp = fopen('http://www.baidu.com/', 'r');
print_r(stream_get_meta_data($fp));
echo "<br>", "<br>";
fclose($fp);
//fstat();

$data = [
    'word' => '郎涯工作室',
    'ie'   => 'utf-8',
];
$data = http_build_query($data);
$opts = [
    'http' => [
        'method'  => 'GET',
        'header'  => "Content-type:application/x-www-form-urlencoded\r\n" .
                     "Content-Length:" . strlen($data) . "\r\n",
        "Content" => $data
    ]
];

$context = stream_context_create($opts);
$html = file_get_contents('http://www.baidu.com/s', false, $context);
print_r($http_response_header); //自动创建保存HTTP响应的报头