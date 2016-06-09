var $ = jQuery.noConflict();
$(function() {
        $(".select_box").live("click",function(event){   
                event.stopPropagation();
                $(this).find(".option").toggle();
                $(this).parent().siblings().find(".option").hide();
        });
        $(document).click(function(event){
                var eo=$(event.target);
                if($(".select_box").is(":visible") && eo.attr("class")!="option" && !eo.parent(".option").length)
                $('.option').hide();									  
        });
        /*赋值给文本框*/
        $(".option a").live("click",function(){
                var value=$(this).text();
                $(this).parent().siblings(".select_txt").text(value);

         });
        /*8 弹出*/
        $(".zg_p_gb").click(function(){
                $(this).parent().parent().hide();
                $(".popIframe").hide();
        });	
    
    
        //头部右侧登陆后
	$(".zgw_top_dlh").each(function(){
            $(this).click(function(){
                $(this).addClass("hover");
                $(this).children("p").show();
            });
	});
        
        //鼠标滑过显示菜单
        $(".zgw_top_dlh").live("mouseover",function(){
            $(this).addClass("hover");
            $(this).children("p").show();
        });
        $(".zgw_top_dlh").live("mouseout",function(){
            $(this).removeClass("hover");
            $(this).children("p").hide();
        });

	
	//首页右侧
	$(".zgw_mrigh_title a").each(function(c) {
            $(this).click(function(){
                    $(".zgw_mrigh_title a").removeClass("hover");
                    $(this).addClass("hover");
                    $(".zgw_mrigh_cont").hide();
                    $(".zgw_mrigh_cont:eq("+c+")").show();
            })
        });
	
	//首页banner
	$(".zgw_mrigh_ad_let_bot span").hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	})
	
	//点击隐藏
	$(".zgw_listtan h2 span").click(function(){
		$(this).parents(".zgw_listtan").hide();
		$(".popIframe").hide();
	})
	$(".quxiao span").click(function(){
		$(this).parents(".zgw_toujl .quxiao").hide();
		event.stopPropagation();	
	})
	$(".zgw_fudo .zgw_fudo_guanbi").click(function(){
		$(this).parents(".zgw_fudobox").hide();
	})
	
	//
	$(".zgw_toujl div.shouc").hover(function(){
		$(this).children("p").show();
	},function(){
		$(this).children("p").hide();
	})
	
	//
