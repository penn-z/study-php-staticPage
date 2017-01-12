<?php
/**
 * @description : 模板引擎类
 * @author : penn
 * @email : penn_z@aliyun.com
 * @date : 2016-12-20
 */
class template {

	private $templateDir;	//存储模板引擎源文件目录
	private $templateExt = '.html';	//模板文件扩展名
	private $templateFilename;	//模板文件完整路径名
	private $compileDir;	//编译后文件目录
	private $compileExt = '.html.php';	//编译文件的扩展名
	private $compileFilename;	//编译文件完整路径名
	private $leftTag = '{';	//模板语法左标签
	private $rightTag = '}';	//模板语法右标签
	private $needCache = false;	//是否需要缓存
	private $cacheTime;	//默认缓存时间

	private $outputHtml;	//正在编译的模板文件Html代码
	private $varPool = array();	//变量池，存储模板编译前变量值，编译完成后可从中获取

	private $T_P = array();	//存储待编译模板标签语法
	private $T_R = array();	//存储编译后对应的php语法

	/**
	 * 构造函数
	 * @param string $templateDir 模板文件所在目录
	 * @param string $compileDir  编译文件所在目录
	 * @param string $leftTag	模板语法开标签
	 * @param string $rightTag    模板语法闭标签
	 */
	public function __construct($templateDir, $compileDir, $leftTag = null, $rightTag = null) {
		$this->templateDir = $templateDir;
		$this->compileDir = $compileDir;
		if(!empty($leftTag)) $this->leftTag = preg_quote($leftTag);
		if(!empty($rightTag)) $this->rightTag = preg_quote($rightTag);
	}

	/**
	 * 数据写入变量池
	 * @description :编译模板前放置变量入变量池
	 * @param string $tag 变量标记
	 * @param mix $var 变量值
	 * @return void
	 */
	public function assign($tag, $var) {
		$this->varPool[$tag] = $var;	//存储变量值
	}

	/**
	 * 从变量池读取数据
	 * @access private
	 * @param string $tag 变量标记
	 * @return void
	 */
	private function getVar($tag) {
		return $this->varPool[$tag];	//返回变量值
	}

	/**
	 * 获取模板源文件
	 * @access private
	 * @return void
	 */
	private function getSourceTemplate() {
		$this->outputHtml = file_get_contents($this->templateFilename);	//获取源文件中内容,存储到outputHtml变量中
	}

	/**
	 * 模板编译
	 * @access private
	 * @return void
	 */
	private function compileTemplate() {

		$this->T_P[] = '/'.$this->leftTag.'\s*\$([a-zA-Z_]\w*)\s*'.$this->rightTag.'/';	// { $data }
		//{ foreach $data as $value }
		$this->T_P[] = '/'.$this->leftTag.'\s*foreach\s*\$([^\d]\w*)\s*as\s*\$([^\d]\w*)\s*'.$this->rightTag.'/i';
		// {key}-----{value}
		$this->T_P[] = '/'.$this->leftTag.'\s*(key|value)\s*'.$this->rightTag.'/i';
		// {if $data == 'abc'}
		$this->T_P[] = '/'.$this->leftTag.'\s*([^e][a-zA-Z]+)\s*\$([a-zA-Z_]\w*)(.*?)'.$this->rightTag.'/';
		$this->T_P[] = '/'.$this->leftTag.'\s*if\s+(.*?)'.$this->rightTag.'/i';	// { if 1 == '1' }
		$this->T_P[] = '/'.$this->leftTag.'\s*([a-zA-Z]+)\s*\$([a-zA-Z_]\w*)(.*?)'.$this->rightTag.'/';
		$this->T_P[] = '/'.$this->leftTag.'\s*(else if|elseif)\s+(.*?)'.$this->rightTag.'/i';// { else if $data == 'def' }
		$this->T_P[] = '/'.$this->leftTag.'\s*else\s*'.$this->rightTag.'/i';	// { else }
		$this->T_P[] = '/'.$this->leftTag.'\s*\/(if|foreach)\s*'.$this->rightTag.'/i';	// { /(if|foreach) }

		$this->T_R[] = '<?php echo $this->getVar("$1");?>';
		$this->T_R[] = '<?php foreach($this->getVar("$1") as $key=>$$2){?>';
		$this->T_R[] = '<?php echo \$$1;?>';
		$this->T_R[] = '<?php $1 ($this->getVar("$2")$3){?>';
		$this->T_R[] = '<?php if($1){?>';
		$this->T_R[] = '<?php }$1 ($this->getVar("$2")$3){?>';
		$this->T_R[] = '<?php }elseif($2){?>';
		$this->T_R[] = '<?php }else{?>';
		$this->T_R[] = '<?php }?>';

		// 进行正则替换
		$this->outputHtml = preg_replace($this->T_P, $this->T_R, $this->outputHtml);

		file_put_contents($this->compileFilename, $this->outputHtml);	//内容写入编译文件中
	}

	/**
	 * 是否需要重新编译文件
	 * @param string $compileFilename 编译文件完整路径名
	 * @return boolean
	 */
	private function isCompiled($compileFilename) {
		// 判断缓存文件是否存在
		if(!file_exists($compileFilename)) return false;	//编译文件不存在时
		// 存在编译文件，但不是最新状态
		if(filemtime($this->templateFilename) > filemtime($this->compileFilename)) return false;
		else return true;	//存在编译文件且处于最新状态
	}
	
	/**
	 * 缓存开关
	 * @param $bool boolean default = false
	 * @param $time int default = 300
	 */
	public function setCache($bool = false, $time = 300) {
		if($bool) {
			$this->needCache = $bool;
			$this->cacheTime = $time;
		} else {
			$this->needCache = false;
			$this->cacheTime = 0;
		}
	}
	
	/**
	 * 生成缓存文件
	 */
	private function makeCache($cacheFilename) {
		// 存在缓存文件且未过期，直接显示缓存文件
		var_dump($this->cacheTime);
		if( is_file($cacheFilename) && (time() - filemtime($cacheFilename) < $this->cacheTime) ) {
			include_once($cacheFilename);
		} else {	//不存在缓存文件或缓存过期
			ob_start();
			$this->compileProcess();
			file_put_contents($cacheFilename, ob_get_clean());
			include_once $cacheFilename;
		}
	}
	
	/**
	 * 编译模板过程
	 */
	private function compileProcess() {
		if(!file_exists($this->templateFilename)) {
			return die("文件名错误或不存在该文件");
		}
		// 判断是否需要生成编译文件(可能已经存在且在最新状态)
		if(!$this->isCompiled($this->compileFilename)) {
			$this->getSourceTemplate();
			$this->compileTemplate();
		}
		include_once $this->compileFilename;
	}
	 
	/**
	 * 渲染模板
	 * @param string $templateName 模板文件前缀名
	 * @param string $templateExt 模板文件扩展名
	 * @param string $compileExt 编译文件扩展名
	 * @return void
	 */
	public function display($templateName = 'index', $templateExt = '.html', $compileExt = '.html.php' ) {
		$this->templateExt = $templateExt;	//模板文件扩展名
		$this->templateFilename = $this->templateDir.$templateName.$this->templateExt;
		$this->compileExt = $compileExt;	//编译文件扩展名
		$this->compileFilename = $this->compileDir.md5($templateName).$this->compileExt;
		
		if( $this->needCache ) {	//开启了缓存
			$cacheFilename = './cache/'.md5($templateName).$this->templateExt;
			$this->makeCache($cacheFilename);
		} else {	//未开启缓存
			$this->compileProcess();
		}
	}

}
