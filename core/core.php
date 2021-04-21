<?php

/*
 * 获取对应属性，并添加符号变量
 */
function shortcode_atts( $pairs, $atts, $shortcode = '' )
{
    $atts = (array)$atts;
    $out = array();
    foreach ($pairs as $name => $default) {
        if (array_key_exists($name, $atts)) {
            $out[$name] = $atts[$name];
        } else {
            $out[$name] = $default;
        }
    }
    return $out;

}
// 三、按钮
////////////////////////////////////////////////////////////
/**
 * button 函数
 * @param $atts
 * @param null $content
 * @return string
 */
function wpbutton( $atts, $content = null ) {
    extract(
        shortcode_atts(
            array(
                'link'      => '#',
                'target'    => '',
                'variation' => '',
                'size'      => '',
                'align'     => '',
            ),
            $atts
        )
    );

    $style = ($variation) ? ' '.$variation : '';
    $align = ($align) ? ' align'.$align : '';
    $size = ($size == 'large') ? ' large_button' : '';
    $target = ($target == 'blank') ? 'target="_blank"' : '';

    $out = '<a '.$target.' class="wpbutton '.$style.$size.$align.'" href="'.$link.'">'.$content.'</a>';

    return $out;
}

// 四、内容隐藏
////////////////////////////////////////////////////////////
// 评论可见
function wp_reply_to_read($atts, $content = null)
{
    global $typecho_archive;
    extract(
        shortcode_atts(
            array(
                "notice" => '<div class="whidebox">抱歉，隐藏内容须成功<a href="' . $typecho_archive->permalink . '#comments" title="评论本文"> 评论本文 </a>后刷新可见！</div>'
            ),
            $atts
        )
    );
    $email = null;
    Typecho_Widget::widget('Widget_User')->to($user);
    if ($user and $user->hasLogin()) {
        $email = $user->email;
        //对博主直接显示内容
        $admin_email = $typecho_archive->author->email; //博主Email
        if ($email == $admin_email) {
            return do_shortcode($content);
        }
    } else if (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
        $email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);
    } else {
        return $notice;
    }
    if (empty($email)) {
        return $notice;
    }
    $db = Typecho_Db::get();
    $coid = $db->fetchObject($db->select('coid')->from('table.comments')->where('cid =? and authorId = ?',$typecho_archive->cid,$typecho_archive->user->uid)->where('status = ?','approved'))->coid;
    if ($coid) {
        return do_shortcode($content);
    } else {
        return $notice;
    }
}

add_shortcode('wm_reply', 'wp_reply_to_read');
//简码：wm_reply]评论后可见内容[/wm_reply]

//登录可见
function wp_login_to_read($atts, $content = null)
{
    global $typecho_archive;

    extract(shortcode_atts(array("notices" => '<div class="whidebox">抱歉，隐藏内容须成功<a href="' . Helper::options()->loginUrl . '/login" etap="login_btn" title="登录"> 登录 </a>后刷新可见！</div>'), $atts));
    Typecho_Widget::widget('Widget_User')->to($user);
    if ($user and $user->hasLogin()) {
        return do_shortcode($content);
    } else {
        return $notices;
    }
}

add_shortcode('wm_login', 'wp_login_to_read');
//简码：[wm_login]只有用户才能看到的内容[/wm_login]

//关注微信公众号可见
function secrets_content($atts, $content = null)
{
    $poptions = Helper::options()->plugin('ShortCodes');
    $qrcode = $poptions->GzhImgUrl;

    global $typecho_archive;

    extract(shortcode_atts(array('key' => null, 'keyword' => null, 'gname'=> '即刻学术'), $atts));
    if (isset($_POST['secret_key']) && $_POST['secret_key'] == $key) {
        return '<div class="secret-password-content">' . $content . '<i title="私密内容" class="fa fa-lock secret-icon"></i></div>';
    } else {
        return
            '<div class="post_hide_box"><img class="gzh-erweima" data-url="' . $qrcode . '" src="' . $qrcode . '"  title="ijkxs"><div class="post-secret-info"><i class="fa fa-exclamation-circle"></i>此处内容已经被作者无情的隐藏，请输入验证码查看内容</div><form action="' . $typecho_archive->permalink . '" method="post"><span style="display:inline-block;">验证码：</span><input id="pwbox" type="password" size="20" name="secret_key"><a class="a2" href="javascript:;"><input type="submit" value="提交" name="Submit"></a></form><div class="post-secret-notice">请关注'.$gname.'官方微信公众号，回复关键字“<span>' . $keyword . '</span>”，获取验证码。</br>注：用手机微信扫描右侧二维码或微信搜索“'.$gname.'”即可关注哦！</div></div>';
    }
}

add_shortcode('wm_gzh', 'secrets_content');
//简码：[wm_gzh]只有用户才能看到的内容[/wm_gzh]

