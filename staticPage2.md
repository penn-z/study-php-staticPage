## 1. Buffer认知
buffer其实就是缓冲区，一个内存地址空间，主要用于存储数据区域。

buffer是一个内存地址空间,Linux系统默认大小一般为4096(4kb),即一个内存页。主要用于存储速度不同步的设备或者优先级不同的设备之间传办理数据的区域。通过buffer，可以使进程这间的相互等待变少。这里说一个通俗一点的例子，你打开文本编辑器编辑一个文件的时候，你每输入一个字符，操作系统并不会立即把这个字符直接写入到磁盘，而是先写入到buffer，当写满了一个buffer的时候，才会把buffer中的数据写入磁盘，当然当调用内核函数flush()的时候，强制要求把buffer中的脏数据写回磁盘。

同样的道理，当执行echo,print的时候，输出并没有立即通过tcp传给客户端浏览器显示, 而是将数据写入php buffer。php output_buffering机制，意味在tcp buffer之前，建立了一新的队列，数据必须经过该队列。当一个php buffer写满的时候，脚本进程会将php buffer中的输出数据交给系统内核交由tcp传给浏览器显示。所以，数据会依次写到这几个地方：echo/print -> php buffer -> tcp buffer -> browser
* * *
## 2. PHP output_buffering
* #### *输出流程*
  
##### 内容 ---> php buffer ---> tcp buffer ---> 终端

默认情况下，php buffer是开启的，而且该buffer默认值是4096，即4kb。你可以通过在php.ini配置文件中找到output_buffering配置.当echo,print等输出用户数据的时候，输出数据都会写入到php output_buffering中，直到output_buffering写满，会将这些数据通过tcp传送给浏览器显示。你也可以通过ob_start()手动激活php output_buffering机制，使得即便输出超过了4kb数据，也不真的把数据交给tcp传给浏览器，因为ob_start()将php buffer空间设置到了足够大。只有直到脚本结束，或者调用ob_end_flush函数，才会把数据发送给客户端浏览器。

> 测试：

**ob_get_contents()函数**

php已经默认开启了4kb的buffer缓冲区。
```php
<?php
    echo 'Hello,PHP';
    echo '<br/>';
    echo ob_get_contents();
?>
//结果会输出
Hello,PHP
Hello,PHP
```
如果php.ini配置文件中的output_buffering关闭了的话，可以利用ob_start()函数手动开启php buffer
```php
<?php
    ob_start();
    echo 'Hello,PHP';
    echo '<br/>';
    echo ob_get_contents();
?>
//输出的结果和上例相同
```
* * *
## 3. PHP如何实现页面纯静态化
* ### 基本方式
1. file_put_contents()函数
2. 使用PHP内置缓存机制实现页面静态化 -- output_buffering
3. OB函数：  
ob_start()          ---->   打开缓冲区  
ob_get_contents()   ---->   返回输出缓冲区内容  
ob_clean()          ---->   清空输出缓冲区  
ob_get_clean()          ---->   先返回并清空输出缓冲区的内容，再关闭输出缓冲区

> 示例：  
```php
<?php
ob_start();
echo 'just test';
file_put_contents('./make/test2.html', ob_get_contents()); //从缓冲区获取数据，写入html文件。
ob_clean(); //清空输出缓冲区，这样会把echo的内容也清空了（因为其还在php buffer中就给清空了）
?>
```
> 再或：  
```php
<?php
ob_start();
echo 'just 2';
file_put_contents('./make/test2.html', ob_get_clean()); //可知，ob_get_clean() == ob_get_contents() + ob_clean()
?>
```
* * *
## 4. 如何触发系统生成纯静态化页面
 1. 页面添加缓存时间
 2. 手动触发方式
 3. crontab定时扫描程序

> #### 1. 页面添加缓存时间
用户请求页面  ---> 页面时间是否过期 ？  
 是--->`动态页面并生成一份新的静态页面`  
 否--->`获取静态页面`
 
 ##### *<font color=red>示例见test目录</font>*


> #### 2. 手动触方式
写一个更新数据的功能模块，每当有数据更新，我们可以手动更新（重新生成）静态页面。

> #### 3. Linux crontab定时任务程序
通过Linux的crontab程序 定时执行生成静态缓存页面的任务
* * *

## 5.静态化页面中如果想要加载动态的内容如何处理（局部动态化）？
* ### <font color=blue>ajax技术</font>
