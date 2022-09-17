<?php
$configure = read('configure.json');
include ("liume.php");
ini_set('memory_limit', $configure['ini_set']); //修改脚本的最大运行内存
set_time_limit($configure['set_time_limit']); //设置超时限制为 10分钟

$wiki = null;
$api = null;
$url = null;
$ini_set = null;
$set_time_limit = null;

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
 * @param $url: 视频地址
 * @param $api: 配置文件读取/网络直接请求
 * @return void
 */
function playVideoStream_get($url,$api){
    $relativeORabsolute = $api ? 'absolute':'relative';
    playVideoStream($relativeORabsolute,$url?:"配置文件读取");
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