// 五、内容收缩
////////////////////////////////////////////////////////////
// Tabs选项
function wp_tab_group($atts, $content = null)
{
    $GLOBALS['wp_tab_count'] = 0;
    do_shortcode($content);
    if (is_array($GLOBALS['wp-tabs'])) {
        foreach ($GLOBALS['wp-tabs'] as $tab) {
            $tabs[] = '<li><a href="#' . $tab['id'] . '">' . $tab['title'] . '</a></li>';
            $panes[] = '<div id="' . $tab['id'] . '">' . $tab['content'] . '</div>';
        }
        $return = "\n" . '<div id="wp-tabwrap"><ul id="wp-tabs">' . implode("\n", $tabs) . '</ul>' . "\n" . '<div id="wp_tab_content">' . implode("\n", $panes) . '</div></div>' . "\n";
    }
    return $return;
}

add_shortcode('wm_tabgroup', 'wp_tab_group');

function wp_scd_tab($atts, $content = null)
{
    extract(shortcode_atts(array(
        'title' => 'wp-tab %d',
        'id' => ''
    ), $atts));
    $x = $GLOBALS['wp_tab_count'];
    $GLOBALS['wp-tabs'][$x] = array('title' => sprintf($title, $GLOBALS['wp_tab_count']), 'content' => $content, 'id' => $id);
    $GLOBALS['wp_tab_count']++;
}

add_shortcode('wm_tab', 'wp_scd_tab');

//2.开关菜单
function wp_toggle_box_shortcode($atts, $content = null)
{
    $toggle_box = "<ul class='wp-toggle-box' style='padding-left: 0'>";
    $toggle_box = $toggle_box . do_shortcode($content);
    $toggle_box = $toggle_box . "</ul>";
    return $toggle_box;
}

add_shortcode('wm_toggle_box', 'wp_toggle_box_shortcode');

function wp_toggle_item_shortcode($atts, $content = null)
{
    extract(shortcode_atts(array("title" => '', "active" => 'false'), $atts));
    $active = ($active == "true") ? " active" : '';
    $toggle_item = "<li>";
    $toggle_item = $toggle_item . "<h3 class='wp-toggle-box-head'>";
    $toggle_item = $toggle_item . "<i class='icon-toggle " . $active . "'></i><span class='" . $active . "'>";
    $toggle_item = $toggle_item . $title . "</span></h3>";
    $toggle_item = $toggle_item . "<div class='wp-toggle-box-content" . $active . "'>" . do_shortcode($content) . "</div>";
    $toggle_item = $toggle_item . "</li>";
    return $toggle_item;
}

add_shortcode('wm_toggle_item', 'wp_toggle_item_shortcode');
//简码：[wm_toggle_box][wm_toggle_item title="标题" active="true"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][wm_toggle_item title="标题"]内容[/wm_toggle_item][/wm_toggle_box]

//3. 阅读全文
function wpcollapse($atts, $content = null)
{
    extract(shortcode_atts(array(""), $atts));
    return '<div style="position:relative">
			    <div class="hidecontent" style="display:none">' . $content . '</div>
		            <a class="hidetitle" style="position: absolute">
                    <button class="collapseButton">阅读全文</button>
                </a>
	</div>';
}

add_shortcode('wm_collapse', 'wpcollapse');

//4. 卡片内链
function wp_embed_posts($atts, $content = null)
{

    extract(shortcode_atts(array(
        'ids' => ''
    ),
        $atts));

    global $post;
    $content = '';
    $postids = explode(',', $ids);
    $inset_posts = sc_get_posts(array('post__in' => $postids));

    foreach ($inset_posts as $key => $post) {

        $content .= '<div class="wp-embed-card"><a style="position: initial" target="_blank" href="' . sc_get_category_link($post->categories[0]["mid"]) . '"><span class="wp-embed-card-category">' . $post->categories[0]['name'] . '</span></a><span class="wp-embed-card-img"><a target="_blank" href="' . $post->permalink . '"><img alt="' . $post->title . '" src="' . sc_getRandomThumbnail_($post) . '"></a></span><span class="wp-embed-card-info"><a target="_blank" href="' . $post->permalink . '"><span class="wp-card-name">' . $post->title . '</span></a><span class="wp-card-abstract">' . tp_trim_words($post->excerpt, 100, '...') . '</span><span class="wp-card-controls"><span class="wp-group-data"> <i>时间:</i>' . sc_formatTime_($post->created) . '</span><span class="wp-group-data"> <i>阅读:</i>' . sc_getViews($post). '</span><a style="display: block" target="_blank" href="' . $post->permalink . '"><span class="wp-card-btn-deep">阅读全文</span></a></span></span></div>';
    }
    return $content;
}

add_shortcode('wm_embed_post', 'wp_embed_posts');

