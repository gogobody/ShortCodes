<script>
    $(function () {
        if (!$("#wmd-editarea")) return;
        var node = `<section class="typecho-post-option"><label for="teepay_price" class="typecho-label">自定义简码</label>
    <p>
    <div class="shortcodes_control">
        <p>如果你想要使用短代码请选择短代码选项：</p>
        <div>
            <label>选择短代码<span></span></label>
            <select name="items" class="shortcode_sel" size="1"
                    onchange="document.getElementById('items_accumulated').value = this.options[selectedIndex].value;">
                <option class="parentscat" value="[TOC]
# 文章大标题
## 文章中标题
### 文章小标题
">
                    0.目录
                </option>
                <option class="parentscat" value="">
                    1.提示框
                </option>
                <option value="[wm_error]这里输入内容[/wm_error]">
                    红色错误框
                </option>
                <option value="[wm_warn]这里输入内容[/wm_warn]">
                    黄色警告框
                </option>
                <option value="[wm_tips]这里输入内容[/wm_tips]">
                    蓝色计划框
                </option>
                <option value="[wm_notice]这里输入内容[/wm_notice]">
                    绿色提醒框
                </option>

                <option class="parentscat" value="">
                    2.按钮
                </option>
                <option value="[wm_wpbutton link=&quot;#&quot; target=&quot;blank&quot; variation=&quot;red&quot;]这里输入内容[/wm_wpbutton]">
                    红色
                </option>
                <option value="[wm_wpbutton link=&quot;#&quot; target=&quot;blank&quot; variation=&quot;yellow&quot;]这里输入内容[/wm_wpbutton]">
                    黄色
                </option>
                <option value="[wm_wpbutton link=&quot;#&quot; target=&quot;blank&quot; variation=&quot;blue&quot;]这里输入内容[/wm_wpbutton]">
                    蓝色
                </option>
                <option value="[wm_wpbutton link=&quot;#&quot; target=&quot;blank&quot; variation=&quot;green&quot;]这里输入内容[/wm_wpbutton]">
                    绿色
                </option>

                <option class="parentscat" value="">
                    3.文本框
                </option>
                <option value="[wm_kuang title=&quot;标题&quot;]这里输入内容[/wm_kuang]">
                    虚线标题框
                </option>
                <option value="[wm_xuk]这里输入内容[/wm_xuk]">
                    虚线文本框
                </option>
                <option value="[wm_red]这里输入内容[/wm_red]">
                    红边文本框
                </option>
                <option value="[wm_yellow]这里输入内容[/wm_yellow]">
                    黄边文本框
                </option>
                <option value="[wm_blue]这里输入内容[/wm_blue]">
                    蓝边文本框
                </option>
                <option value="[wm_green]这里输入内容[/wm_green]">
                    绿边文本框
                </option>

                <option class="parentscat" value="">
                    4.内容隐藏
                </option>
                <option value="[wm_reply]评论后可见内容[/wm_reply]">
                    评论后可见内容
                </option>
                <option value="[wm_login]登录后可见内容[/wm_login]">
                    登录后可见内容
                </option>
                <option value="[wm_gzh keyword=&quot;关键字&quot; key=&quot;验证码&quot; gname=&quot;公众号名字&quot;]关注微信可见内容[/wm_gzh]">
                    关注微信可见内容
                </option>

                <option class="parentscat" value="">
                    5.内容收缩
                </option>
                <option value="[wm_tabgroup][wm_tab title=&quot;标题 1&quot; id=&quot;1&quot;]内容 1[/wm_tab][wm_tab title=&quot;标题 2&quot; id=&quot;2&quot;]内容 2[/wm_tab] [wm_tab title=&quot;标题 3&quot; id=&quot;3&quot;]内容 3[/wm_tab][/wm_tabgroup]">
                    TABS选项
                </option>
                <option value="[wm_toggle_box][wm_toggle_item title=&quot;标题 1&quot; active=&quot;true&quot;]内容 1[/wm_toggle_item][wm_toggle_item title=&quot;标题 2&quot;]内容 2[/wm_toggle_item][wm_toggle_item title=&quot;标题 3&quot;]内容 3[/wm_toggle_item][/wm_toggle_box]">
                    开关菜单
                </option>
                <option value="[wm_collapse title=&quot;阅读全文&quot;][/wm_collapse]">
                    阅读全文
                </option>
                <option value="[wm_embed_post ids=id1,id2][/wm_embed_post]">
                    卡片内链
                </option>
            </select>
            <label>
                短代码预览
                <br><span>注：复制短代码到编辑器(可视模式)中，修改成自己的内容即可。</span></label>
            <p>
                <textarea id="items_accumulated" rows="5" style="width: 100%"></textarea>
            </p>
        </div>
    </div>
    </p>
</section>`;
        $("#tab-advance").before(node)
    })
</script>
