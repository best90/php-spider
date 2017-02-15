#!/usr/local/php/bin/php
<?php
require('./init.php');
require(ROOT_PATH . '/lib/Core/Core.php');
require(ROOT_PATH . '/lib/Core/Base.php');
require(ROOT_PATH . '/crawl/spider.class.php');

$param = (isset($argv[1]) && !empty($argv[1])) ? trim($argv[1]) : '';

if(strpos($param, '/')){
    $param = explode('/', $param);
    $task = lcfirst($param[0]);
    $action = lcfirst($param[1]);
    $file = ROOT_PATH .'/crawl/'.$task.'.class.php';

    if(count($param) > 2){
        $tmp_param = array_slice($param, 2);

        $i = 0;
        while ($i < count($tmp_param)){
            try{
                if(isset($tmp_param[$i+1])){
                    $_GET[trim($tmp_param[$i])] = trim($tmp_param[$i+1]);
                }else{
                    throw new Exception('param \''.$tmp_param[$i].'\' value is missing.');
                }
            }catch (Exception $e) {
                echo $e->getMessage().EOL;
            }

            $i += 2;
        }
    }
}else{
    $task = lcfirst($param);
    $file = ROOT_PATH .'/crawl/'.$task.'.php';
}

if(file_exists($file)){
    require_once($file);

    if(isset($action)){
        $task = ucfirst($task);
        $class = new $task();

        if(method_exists($class, $action)){

            $dir_name = strpos($task, '_') ? end(explode('_', $task)) : lcfirst($task);

            $cache_dir = CACHE_PATH.'/'.$dir_name.'/';
            $cache = $cache_dir.$action.'/';
            if(! file_exists( $cache_dir)) mkdir($cache_dir);
            if(! file_exists( $cache )) mkdir($cache);

            if(method_exists($class, 'setCache')) $class->setCache($cache);
        }

        if(isset($_GET['loop'] )){
            $loop = 0;
            while ($loop < intval($_GET['loop'])){
                $class->$action();
                $loop ++;
            }
        }else{
            $class->$action();
        }
    }
}else{
    echo "ERROR TASK : ".lcfirst($task);
}