/*	$(".zgw_toujl div.shouc a.sc").click(function(){
		$(this).next("a.yshouc").show()
		$(this).hide()
		$(this).children("p.quxiao").hide();
		$(this).children("p.scang").show();
	}) */

    
        //弹框select
	var objWindowH=$(document).height();
	$(".oPubOpacity").height(objWindowH);
	$(".oPubCloseAlert ").click(function(){
		$(".oPubOpacity").hide();
		$(this).parents(".oPubAlert").hide();
	});
	$(".oOnlineResume").click(function(event){
		$(this).toggleClass("oActiveCsj");
	});
        
	/*赋值给文本框*/
	$(".o_select_box").click(function(event){ 
		event.stopPropagation();  	
		$(this).find(".o_option").toggle();
	});
	$(document).click(function(event){
                var eo=$(event.target);
                if($(".o_select_box").is(":visible") && eo.attr("class")!="o_option" && !eo.parent(".o_option").length)
                $('.o_option').hide();									  
        });
        $(".o_option a").click(function() {
            var value = $(this).text();
            $(this).parent().siblings(".o_select_txt").text(value);
            $('#listpreview').attr('href', $(this).attr('preview'));
            $('#listeditresume').attr('href', $(this).attr('editresume'));
            var val = $(this).attr('value')
            $('input[name=jobn]').val(val);
        });
         var topMain=$(".zgw_topbox").height()//是头部的高度加头部与nav导航之间的距离。
         var nav=$(".zgw_gotop");
         $(window).scroll(function(){
                if ($(window).scrollTop()>topMain){//如果滚动条顶部的距离大于topMain则就nav导航就添加类.nav_scroll，否则就移除。
                        nav.show();
                }
                else{
                        nav.hide();
                }
         });
         $(".zgw_gotop").click(function(){$("html,body").animate({scrollTop:"0px"},800);})
         
	var sWidth = $(".oFocusPic").width();
	var len = $(".oFocusPic ul li").length;
	var index = 0;
	var picTimer;
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < len; i++) {
		btn += "<a>" + "&nbsp;" + "</a>";
	}
	btn += "</div>"
	$(".oFocusPic").append(btn);
	$(".oFocusPic .btnBg").css("opacity",0.4);
	$(".oFocusPic .btn a").click(function() {
		index = $(".oFocusPic .btn a").index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");
	$(".oFocusPic ul").css("width",sWidth * (len+1));
	$(".oFocusPic ul li div").hover(function() {
		$(this).siblings().css("opacity",0.7);
	},function() {
		$(".oFocusPic ul li div").css("opacity",1);
	});
	$(".oFocusPic").hover(function() {
		clearInterval(picTimer);
	},function() {
		picTimer = setInterval(function() {
			if(index == len) {
				showFirPic();
				index = 0;
			} else { 
				showPics(index);
			}
			index++;
		},3000);
	}).trigger("mouseleave");
	//上一页、下一页按钮透明度处理
	$(".oFocusPic .preNext").css("opacity",0.7).hover(function() {
		$(this).stop(true,false).animate({"opacity":"1"},300);
	},function() {
		$(this).stop(true,false).animate({"opacity":"0.7"},300);
	});

	//上一页按钮
	$(".oFocusPic .pre").click(function() {
		index -= 1;
		if(index == -1) {index = len - 1;}
		showPics(index);
	});

	//下一页按钮
	$(".oFocusPic .next").click(function() {
		index += 1;
		if(index == len) {index = 0;}
		showPics(index);
	});

	function showPics(index) { 
		var nowLeft = -index*sWidth;
		$(".oFocusPic ul").stop(true,false).animate({"left":nowLeft},500); 
		$(".oFocusPic .btn a").removeClass("on").eq(index).addClass("on"); 
	}
	
	function showFirPic() { 
		$(".oFocusPic ul").append($(".oFocusPic ul li:first").clone());
		var nowLeft = -len*sWidth; 
		$(".oFocusPic ul").stop(true,false).animate({"left":nowLeft},500,function() {
			$(".oFocusPic ul").css("left","0");
			$(".oFocusPic ul li:last").remove();
		}); 
		$(".oFocusPic .btn a").removeClass("on").eq(0).addClass("on");
	}
        
        
        //职位检索
	$(".oDetailNobx a").click(function(){
		$(".oDetailNobx").hide();
		$(".oSearchDetail").slideDown();
		$("a.oCollection").removeClass("oScCollection");
	});
	$("span.oCollection").click(function(){
		$(this).toggleClass("oScCollection");
		$(".oSearchDetail").slideToggle();
		$(".oDetailNobx").slideToggle();
	});
	$(".aWorkAddress").hover(function(){
		$(".oWrokConAddress").show();
	});
	$(".oWrokConAddress").hover(function(){
		$(".oWrokConAddress").show();
	},function(){
		$(".oWrokConAddress").hide();
	});
	
	$(".aIndustry").hover(function(){
		$(".oConIndustry").show();
	});
	$(".oConIndustry").hover(function(){
		$(".oConIndustry").show();
	},function(){
		$(".oConIndustry").hide();
	});
        $(".zg_city_all span:eq(0)").click(function(){
            $(this).next().hide();
            $(this).removeClass("zgCity");
            $(".zg_city_box:eq(0)").show();
            $(".zg_city_box:eq(1)").hide();
        })
});


/*首页边导航*/	
function show_knav(obj)
{
	document.getElementById("knav"+obj).className="kenav";
	document.getElementById("mskc"+obj).style.display="block";
}
function close_knav(obj)
{
	document.getElementById("mskc"+obj).style.display="none";
	document.getElementById("knav"+obj).className="";
}
/*底部微信*/	
function show_wx(obj)
{
	document.getElementById("weixin"+obj).style.display="block";
}
function close_wx(obj)
{
	document.getElementById("weixin"+obj).style.display="none";
}


//页面滑动到底部时，底部的登录banner向上移，露出“微博 微信 帮助中心 联系我们”
$(window).scroll(function(){
　　var scrollTop = $(this).scrollTop()+60;
　　var scrollHeight = $(document).height();
　　var windowHeight = $(this).height();
　　if(scrollTop + windowHeight >= scrollHeight){
        $(".zgw_fudobox").css('bottom', 60);
　　}else{
        $(".zgw_fudobox").css('bottom', 0);
　　}

});

