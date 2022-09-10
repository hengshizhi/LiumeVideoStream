<?php

/*
使用read('configure.json')可以读取配置
*/
ini_set('memory_limit', '1024M'); //修改脚本的最大运行内存
set_time_limit(600); //设置超时限制为 10分钟
 
//输出视频流
function outPutStream($videoUrl) {
    
    if(!$videoUrl){
        header('HTTP/1.1 500 Internal Server Error');
        echo "Error: Video cannot be played !";
	    exit();
    }
    
    //获取视频大小
    $header_array = get_headers($videoUrl, true);
    $sizeTemp = $header_array['Content-Length'];
    if (is_array($sizeTemp)) {
        $size = $sizeTemp[count($sizeTemp) - 1];
    } else {
        $size = $sizeTemp;
    }
   
    //初始参数
    $start = 0;
    $end = $size - 1;
    $length = $size;
    $buffer = 1024 * 1024 * 5; // 输出的流大小 5m
    
    //计算 Range
    $ranges_arr = array();
    if (isset($_SERVER['HTTP_RANGE'])) {
        
        if (!preg_match('/^bytes=\d*-\d*(,\d*-\d*)*$/i', $_SERVER['HTTP_RANGE'])) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
        }
        $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
        foreach ($ranges as $range) {
            $parts = explode('-', $range);
            $ranges_arr[] = array($parts[0], $parts[1]);
        }
        $ranges = $ranges_arr[0];
        $start = (int)$ranges[0];
        if ($ranges[1] != '') {
            $end = (int)$ranges[1];
        }
        $length = min($end - $start + 1, $buffer);
        $end = $start + $length - 1;
    }else{
        
        // php 文件第一次浏览器请求不会携带 RANGE 为了提升加载速度 默认请求 1 个字节的数据
        $start=0;
        $end=1;
        $length=2;
    }
 
    //添加 Range 分段请求
    $header = array("Range:bytes={$start}-{$end}");
    #发起请求
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $videoUrl);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, $header);
    //设置读取的缓存区大小
    curl_setopt($ch2, CURLOPT_BUFFERSIZE, $buffer);
    // 关闭安全认证
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
    //追踪返回302状态码，继续抓取
    curl_setopt($ch2, CURLOPT_HEADER, false);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch2, CURLOPT_NOBODY, false);
    curl_setopt($ch2, CURLOPT_REFERER, $videoUrl);
    //模拟来路
    curl_setopt($ch2, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36 Edg/85.0.564.44");
    $content = curl_exec($ch2);
    curl_close($ch2);
    #设置响应头
    header('HTTP/1.1 206 PARTIAL CONTENT');
    header("Accept-Ranges: bytes");
    header("Connection: keep-alive");
    header("Content-Type: video/mp4");
    header("Access-Control-Allow-Origin: *");
    //为了兼容 ios UC这类浏览器 这里加个判断 UC的 Content-Range 是 起始值-总大小减一
    if($end!=1){
        $end=$size-1;
    }
    header("Content-Range: bytes {$start}-{$end}/{$size}");
    //设置流的实际大小
    header("Content-Length: ".strlen($content));
    //清空缓存区
    ob_clean();
    //输出视频流
    echo $content;
    //销毁内存
    unset($content);
}
 
//写入或创建json文件
function json_new($arr,$file_name){     //数据，文件
    $json_string = json_encode($arr);//
    $a = file_put_contents($file_name,$json_string);
    if($a == TRUE){
        return TRUE;
    } elseif($a == FALSE) {
        return FALSE;
    }
}
//读取json文件
function read($user){   //传入文件名
    // 从文件中读取数据到PHP变量
    $json_string = file_get_contents($user);
    // 把JSON字符串转成PHP数组
    $data = json_decode($json_string, true);
    // 输出
    return $data;  //返回一个数组
}
//修改json
function modify_ByArray($xgzd,$data,$file_name){  //修改键值，修改后的值，文件名
    $json = read_json($file_name);  //读取文件
    $json[$xgzd] = $data;    //修改值
    $a = new_json($json,$file_name);   //写入
    return $a;
}
/**
 * 判断数组为几维数组 可优化
 * @param array $array
 * @param int $count
 * @return int
 */
