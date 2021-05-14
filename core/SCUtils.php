<?php
$db = Typecho_Db::get();


/* 随机图片 */
function sc_getRandomThumbnail_($widget)
{
//    $random = 'https://cdn.jsdelivr.net/npm/typecho_joe_theme@4.3.5/assets/img/random/' . rand(1, 25) . '.webp';
//    $random = 'https://picsum.photos/536/354?random='. rand(1, 50);
    $random = 'https://cdn.jsdelivr.net/gh/gogobody/PicsumPlaceholder/img/536_354_webp/' . rand(0, 100) . '.webp';

    $pattern = '/\<img.*?src\=\"(.*?)\"[^>]*>/i';
    $patternMD = '/\!\[.*?\]\((http(s)?:\/\/.*?(jpg|jpeg|gif|png|webp))/i';
    $patternMDfoot = '/\[.*?\]:\s*(http(s)?:\/\/.*?(jpg|jpeg|gif|png|webp))/i';
    $img = $random;
    if ($widget->fields->thumb) {
        $img = $widget->fields->thumb;
        return $img;

    }

    $t = preg_match_all($pattern, $widget->content, $thumbUrl);
    if ($t) {
        $img = $thumbUrl[1][0];
    } elseif (preg_match_all($patternMD, $widget->content, $thumbUrl)) {
        $img = $thumbUrl[1][0];
    } elseif (preg_match_all($patternMDfoot, $widget->content, $thumbUrl)) {
        $img = $thumbUrl[1][0];
    } else{
        return $random;
    }
    if ($widget->cid) $widget->setField('thumb','str',$img,$widget->cid); // 将查询到的图片字段保存下来以备下一次使用
    return $img;
}


function sc_get_posts($arr)
{

    $all_posts = array();
    if (array_key_exists('post__in',$arr)){
        $pids = $arr['post__in'];
        foreach ($pids as $pid){
            Typecho_Widget::widget('Widget_Archive@_'.$pid, 'pageSize=1&type=post', 'cid='.$pid)->to($post);
            if ($post and $post->cid)
            array_push($all_posts,$post);
        }
    }
    return $all_posts;
}

function sc_get_category_link($mid){

    Typecho_Widget::widget('Widget_Archive@_m_'.$mid, 'pageSize=1&type=category', 'mid='.$mid)->to($cat);
    if ($cat and $cat->mid)
        return $cat->permalink;
    else
        return null;
}

function tp_trim_words($content,$length = 100, $trim = '...'){

    return Typecho_Common::subStr(strip_tags($content), 0, $length, $trim);
}


/**
 * 时间友好化
 *
 * @access public
 * @param mixed
 * @return
 */
function sc_formatTime_($time){

    $text = '';
    $time = intval($time);
    $ctime = time();
    $t = $ctime - $time; //时间差
    if ($t < 0) {
        return date('Y-m-d', $time);
    }
    $y = date('Y', $ctime) - date('Y', $time);//是否跨年
    switch ($t) {
        case $t == 0:
            $text = '刚刚';
            break;
        case $t < 60://一分钟内
            $text = $t . '秒前';
            break;
        case $t < 3600://一小时内
            $text = floor($t / 60) . '分钟前';
            break;
        case $t < 86400://一天内
            $text = floor($t / 3600) . '小时前'; // 一天内
            break;
        case $t < 2592000://30天内
            if($time > strtotime(date('Ymd',strtotime("-1 day")))) {
                $text = '昨天';
            } elseif($time > strtotime(date('Ymd',strtotime("-2 days")))) {
                $text = '前天';
            } else {
                $text = floor($t / 86400) . '天前';
            }
            break;
        case $t < 31536000 && $y == 0://一年内 不跨年
            $m = date('m', $ctime) - date('m', $time) -1;
            if($m == 0) {
                $text = floor($t / 86400) . '天前';
            } else {
                $text = $m . '个月前';
            }
            break;
        case $t < 31536000 && $y > 0://一年内 跨年
            $text = (11 - date('m', $time) + date('m', $ctime)) . '个月前';
            break;
        default:
            $text = (date('Y', $ctime) - date('Y', $time)) . '年前';
            break;
    }
    return $text;
}


/* 查询文章浏览量 */
function sc_getViews($item)
{
    $db = Typecho_Db::get();
    $result = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $item->cid))['views'];
    return number_format($result);
}