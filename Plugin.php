<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * ShortCodes typecho 短代码插件 by 即刻学术
 * <div class="tp_shortcodes"><a style="width:fit-content" id="tp_shortcodes">版本检测中..</div>&nbsp;</div><style>.tp_shortcodes{margin-top: 5px;}.tp_shortcodes a{background: #ff5a8f;padding: 5px;color: #fff;}</style>
 * <script>var tp_shortcodes_ver="1.0.5";function update_detec(){var container=document.getElementById("tp_shortcodes");if(!container){return}var ajax=new XMLHttpRequest();container.style.display="block";ajax.open("get","https://api.github.com/repos/gogobody/ShortCodes/releases/latest");ajax.send();ajax.onreadystatechange=function(){if(ajax.readyState===4&&ajax.status===200){var obj=JSON.parse(ajax.responseText);var newest=obj.tag_name;if(newest>tp_shortcodes_ver){container.innerHTML="发现新版本："+obj.name+'。下载地址：<a href="'+obj.zipball_url+'">点击下载</a>'+"<br>您目前的版本:"+String(tp_shortcodes_ver)+"。"+'<a target="_blank" href="'+obj.html_url+'">👉查看新版亮点</a>'}else{container.innerHTML="您目前的版本:"+String(tp_shortcodes_ver)+"。"+"您目前使用的是最新版。"}}}};update_detec();</script>
 * @package ShortCodes
 * @author gogobody
 * @version 1.0.5
 * @link https://ijkxs.com
 */
require_once 'component/TOC.php';
//require(__DIR__ . DIRECTORY_SEPARATOR . "Action.php");
require_once 'core/ShortCodeCore.php';
require_once 'core/constants.php';
class ShortCodes_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 已注册的短代码列表
     *
     * @access private
     * @var array
     */
    private static $ShortCodes = [];

    /**
     * 实例
     *
     * @access private
     * @var array
     */
    private static $instance = null;

    /**
     * 是否强制处理文本
     *
     * @access public
     * @var bool
     */
    public static $isForce = true;

    /**
     * 注册短代码
     *
     * @access public
     * @param mixed $names 短代码名称，可以一个字符串或字符串数组
     * @param mixed $callbacks 短代码对应回调函数，可以一个回调函数或回调函数数组
     * @param bool $overried 覆盖已注册的短代码<br>可选，默认<code>false</code>
     */
    public static function set($names,$callbacks,$overried = false){
        if(!is_array($names)) $names = [$names];
        if(!is_array($callbacks)) $callbacks = [$callbacks];
        $i = count($callbacks)-1;
        foreach($names as $j => $name){
            $k = $j;
            if($i<$j) $k = $i;
            $callback = $callbacks[$k];
            if(!array_key_exists($name,self::$ShortCodes)||$overried)
                self::$ShortCodes[$name] = $callback;
        }
        return self::instance();
    }

    /**
     * 移除短代码
     *
     * @access public
     * @param string $name 短代码名称
     * @param callback $callback 只有回调函数相同，短代码才会被移除<br>可选，默认<code>Null</code>
     */
    public static function remove($name,$callback = null){
        if(isset(self::$ShortCodes[$name]))
            if(self::$ShortCodes[$name] === $callback||empty($callback))
                unset(self::$ShortCodes[$name]);
        return self::instance();
    }

    /**
     * 移除所有短代码
     *
     * @access public
     */
    public static function removeAll(){
        self::$ShortCodes[] = [];
        return self::instance();
    }

    /**
     * 获取短代码列表
     *
     * @access public
     * @return array
     */
    public static function get(){
        return self::$ShortCodes;
    }

    /**
     * 强制处理文本
     * 使用此插件后Markdown或AutoP失效，使用此函数，并传入<code>true</code>值
     * @access public
     * @param bool
     * @return bool
     */
    public static function isForce($bool = null){
        if(is_bool($bool)) self::$isForce = $bool;
        return self::$isForce;
    }

    /**
     * 文本处理
     *
     * @access public
     * @param string
     * @retur string
     */
    public static function handle($content, $obj = null){
        $pattern  = [];
        $RegExp = '((?:"[^"]*"|'."'[^']*'|[^'".'"\]])*)';
        foreach(array_keys(self::$ShortCodes) as $name)
            array_push($pattern,
                "#\\\\\[|\[($name)$RegExp\]([\s\S]*?)\[/$name\]#i",
                "#\\\\\[|\[($name)$RegExp\]()#i"
            );
        return preg_replace_callback($pattern,function($a) use ($obj) {
            if(count($a) == 1)
                return $a[0];
            $name = strtolower($a[1]);
            $ShortCodes = self::$ShortCodes;
            $callback = $ShortCodes[$name];
            if(array_key_exists($name,$ShortCodes)&&is_callable($callback)){
                $attrs_arr = array();
                foreach (explode(' ',trim($a[2])) as $data) {
                    $arr = explode('=',$data);
                    $attrs_arr[$arr[0]] = trim($arr[1],'"');
                }
                // markdown 需要特别处理n
                if($obj->isMarkdown){
                    return '!!!
                    '.call_user_func($callback, $name, $attrs_arr, $a[3], $a[0], $obj).'
                    !!!';
                }else {
                    return call_user_func($callback, $name, $attrs_arr, $a[3], $a[0], $obj);

                }
            }
            else
                return $a[0];
        },$content);
    }



    /**
     * 获取实例
     *
     * @access private
     */
    private static function instance(){
        return self::$instance?self::$instance:new ShortCodes_Plugin();
    }

    /**
     * 构造函数
     *
     * @access public
     */
    public function __construct(){
        self::$instance = $this;
    }

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // 全局 header
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'echoHeader');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'echoFooter');

        // 段代码接入
        Typecho_Plugin::factory('Widget_Abstract_Contents')->content = [__Class__, 'content'];
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = [__Class__, 'contentEx'];

        Typecho_Plugin::factory('admin/header.php')->header = array(__CLASS__, 'header');

        Typecho_Plugin::factory('Widget_Archive')->handleInit_1008 = array(__Class__, 'handleInit');

        // 添加选择按钮
        Typecho_Plugin::factory('admin/write-page.php')->option = array(__CLASS__, 'addOption');
        Typecho_Plugin::factory('admin/write-post.php')->option = array(__CLASS__, 'addOption');


        Typecho_Plugin::factory('admin/write-post.php')->bottom = array(__CLASS__, 'render');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array(__CLASS__, 'render');

    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $GzhImgUrl = new Typecho_Widget_Helper_Form_Element_Text('GzhImgUrl',null,'','公众号图片URL','输入公众号的二维码图片URL');
        $form->addInput($GzhImgUrl);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    // 全局 header
    public static function echoHeader($header,$obj)
    {
        if ($obj!=null) $header = $obj;
        $html ='<link href="/usr/plugins/ShortCodes/assets/css/shortcodes.css" rel="stylesheet" type="text/css" />';
        echo $html;
        return $header;

    }
    public static function echoFooter($footer,$obj)
    {
        if ($obj!=null) $footer = $obj;
        $html ='<script src="/usr/plugins/ShortCodes/assets/js/shortcodes.min.js"></script>';
        echo $html;
        return $footer;
    }



    public static function render()
    {
//        echo '<script src="https://cdn.bootcdn.net/ajax/libs/jqueryui/1.9.2/jquery.ui.dialog.min.js"></script>
//<script src="/usr/plugins/ShortCodes/js/admin.js"></script>';
        require_once 'component/meta.php';

    }

    public static function addOption($post)
    {
    }

    public static function handleInit($obj, $select)
    {
        ty_cookie_constants();
        ShortCodeCore::CodesInit(self::instance());
    }

    /**
     * 插件处理 content
     *
     * @access public
     * @param string
     * @param Widget_Abstract_Contents
     * @param string
     * @return string
     */
    public static function content($content,$archive,$last){
        global $typecho_archive;
        $typecho_archive = $archive;

        if($last) $content = $last;
        $content = self::handle($content, $archive);
        if(Typecho_Plugin::export()['handles']['Widget_Abstract_Contents:content'] === [[__Class__,__Function__]]||self::$isForce)
            return $archive->isMarkdown?$archive->markdown($content):$archive->autoP($content);
        return $content;
    }

    /**
     * 插件处理 contentEx
     *
     * @access public
     * @param string
     * @param Widget_Abstract_Contents
     * @param string
     * @return string
     */
    public static function contentEx($content,$archive,$last){
        global $typecho_archive;
        $typecho_archive = $archive;

        if($last) $content = $last;

        return TOC::build($content,$archive->is('single'));
    }

}
