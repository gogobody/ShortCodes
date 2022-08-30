<?php
define('SC_ROOT', Helper::options()->pluginDir('ShortCodes'));
require_once SC_ROOT.'/core/parser.php';
require_once SC_ROOT.'/core/SCUtils.php';
require_once SC_ROOT.'/core/core.php';

class ShortCodeCore{


    public static function CodesInit($instance){
        //简码：[wm_tips]远方的雪山[/wm_tips]
        // 一、提示框
        ////////////////////////////////////////////////////////////

        //1.红色错误框
        $instance::set('wm_error',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="werror">'.$text.'</div>';
        },true);
        //2.绿色提醒框
        $instance::set('wm_notice',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wnotice">'.$text.'</div>';
        },true);
        //3.黄色警告框
        $instance::set('wm_warn',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wwarn">'.$text.'</div>';
        },true);
        //4.蓝色计划框
        $instance::set('wm_tips',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wtips">'.$text.'</div>';
        },true);
        // 二、文本框
        ////////////////////////////////////////////////////////////

        //1.虚线标题框
        $instance::set('wm_kuang',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            extract(shortcode_atts(array( "title" => "" ) , $attr));
            return '<div class="wfieldset"> <tt>' . $title . '</tt><a>' . $text . '</a></div>';
        },true);
        //2.虚线文本框
        $instance::set('wm_xuk',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wxuk">'.$text.'</div>';
        },true);
        //3.红边提示框
        $instance::set('wm_red',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wred">'.$text.'</div>';
        },true);
        //4.黄边提示框
        $instance::set('wm_yellow',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wyellow">'.$text.'</div>';
        },true);
        //5.蓝边提示框
        $instance::set('wm_blue',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wblue">'.$text.'</div>';
        },true);
        //6.绿边提示框
        $instance::set('wm_green',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return '<div class="wgreen">'.$text.'</div>';
        },true);
        // 三、按钮
        ////////////////////////////////////////////////////////////
        //简码：[wm_wpbutton link="链接地址" size="large" align="right"]链接名称[/wm_wpbutton]
        $instance::set('wm_wpbutton',function ($name,$attr,$text,$code,$obj){
            $text = preHandle($text,$obj);
            return wpbutton($attr,$text);
        },true);
        // 四、内容隐藏
        ////////////////////////////////////////////////////////////
        //评论可见
        $instance::set('wm_reply',function ($name,$attr,$text,$code,$obj){

            return do_shortcode($code,false,$obj);
        },true);
        //登录可见
        $instance::set('wm_login',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);

        },true);
        //关注微信公众号可见
        $instance::set('wm_gzh',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);

        },true);
        // 五、内容收缩
        ////////////////////////////////////////////////////////////
        //1. Tabs选项
        //简码：[wm_tabgroup][wm_tab title="标题 1" id="1"]内容 1[/wm_tab][wm_tab title="标题 2" id="2"]内容 2[/wm_tab] [wm_tab title="标题 3" id="3"]内容 3[/wm_tab][/wm_tabgroup]

        $instance::set('wm_tabgroup',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);
        },true);
        //2.开关菜单
        //简码：[wm_toggle_box][wm_toggle_item title="标题" active="true"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][/wm_toggle_box]

        $instance::set('wm_toggle_box',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);
        },true);
        //3. 阅读全文
        $instance::set('wm_collapse',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);
        },true);
        //4. 卡片内链
        $instance::set('wm_embed_post',function ($name,$attr,$text,$code,$obj){
            return do_shortcode($code,false,$obj);
        },true);

    }

}

