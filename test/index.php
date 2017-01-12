<?php
require_once 'template.class.php';

$baseDir = str_replace('\\','/',dirname(__FILE__));	//当前文件所在目录路径,把Windows下的\替换成/
$temp = new template($baseDir.'/source/', $baseDir.'/compile/');
// $temp->setCache();  //开启缓存，时间300秒
$temp->assign('pageTitle','正则替换测试');
$temp->assign('test','你好哇！正则匹配~~');
$arr = array(1,2,3,4,5,6,7);
$temp->assign('arr',$arr);
$num1 = 30;
$temp->assign('num1',$num1);
$temp->display();	//显示编译后的文件
/*
补充：if、foreach、include、$data[$key]等语法的补充，php内置函数
改进：检测是否存在已编译的相应文件，若存在且模板文件无改动，则直接使用已存在的编译文件。不存在编译文件或模板源文件修改后，才重新编译模板。
可把getSourceTemplate()与compileTemplate()两方法写入display()方法中去调用。
在display中判断编译文件与模板文件的新旧关系（两者修改时间比较）
*/
