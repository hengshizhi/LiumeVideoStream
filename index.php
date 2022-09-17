<?php
$configure = read('configure.json');
include ("liume.php");
ini_set('memory_limit', $configure['ini_set']); //修改脚本的最大运行内存
set_time_limit($configure['set_time_limit']); //设置超时限制为 10分钟

<<<<<<< HEAD
=======
ini_set('memory_limit', '1024M'); //修改脚本的最大运行内存
set_time_limit(600); //设置超时限制为 10分钟

>>>>>>> aa9e54a99df7b55f548fd9da282bcf61eb0a057d
$wiki = $_GET['wiki'];
$api = $_GET['api'];
$url = $_GET['url'];
$ini_set = $_GET['ini_set'];  //脚本的最大运行内存
$set_time_limit = $_GET['set_time_limit'];  //超时时间

/**
 * wiki=yes 开启使用提升
 */
if($wiki == 'yes'){
    echo '传api=playVideoStream输出视频流,传api=ChangeSetting修改配置<br>';
    echo '传入relativeORabsolute = relative or absolute(相对/绝对,默认相对),url = 视频路径(相对/绝对)<br> ';
    echo 'url  //相对的路径<br>ini_set  //脚本的最大运行内存<br>set_time_limit //超时时间<br><br>';
}

/**
 * api控制流程
 */
switch ($api){
    case 'playVideoStream':
        @playVideoStream_get($url,$api);
        break;
    case 'ChangeSetting':
        @ChangeSetting_get($url,$ini_set,$set_time_limit);
        break;
    default:
        echo '参数形式不正确<br>';
}

/**
 * @param $url: 视频地址
 * @param $api: 配置文件读取/网络直接请求
 * @return void
 */
function playVideoStream_get($url,$api){
    $relativeORabsolute = $api ? 'absolute':'relative';
<<<<<<< HEAD
    playVideoStream($relativeORabsolute,$url?:"配置文件读取");
=======
    $url ?  playVideoStream($relativeORabsolute, $url):playVideoStream($relativeORabsolute, "配置文件读取");
>>>>>>> aa9e54a99df7b55f548fd9da282bcf61eb0a057d
}

/**
 * @param $relativeORabsolute: [relative or absolute][视频路径(配置文件读取/网络直接请求)]
 * @param $url: 网络直接请求
 * @return bool
 */
function playVideoStream($relativeORabsolute , $url){ //传入
    $data = read('configure.json');
    ini_set('memory_limit', $data['ini_set']);
    set_time_limit($data['set_time_limit']);
    if($relativeORabsolute == 'relative'){
        //读取配置
        //设置脚本的最大运行内存(1024M)
        //设置600超时限制为 10分钟
        outPutStream($data['url'].$url);
    } else{
        //读取配置
        //设置脚本的最大运行内存(1024M)
        //设置600超时限制为 10分钟
        outPutStream($url);
    }
    return TRUE;
}
/**
 * 修改configure
 * @param $url: 视频的根目录地址
 * @param $ini_set: 脚本的最大运行内存
 * @param $set_time_limit: 超时时间
 */
function ChangeSetting_get($url,$ini_set,$set_time_limit){
    !empty($url) ?  $data['url'] = $url:print "???";
    !empty($ini_set) ?  $data['ini_set'] = $ini_set:print "???";
    !empty($set_time_limit) ?  $data['set_time_limit'] = $set_time_limit:print "???";
    if (json_new($data,'configure.json')){
        echo '<b>修改配置成功</b><br>';
        $data = read('configure.json');  //读取
        echo "修改内容:<br>url => {$data['url']}<br>ini_set => {$data['ini_set']}<br>set_time_limit => {$data['set_time_limit']}";
    }else{
        echo "修改失败???";
    }
<<<<<<< HEAD
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
=======
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


//liume核心输出
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
    $ch2 = curl_init($videoUrl); //"https://cdn.seclusion.work/public/video/DNA%20%E4%B9%B1%E4%BA%86.mp4"
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
>>>>>>> aa9e54a99df7b55f548fd9da282bcf61eb0a057d
