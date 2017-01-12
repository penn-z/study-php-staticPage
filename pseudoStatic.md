## 1. 伪静态
> * #### PHP处理
> * #### 服务器配置

### PHP处理
> 例如：  

我们要把 http://www.mvc.org/index.php?controller=index&method=index 转换为=> http://www.mvc.org/index/index.html

这个...待补充，我装的是ubuntun14.04，apache版本号为2.4.7。一时还找不到如何开启PATH_INFO这个功能...
* * *

## 2. 服务器配置(apache、nginx)
> ### *<font color=red>apache下rewrite配置</font>*
1. 虚拟域名配置
2. httpd_vhosts.conf配置文件配置相关信息(ubuntu下为000-default.conf)

> 虚拟域名配置

*Windows下：*
1. httpd.conf文件中开启相关模式：把 <font color=blue>LoadModule rewrite_module modules/mod_rewrite.so</font> 此行注释取消。<font color=blue>Include conf/extra/httpd-vhosts.conf</font> 注释也取消,此时就会引入httpd-vhosts.conf文件
2. 在httpd-vhosts.conf文件下：  
```
<VirtualHost 127.0.0.xx:80>           # xx表示自己分配最后一段ip，80为端口
    ServerAdmin webmaster@localhost
    DocumentRoot xxx/xxx/xxx          # 此处表示为项目根目录，根据自己的实际情况设置根目录
    ServerName www.demo.org           # 自己设置的虚拟主机域名
    ErrorLog xxxxxxxx                 # 错误日志地址，一般不改动
    CustomLog xxxxxxxx xxxxx          # 日志地址，格式，一般不改动
</VirtualHost>
```
3. 修改hosts文件：  
添加一行地址  `127.0.0.xx www.demo.org ` "xx" 与 httpd.vhosts.conf文件中 `<VirtualHost 127.0.0.xx:80>`的xx一致
4. 重启apache后，在浏览器的url中输入刚才配置的 www.demo.org 即可访问项目目录
5. 在httpd-vhosts.conf中书写伪静态规则：
```
<VirtualHost 127.0.0.xx:80>           # xx表示自己分配最后一段ip，80为端口
    ServerAdmin webmaster@localhost
    DocumentRoot e:/test          # 此处表示为项目根目录，根据自己的实际情况设置根目录
    ServerName www.demo.org           # 自己设置的虚拟主机域名
    ErrorLog xxxxxxxx                 # 错误日志地址，一般不改动
    CustomLog xxxxxxxx xxxxx          # 日志地址，格式，一般不改动
    RewriteEngine on
    RewriteRule ^/detail/([0-9]+).html$ /detail.php?id=$1                 
    # 此处示例为匹配/detail/1(0~9都可).html 然后指向 /detail.php?id=1(0~9)，$1表示前面正则式中第一个()中匹配的内容。此处仅为示例，规则根据自己实际情况改动
</VirtualHost>
```
6. 重启apache后，在浏览器中访问 www.demo.org/detail/123.html 便可以访问e:/test/detail.php文件啦。  
在detail.php中写
```php
<?php
    echo $_GET['id'];
?>
```
便可以输出123
7. 但如果存在e:/test/detail/123.html会怎么样呢，在上述情况下，依然会访问detail.php。此时需要在httpd-vhosts.conf中修改配置:
```
<VirtualHost 127.0.0.xx:80>           
    ServerAdmin webmaster@localhost
    DocumentRoot e:/test          
    ServerName www.demo.org           
    ErrorLog xxxxxxxx                 
    CustomLog xxxxxxxx xxxxx          
    RewriteEngine on
    RewriteRule ^/detail/([0-9]+).html$ /detail.php?id=$1                 
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME}!-d
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME}!-f
</VirtualHost>
```
重启后，便会访问123.html这个静态文件
8. 其实，只要把apache的rewrite功能打开，然后在项目根目录下创建.htaccess文件，里面写伪静态规则，这样更为方便

> ### *<font color=red>nginx下rewrite配置</font>*
*Ubuntu下：*  
1. 在/etc/nginx/conf.d/default.conf文件中
```
location / {
    if (!-e $request_filename){
        rewrite ^/index.php(.*)$ /index.php?s=$1 last;
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```
访问/index.php?xxx=xx&xx=xx 都会访问成/xxx=xx&xx=xx