//在线简历填写右侧浮动xyj
$(window).scroll(function(){
    var heig = $("#zgPhxg").height()+100;
	var hef=$(document).scrollTop();
	if(hef>heig){
		$(".zg_phxg").addClass("zg_fxd");
		}else{
		$(".zg_phxg").removeClass("zg_fxd");	
			}
	})

$(function(){
	$('.zg_t_l_1').click(function(){$('html,body').animate({scrollTop:$('#basic').offset().top}, 800);$(this).addClass("zg_t_l_1_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_2').click(function(){$('html,body').animate({scrollTop:$('#exp').offset().top}, 800);$(this).addClass("zg_t_l_2_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_3').click(function(){$('html,body').animate({scrollTop:$('#edu').offset().top}, 800);$(this).addClass("zg_t_l_3_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_4').click(function(){$('html,body').animate({scrollTop:$('#win').offset().top}, 800);$(this).addClass("zg_t_l_4_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_5').click(function(){$('html,body').animate({scrollTop:$('#active').offset().top}, 800);$(this).addClass("zg_t_l_5_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_8').click(function(){$('html,body').animate({scrollTop:$('#work').offset().top}, 800);$(this).addClass("zg_t_l_8_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_9').click(function(){$('html,body').animate({scrollTop:$('#interest').offset().top}, 800);$(this).addClass("zg_t_l_9_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_6').click(function(){$('html,body').animate({scrollTop:$('#skill').offset().top}, 800);$(this).addClass("zg_t_l_6_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_7').click(function(){$('html,body').animate({scrollTop:$('#eval').offset().top}, 800);$(this).addClass("zg_t_l_7_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_0').removeClass("zg_t_l_0_c");});
	$('.zg_t_l_0').click(function(){$('html,body').animate({scrollTop:$('.zg_zpzs').offset().top}, 800);$(this).addClass("zg_t_l_0_c");$('.zg_t_l_2').removeClass("zg_t_l_2_c");$('.zg_t_l_3').removeClass("zg_t_l_3_c");$('.zg_t_l_4').removeClass("zg_t_l_4_c");$('.zg_t_l_5').removeClass("zg_t_l_5_c");$('.zg_t_l_6').removeClass("zg_t_l_6_c");$('.zg_t_l_1').removeClass("zg_t_l_1_c");$('.zg_t_l_8').removeClass("zg_t_l_8_c");$('.zg_t_l_9').removeClass("zg_t_l_9_c");$('.zg_t_l_7').removeClass("zg_t_l_7_c");});
})

var $ = jQuery.noConflict();
$(function() {
	var sWidth = $("#focus").width();
	var len = $("#focus ul li").length;
	var index = 0;
	var picTimer;
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < len; i++) {
		btn += "<a>" + (i+1) + "</a>";
	}
	btn += "</div>"
	$("#focus").append(btn);
	$("#focus .btnBg").css("opacity",0.5);
	$("#focus .btn a").click(function() {
		index = $("#focus .btn a").index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");
	
	$("#focus .pre").click(function () {
        index -= 1;
        if (index == -1) { index = len - 1; }
        showPics(index);
    });
    $("#focus .next").click(function () {
        index += 1;
        if (index == len) { index = 0; }
        showPics(index);
    });
	
	$("#focus ul").css("width",sWidth * (len + 1));
	$("#focus ul li div").hover(function() {
		$(this).siblings().css("opacity",0.7);
	},function() {
		$("#focus ul li div").css("opacity",1);
	});
	$("#focus").hover(function() {
		clearInterval(picTimer);
	},function() {
		picTimer = setInterval(function() {
			if(index == len) {
				showFirPic();
				index = 0;
			} else { 
				showPics(index);
			}
			index++;
		},3000);
	}).trigger("mouseleave");
	function showPics(index) { 
		var nowLeft = -index*sWidth;
		$("#focus ul").stop(true,false).animate({"left":nowLeft},500); 
		$("#focus .btn a").removeClass("on").eq(index).addClass("on"); 
	}
	
	function showFirPic() { 
		$("#focus ul").append($("#focus ul li:first").clone());
		var nowLeft = -len*sWidth; 
		$("#focus ul").stop(true,false).animate({"left":nowLeft},500,function() {
			$("#focus ul").css("left","0");
			$("#focus ul li:last").remove();
		}); 
		$("#focus .btn a").removeClass("on").eq(0).addClass("on");
	}
});

