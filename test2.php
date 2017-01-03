<?php
header('Content-Type: text/html; charset=utf8');

//  线上
$asktime    = '1482163200';
$dbConf = array(
    'host'=>'10.52.8.21',
    'user'=>'chinaedu',
    'password'=>'chinaedu',
    'dbName'=>'dayi',
    'charSet'=>'utf8',
    'port'=>'3306'
);
////  测试
$asktime    = '1382163200';
$dbConf = array(
    'host'=>'172.16.9.52',
    'user'=>'ceshi',
    'password'=>'prcedu',
    'dbName'=>'dayi',
    'charSet'=>'utf8',
    'port'=>'3306'
);

$pdo    =   myPDO::getInstance($dbConf);
$statistics = "select qid from cy_question where asktime>".$asktime." and (auid!='' or auid!=0);";
$rs     = $pdo->query($statistics);
$data   = $rs->fetchAll(PDO::FETCH_ASSOC);  //取出所有结果

if(!empty($data)){
    foreach($data as $index => $item){
        $sql    = 'select * from cy_question_extend where qid='.$item['qid'].';';
        $singleRs = $pdo->query($sql);
        $singleQuestion = $singleRs->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($singleQuestion)){
            myPDO::addListes($singleQuestion);    //  添加结果到listes中
        }
    }
}

myPDO::function(myPDO::getListes());






class myPDO{
    private static $pdo;

    private static $filetypes  = array(
        'tutor_purview',    //  jpg（大）
        'thumbnail_small',//  jpg（小）
        'voice_video',      //  MP4
        'audio',            //  MP3
        't_voice_whiteboard',   //  .draw
        's_voice_whiteboard',   //  ???
    );
    //  测试DB
    private static $dbConf  = array(
        'host'=>'172.16.9.52',
        'user'=>'ceshi',
        'password'=>'prcedu',
        'dbName'=>'dayi',
        'charSet'=>'utf8',
        'port'=>'3306'
    );
    //  线上DB
//    private static $dbConf = array(
//        'host'=>'10.52.8.21',
//        'user'=>'chinaedu',
//        'password'=>'chinaedu',
//        'dbName'=>'dayi',
//        'charSet'=>'utf8',
//        'port'=>'3306'
//    );

    private static $listes  = array();

    private function __construct(){
        //code
    }

    public static function getInstance($dbConf){
        if(!(self::$pdo instanceof PDO)){
            $dsn ="mysql:host=".$dbConf['host'].";port=".$dbConf['port'].";dbname=".$dbConf['dbName'].";charset=".$dbConf['charSet'];
            try {
                self::$pdo = new PDO($dsn,$dbConf['user'], $dbConf['password'], array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //保持长连接
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            } catch (PDOException $e) {
                print "Error:".$e->getMessage()."<br/>";
                die();
            }
        }
        return self::$pdo;
    }

    public static function addListes($questionList){
        foreach($questionList as $index=> $item){
            if(in_array($item['fieldname'],self::$filetypes)){
                //  修改文件路径
                $relationPath   = self::getRelativelyPath($item['content']);
                $item['contents']   = $relationPath;
                //  下载文件
                //  self::curlGetFile($item['content'],$relationPath,1);
                //  测试线上地址
                $item['content']    = 'http://211.144.154.195//./uploads/image/q2621704/20161020081953_360.jpg';
//                $item['content']    = 'http://211.144.154.195//./uploads/text/q2621704/20161020081953_225.draw';

                //  判断文件是否存在 并插入数据
                if(self::checkFileExists($item['content']) && self::insertDB(self::getSaveItem($item,$relationPath))){
                    //  压入listes
                    array_push(self::$listes,$item);
                    //  睡眠0.5s
                    usleep(500*1000);

                }
            }
        }
    }

    //  插入数据库操作
    public static function insertDB($insertSql){
        $pdo    =   self::getInstance(self::$dbConf);
        $flag   = $pdo->exec($insertSql);
        if($flag){
            return true;
        }
        return false;
    }
    //  获取保存的数据
    public static function getSaveItem($item,$savePath){
        unset($item['id']);
        unset($item['contents']);
        $item['content']    = $savePath;
        $flagV  = 1;
        foreach($item as $key => $value) {
            if($flagV) {
                $name = "$key";
                $values = "('$value'";
                $flagV = 0;
            } else {
                $name .= ",$key";
                $values .= ",'$value'";
            }
        }
        $values .= ") ";
        return "insert into cy_question_extend ($name) values $values";
    }
    //  检查文件是否存在
    public static function checkFileExists($url){
        $curl = curl_init($url);
        // 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        //  超时时间
//        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
        // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    }

    //  下载文件到指定的 相对目录
    public static function curlGetFile($url,$path,$type=0){
//        $url  = 'upload/741.zip';
//        $path   = 'uploadto/741.zip';

        $baseinfo   = pathinfo($path);
        $save_dir   = $baseinfo['dirname'];
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        if($type){
            $ch = curl_init ();
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt ( $ch, CURLOPT_URL, $url );
            $img = curl_exec ( $ch );
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img    =   ob_get_contents();
            ob_end_clean();
        }
        $fp= @fopen($path,"w");
        fwrite($fp,$img);
        fclose($fp);
    }

    //  获取相对路径
    public static function getRelativelyPath($path){
        $pathes = explode('/',$path);
        $ext    = array('upload','uploads');
        foreach($pathes as $index => $item){
            if(!in_array($item,$ext)){
                unset($pathes[$index]);
            }else{
                break;
            }
        }
        return './'.implode($pathes,'/');
    }

    //  获取插入数据的列表
    public static function getListes(){
        return self::$listes;
    }

    public static function pWrite($data){
        file_put_contents("test.txt",json_encode($data));
    }
}



?>