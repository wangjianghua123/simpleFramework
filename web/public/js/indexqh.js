var $ = jQuery.noConflict();
$(function() {
	var sWidth = $("#focusIndex").width();
	var len = $("#focusIndex ul li").length;
	var index = 0;
	var picTimer;
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < len; i++) {
		btn += "<a>" + "&nbsp;" + "</a>";
	}
	btn += "</div>"
	$("#focusIndex").append(btn);
	$("#focusIndex .btnBg").css("opacity",0.4);
	$("#focusIndex .btn a").click(function() {
		index = $("#focusIndex .btn a").index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");
	$("#focusIndex ul").css("width",sWidth * (len+1));
	$("#focusIndex ul li div").hover(function() {
		$(this).siblings().css("opacity",0.7);
	},function() {
		$("#focusIndex ul li div").css("opacity",1);
	});
	$("#focusIndex").hover(function() {
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
	$("#focusIndex .preNext").css("opacity",0.7).hover(function() {
		$(this).stop(true,false).animate({"opacity":"1"},300);
	},function() {
		$(this).stop(true,false).animate({"opacity":"0.7"},300);
	});

	//上一页按钮
	$("#focusIndex .pre").click(function() {
		index -= 1;
		if(index == -1) {index = len - 1;}
		showPics(index);
	});

	//下一页按钮
	$("#focusIndex .next").click(function() {
		index += 1;
		if(index == len) {index = 0;}
		showPics(index);
	});

	function showPics(index) { 
		var nowLeft = -index*sWidth;
		$("#focusIndex ul").stop(true,false).animate({"left":nowLeft},500); 
		$("#focusIndex .btn a").removeClass("on").eq(index).addClass("on"); 
	}
	
	function showFirPic() { 
		$("#focusIndex ul").append($("#focusIndex ul li:first").clone());
		var nowLeft = -len*sWidth; 
		$("#focusIndex ul").stop(true,false).animate({"left":nowLeft},500,function() {
			$("#focusIndex ul").css("left","0");
			$("#focusIndex ul li:last").remove();
		}); 
		$("#focusIndex .btn a").removeClass("on").eq(0).addClass("on");
	}
});