function foreachArray($array = [], $count = 0){

    if (!is_array($array)){
        return $count;
    }
    foreach ($array as $value){
        $count++;
        if (!is_array($value)){
            return $count;
        }
        return foreachArray($value, $count);
    }
}
//outPutStream('url')
//输出视频流
//echo urldecode('http://127.0.0.1/PHP Video Stream/Faded - Alan Walker (Piano Orchestral Cover Mathias Fritsche).mp4');

function playVideoStream($relativeORabsolute = 'relative' , $url){ //传入 [relative or absolute][视频路径(相对/绝对)]
 if($relativeORabsolute == 'relative'){
    $data = read('configure.json');  //读取配置
    ini_set('memory_limit', $data['ini_set']); //设置脚本的最大运行内存(1024M)
    set_time_limit($data['set_time_limit']); //设置600超时限制为 10分钟
    outPutStream($data['url'].$url);
    return TRUE;
 } elseif ($relativeORabsolute == 'absolute'){
    $data = read('configure.json');  //读取配置
    ini_set('memory_limit', $data['ini_set']); //设置脚本的最大运行内存(1024M)
    set_time_limit($data['set_time_limit']); //设置600超时限制为 10分钟
    outPutStream($url);
    return TRUE;
 } else {
    return FALSE;
 }

}

function playVideoStream_get(){
    if($_GET['wiki'] == 'yes'){
        echo '传入relativeORabsolute = relative or absolute(相对/绝对,默认相对),url = 视频路径(相对/绝对)<br> ';
    }
    $url = $_GET['url'];
    if($relativeORabsolute != TRUE){
        $relativeORabsolute = 'relative';
    } else {
        $relativeORabsolute = $_GET['relativeORabsolute'];
    }
    if($url == TRUE){
        playVideoStream($relativeORabsolute, $url);  //输出视频
    } elseif($url != TRUE) {
        echo 'FALSE';
    }
}
/*
    修改配置:
        配置结构：
        'url' => 视频的根目录地址
        'ini_set' => 脚本的最大运行内存
        'set_time_limit' => 超时时间
*/
function ChangeSetting($url,$ini_set,$set_time_limit){
    if($url != null){
        $data['url'] = $url;
    }
    if($ini_set != null){
        $data['ini_set'] = $ini_set;
    }
    if($set_time_limit != null){
        $data['set_time_limit'] = $set_time_limit;
    }
    return json_new($data,'configure.json'); //写入配置
}
function ChangeSetting_get(){
    if($_GET['wiki'] == 'yes'){
        echo '
        url  //相对的路径<br>
        ini_set  //脚本的最大运行内存<br>
        set_time_limit //超时时间<br>
        <br>';
    }
    $url = $_GET['url'];
    $ini_set = $_GET['ini_set'];  //脚本的最大运行内存
    $set_time_limit = $_GET['set_time_limit'];  //超时时间
    if(ChangeSetting($url,$ini_set,$set_time_limit) == TRUE){
        echo '<b>修改配置成功</b><br>';
        $data = read('configure.json');  //读取
        echo "修改内容:<br>url => {$data['url']}<br>ini_set => {$data['ini_set']}
        <br>set_time_limit => {$data['set_time_limit']}" ;
    }
}


if($_GET['wiki'] == 'yes'){
    echo '传api=playVideoStream输出视频流,传api=ChangeSetting修改配置<br>';
}
switch ($_GET['api']){
    case 'playVideoStream':
        @playVideoStream_get();
        break;
    case 'ChangeSetting':
        @ChangeSetting_get();
        break;
    default:
        echo '参数形式不正确<br>';
}

