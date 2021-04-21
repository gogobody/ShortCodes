jQuery(document).ready(
    function (jQuery) {
        jQuery('.collapseButton').click(
            function () {
                jQuery(this).parent().parent().find('.hidecontent').slideToggle('slow');
                $(this).get(0).parentNode.style.display = "none";
            }
        );
    }
);

jQuery(document).ready(function () {
        jQuery("ul.wp-toggle li,ul.wp-toggle-box li").each(function () {
                jQuery(this).children(".wp-toggle-content,.wp-toggle-box-content").not(".active").css("display", "none");
                $("ul.wp-toggle li:first .wp-toggle-head .icon-plus,ul.wp-toggle-box li:first .wp-toggle-box-head .icon-plus").addClass("active");
                $("ul.wp-toggle li:first .wp-toggle-content,ul.wp-toggle-box li:first .wp-toggle-box-content").show();
                jQuery(this).children(".wp-toggle-head,.wp-toggle-box-head").bind("click", function () {
                        jQuery(this).children().addClass(function () {
                            if (jQuery(this).hasClass("active")) {
                                jQuery(this).removeClass("active");
                                return ""
                            }
                            return "active"
                        });
                        jQuery(this).siblings(".wp-toggle-content,.wp-toggle-box-content").slideToggle()
                    }
                )
            }
        )
    }
);
$(function () {
        $("#wp-tabs li:first,#wp_tab_content > div:first").addClass("current");
        $("#wp-tabs li a").click(function (a) {
                $("#wp-tabs li, #wp_tab_content .current").removeClass("current").removeClass("fadeInLeft");
                $(this).parent().addClass("current");
                var b = $(this).attr("href");
                $(b).addClass("current fadeInLeft");
                a.preventDefault()
            }
        )
    }
);
$(".footer-popup .show").click(function () {
    $(".footer-popup").toggleClass("mobile-btn")
});