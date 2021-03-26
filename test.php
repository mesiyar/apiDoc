<?php

require 'src/ApiDoc.php';

$dir = './test';

// 获取文件下的所有文件

function flist($path, $rpath = '', &$ret = [])
{
    $f =  array();
    if ($files = scandir($path)) {
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $rpath = trim(str_replace('\\', '', $rpath), '/') . '/';
            $fpath = $path . '/' . $file;
            if (is_dir($fpath)) {
                $f = array_merge($f, flist($fpath, $rpath . $file));
            } else {
                if (substr($file, strlen($file) - 4) == '.php') {
                    $ret[] = [
                        'file_name' => ltrim($rpath . $file, '/'),
                        'class_name' => substr($file, 0, strlen($file) - 4),
                    ];
                }

            }
        }
    }
    $f = array_reverse($f);
    return $f;
}
$out = [];
 flist($dir, '',$out);
$return = [];
foreach ($out as $item) {
    include $dir.'/'. $item['file_name'];
    $class = new $item['class_name'];
    $ref = new \ReflectionClass($class);
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        $document = $method->getDocComment();
        if(!empty($document) && preg_match('/@api/', $document)) {
            $document = explode("\r\n", $document);
            $tmp = [];
            foreach ($document as $item) {
                // 请求方法
                if(preg_match('/@method/', $item)) {
                    $tmp['method'] = trim(str_replace('* @method', '', $item));
                }
                // 描述
                if(preg_match('/@description/', $item)) {
                    $tmp['description'] = trim(str_replace('* @description', '', $item));
                }
                // 请求地址
                if(preg_match('/@path/', $item)) {
                    $tmp['path'] = trim(str_replace('* @path', '', $item));
                }

                // 参数
                if(preg_match('/@params/', $item)) {
                    $param = trim(str_replace('* @params', '', $item));
                    $param = explode(' ', $param);
                    $tmp['params'][] = [
                        'param' => $param[0],
                        'type' => $param[1],
                        'description' => $param[2] ?? '',
                    ];
                }

                // 请求地址
                if(preg_match('/@author/', $item)) {
                    $tmp['author'] = trim(str_replace('* @author', '', $item));
                }
            }
            $return[] = $tmp;
        }
    }
}
var_dump($return);