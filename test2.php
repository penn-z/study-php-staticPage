<?php
    ob_start();
    echo 'just test';
    file_put_contents('./make/test2.html', ob_get_contents()); //从缓冲区获取数据，写入html文件。
    ob_clean(); //清空输出缓冲区，这样会把echo的内容也清空了（因为其还在php buffer中就给清空了）
    
    ob_start();
    echo 'just 2';
    file_put_contents('./make/test2.html', ob_get_clean()); //可知，ob_get_clean() == ob_get_contents() + ob_clean()
    require_once './make/test2.html';
?>
