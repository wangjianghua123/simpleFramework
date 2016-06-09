(function(){
	$(function(){	
		//模板id（修改时用）
		var upTemId = -1;
		//判断是否离开页面或者当前可编辑框未保存
		var isLeave=true;
		//判断页面内添加窗口是否被打开
		var addFlag = false;
		//存储当前修改的简历模板标识
		var tmpIdt = -1;
		var flag = -1;
		var tmpIds = -1;
		
		beforeLeave();
		function beforeLeave(){
                    window.onbeforeunload = function(){
                        if(isLeave){
                            return;
                        }else{
                            return "内容还未保存，确认离开该页面吗？ ";	
                        }
                     }				
		}
		
		//设为默认模板
		$(".set_default").bind("click",function(){
			var id = $(this).data("temid");
			if($("#noticeType").val() == 0){
                            var href = "/templates/setDefaultInterviewTemplate.json";
                            var obj = {id:id};					
                            callAjax(href,obj);				
			}else{
                            var href = "/templates/setDefaultRefuseTemplate.json";
                            var obj = {id:id};					
                            callAjax(href,obj);					
			}
		});
		
		$('.del_label .checkbox input').siblings('i').fadeOut();
		//删除弹窗-模拟checkbox
		$('.del_label .checkbox input').bind('click',function(){
			if($(this).attr('checked')){
				$(this).removeAttr('checked');
				$(this).siblings('i').fadeOut(200);
			}else{
				$(this).attr('checked','checked');
				$(this).siblings('i').fadeIn(200);
			}
			return false;
		});
		//删除弹窗- 我知道了 按钮
		$(".del_know").bind("click",function(){
			$("#cboxClose").click();
		});
		//删除弹窗- 确定 按钮
		$(".del_sure").bind("click",function(){
			if($("#noticeType").val() == 0){
				if($('.del_label .checkbox input').attr('checked')){
					closenlraf(true,"interviewCookie");
				}
				var href = "/templates/deleteInterviewTemplate.json";
				var obj = {id:upTemId};
				callAjax(href,obj);
			}else{
				if($('.del_label .checkbox input').attr('checked')){
					closenlraf(true,"unqualifyCookie");
				}
				var href = "/templates/deleteRefuseTemplate.json";
				var obj = {id:upTemId};					
				callAjax(href,obj);
			}
			$("#cboxClose").click();
		});
		
		//防止未保存--弹窗-确定按钮
		$("#confirm_leave").bind("click",function(){
			$("#cboxClose").click();
			
			if(addFlag){
				$("#addTmp").removeClass("grey");
			}
			var formId = $(".notice_dd form").eq(0).attr("id");
			if(formId == "addForm"){
				if(tmpIds != -1){
					$(".template_list").each(function( index ) {
						if($(this).data("temid") == flag){
							$("#addForm").remove();
							var othis = $(this).find(".updateTmp");
							initUpForm(othis);
							addFlag = false;
							isLeave = false;
						}	
					});
				}else{
					
				}
			}else{
				if(tmpIds != -1){
					$(".template_list").each(function( index ) {
						if($(this).data("temid") == tmpIds){
							$(this).next().remove();
							$(this).show();
							isLeave = false;						
						}
					});
					$(".template_list").each(function( index ) {
						if($(this).data("temid") == flag){
							var othis = $(this).find(".updateTmp");
							initUpForm(othis);
							addFlag = false;
							isLeave = false;
						}
					});						
				}else{
					$(".template_list").each(function( index ) {
						if($(this).data("temid") == flag){
							$(this).next().remove();
							$(this).show();
							console.log(tmpIds+"--tmpIds");
							var othis = $(this).find(".updateTmp");
                                                        console.log(1111111111111111);
							initAdd();
							isLeave = false;						
						}						
					});
				}			
			}
		});
		//初始化修改页面逻辑
		function initUpForm(othis){
			isLeave = false;
			var _this =$(othis);
			var tmpName = $(othis).parent().parent().find("strong").text();
			if($("#noticeType").val() == 0){
				var address = $(othis).parent().parent().parent().next().find("span").eq(0).text();
				var linkman = $(othis).parent().parent().parent().next().find("span").eq(1).text();
				var mobile = $(othis).parent().parent().parent().next().find("span").eq(2).text();
				var tip = $(othis).parent().parent().parent().next().find("span").eq(3).html();
			}else{
				var tip = $(othis).parent().parent().parent().next().find("span").eq(0).html();
			}
			//赋值
			$("#add_template #cancel").text("取消修改");
			//隐藏列表信息 追加修改框
			$(othis).parent().parent().parent().parent().hide().after($("#add_template").html());
			//对修改框id赋值，用来区分添加与修改
			$(othis).parent().parent().parent().parent().next().attr("id","updateForm");
			//校验
			validUpForm();
			$("#tmpname").val(tmpName);
			//面试通知模板
			if($("#noticeType").val() == 0){
				$("#site").val(address);
				$("#linkman").val(linkman);
				$("#mobile").val(mobile);
			}
			$("#tmpTip").html(tip);	
			//取消修改
			$("#updateForm #cancel").bind("click",function(){
				//判断是否离开页面或者当前可编辑框未保存
				isLeave = true;
				//恢复添加按钮
				$("#addTmp").removeClass("grey");
				//删除当前修改框
				$(this).parent().parent().parent().parent().remove();
				//显示修改前的该条列表信息
				$(_this).parent().parent().parent().parent().show();
			});				
		}
		//防止未保存--弹窗-取消按钮
		$("#cancel_leave").bind("click",function(){
			$("#cboxClose").click();
			addFlag = false;
		});
		//创建cookie
		function closenlraf(a,name){
			if(a){
				$.cookie(name,"true",{path:"/",expires:30,domain:"172.16.1.10:8098"});
			}else{
				$.cookie(name,"true",{path:"/",domain:"172.16.1.10:8098"});
			}
		}
		//删除按钮绑定colorbox
		$(".unqualified .delTmp").bind("click",function(){
			upTemId = $(this).data("temid");
			var tmpType = $(this).parent().parent().find(".set_default");
			if(tmpType.text() != "" && tmpType.text() !=null){
				if( $.cookie("unqualifyCookie")==null){
					var href = "/templates/deleteRefuseTemplate.json";
					colorbox({href:href,id:upTemId,content:"模板删除后将无法恢复，你确认要删除吗？",title:"模板提示"});
				}else{
					closenlraf(true,"unqualifyCookie");
					var href = "/templates/deleteRefuseTemplate.json";					
					var obj = {id:upTemId};
					callAjax(href,obj);
				}				
			}else{
                                var href = "/templates/deleteRefuseTemplate.json";
				colorbox({href:href,id:upTemId, content:"该模板为默认模板，要先设定其他默认模板后才可以删除哦",title:"模板提示"});
			}
		});
		//面试通知删除按钮绑定colorbox
		$(".interview .delTmp").bind("click",function(){
			upTemId = $(this).data("temid");
			var tmpType = $(this).parent().parent().find(".set_default");
			if(tmpType.text() != "" && tmpType.text() !=null){
				if( $.cookie("interviewCookie")==null){
					var href = "/templates/deleteRefuseTemplate.json";
                                        colorbox({href:href,id:upTemId,content:"模板删除后将无法恢复，你确认要删除吗？",title:"模板提示"});
				}else{
					closenlraf(true,"interviewCookie");
					var href = "/templates/deleteInterviewTemplate.json";
					var obj = {id:upTemId};					
					callAjax(href,obj);
				}
			}else{
                                var href = "/templates/deleteRefuseTemplate.json";
				colorbox({href:href,id:upTemId, content:"该模板为默认模板，要先设定其他默认模板后才可以删除哦",title:"模板提示"});
			}
		});
		//修改模板事件
		$(".updateTmp").bind("click",function(){
			//获取模板id
			upTemId = $(this).data("temid");
			//确定当前修改模板唯一标识
			if(tmpIdt != -1){
				tmpIds = tmpIdt;
			}else{
				tmpIds = $(this).parent().parent().parent().parent().data("temid");
			}
			flag = tmpIdt = $(this).parent().parent().parent().parent().data("temid");
			
			//防止未保存
			if(!isLeave){
                                var href = "/templates/deleteRefuseTemplate.json";
				colorbox({href:href,id:upTemId, content:"您当前编辑的内容尚未保存，确定不保存吗？",title:"提示信息"});
				return false;
			}
			isLeave = false;
			
			var _this =$(this);
			var tmpName = $(this).parent().parent().find("strong").text();
			if($("#noticeType").val() == 0){
				var address = $(this).parent().parent().parent().next().find("span").eq(0).text();
				var linkman = $(this).parent().parent().parent().next().find("span").eq(1).text();
				var mobile = $(this).parent().parent().parent().next().find("span").eq(2).text();
				var tip = $(this).parent().parent().parent().next().find("span").eq(3).text();
			}else{
				var tip = $(this).parent().parent().parent().next().find("span").eq(0).text();
			}
			//赋值
			$("#add_template #cancel").text("取消修改");
			$(this).parent().parent().parent().parent().hide().after($("#add_template").html());
			$(this).parent().parent().parent().parent().next().attr("id","updateForm");
			validUpForm();
			placeholderFn();
			$("#tmpname").val(tmpName);
			if($("#noticeType").val() == 0){
				$("#site").val(address);
				$("#linkman").val(linkman);
				$("#mobile").val(mobile);
			}
			$("#tmpTip").val(tip);	
			//取消修改
			$("#updateForm .cancel").bind("click",function(){
				isLeave = true;
				addFlag = false;
				$("#addTmp").removeClass("grey");
				$(this).parent().parent().parent().parent().remove();
				$(_this).parent().parent().parent().parent().show();
			});				
		});
		
		//添加模板事件
		$("#addTmp").bind("click",function(e){
			tmpIdt = -1;
			tmpIds = -1;
			e.stopPropagation();  
			var formId = $(".notice_dd form").eq(0).attr("id");
			if(formId == "addForm"){
				return false;
			}
			addFlag = true;		
			if(!isLeave){
                            var href = "/templates/deleteRefuseTemplate.json";	
                            colorbox({href:href,id:upTemId, content:"您当前编辑的内容尚未保存，确定不保存吗？",title:"提示信息"});
                            return false;
			}else{
                            //添加一次后，添加按钮置灰
                            $("#addTmp").addClass("grey");
			}
			
			//初始化添加页面
			$("#add_template #cancel").text("取消添加");
			$(".default").parent().parent().parent().parent().after($("#add_template").html());
			$(".default").parent().parent().parent().parent().next().attr("id","addForm");
			//点击添加模板，浏览器滚动条将添加弹窗置顶
			$("html, body").animate({scrollTop: $("#addForm").offset().top});
			placeholderFn();
			validForm();
			isLeave = false;
			//取消添加
			$("#addForm #cancel").bind("click",function(){
				isLeave = true;
				addFlag = false;
				$("#addTmp").removeClass("grey");
				$(this).parent().parent().parent().parent().remove();
			});					
			
		});
		//初始化添加方法
		function initAdd(){
			tmpIdt = -1;
			tmpIds = -1;
			addFlag = true;		
			$("#addTmp").addClass("grey");
			isLeave = false;
			//初始化添加页面
			$("#add_template #cancel").text("取消添加");
			$(".default").parent().parent().parent().parent().after($("#add_template").html());
			$(".default").parent().parent().parent().parent().next().attr("id","addForm");
			validForm();
			placeholderFn();
			//取消添加
			$("#addForm #cancel").bind("click",function(){
                            isLeave = true;
                            addFlag = false;
                            $("#addTmp").removeClass("grey");
                            $(this).parent().parent().parent().parent().remove();
			});
			return false;
		}
		//鼠标滑过模板列表显示设为默认模板按钮
		$(".template_list").hover(function(){
			$(this).find(".set_default").show();
		},function(){
			$(this).find(".set_default").hide();
		});
		//校验
		function validForm(){
			//校验
			$('#addForm').validate({
				onkeyup:true, 
		        rules: {
		        	tmpname : {
		    			required : true,
		    			maxlenStr:15
		    		},
		    		site:{
		    			required:false,
		    			maxlength:200
		    		},
		    		linkman:{
		    			required:false,
		    			rangeLenMin: 1,
		    			rangeLenMax: 8
		    		},
		    		mobile:{
		    			required:false,
		    			isTelNoRequire:true,
		    			maxlength:30 
		    		},
		    		tmpTip:{
		    			required:false,
		    			maxlength:500
		    		},
		    		tmpTipRefuse:{
		    			required:false,
		    			maxlength:200
		    		}
		    	},
		    	messages: {
		    		tmpname: {
		    	    	required: "请填写模板名",
		    	    	maxlenStr:"请输入30字以内的字符"
		    	   	},
		    	   	site:{
		    	   		maxlength:"请输入200字符以内的面试地点"
		    	   	},
		    	   	linkman:{
		    	   		rangeLenMin:"请输入2-16个字符的联系人",
			    		rangeLenMax:"请输入2-16个字符的联系人"
		    	   	},
		    	   	mobile:{
		    	   		maxlength:"请输入30字以内的联系电话"
		    	   	},
		    	   	tmpTip:{
		    	   		maxlength:"请输入500字符以内的补充内容"
		    	   	},
		    		tmpTipRefuse:{
		    			maxlength:"请输入200字符以内的补充内容"
		    		}
		    	},
		    	errorPlacement:function(label, element){
		    		label.appendTo($(element).parent());
		    		$(".c_section span.error").css("margin","5px 0 10px 88px");
		    	},
		    	submitHandler:function(form){
		    		//通知面试模板
		    		if($("#noticeType").val() == 0){
		    			var tmpname = $(".notice_main #tmpname").val();
		    			var site = $(".notice_main #site").val();
		    			var linkman = $(".notice_main #linkman").val();
		    			var mobile = $(".notice_main #mobile").val();
		    			var tmpTip = $(".notice_main #tmpTip").val();
		    			 //ajax调用
                                        var href = "/templates/addInterviewTemplate.json";
                                        var obj = {alis:tmpname,content:tmpTip,interviewAddress:site,linkMan:linkman,linkPhone:mobile};					
                                        callAjax(href,obj);		    			 
    			
		    		}else{
		    		        var tmpname = $(".notice_main #tmpname").val();
		    			var tmpTip = $(".notice_main #tmpTip").val();
		    			//ajax调用
                                        var href = "/templates/addRefuseTemplate.json";
                                        var obj = {alis:tmpname,content:tmpTip};					
                                        callAjax(href,obj);		    			 
   			
		    		}
		    		isLeave = true;
		    		
		    	}
			});				
		}
		//修改校验
		function validUpForm(){
			//校验
			$('#updateForm').validate({
			    onkeyup:true, 
		        rules: {
		        	tmpname : {
		    			required : true,
		    			maxlenStr:15
		    		},
		    		site:{
		    			required:false,
		    			maxlength:200
		    		},
		    		linkman:{
		    			required:false,
		    			rangeLenMin: 1,
		    			rangeLenMax: 8
		    		},
		    		mobile:{
		    			required:false,
		    			isTelNoRequire:true,
		    			maxlength:30 
		    		},
		    		tmpTip:{
		    			required:false,
		    			maxlength:500
		    		},
		    		tmpTipRefuse:{
		    			required:false,
		    			maxlength:200
		    		}
		    	},
		    	messages: {
		    		tmpname: {
		    	    	required: "请填写模板名",
		    	    	maxlenStr:"请输入30字以内的字符"
		    	   	},
		    	   	site:{
		    	   		maxlength:"请输入200字符以内的面试地点"
		    	   	},
		    	   	linkman:{
		    	   		rangeLenMin:"请输入2-16个字符的联系人",
			    		rangeLenMax:"请输入2-16个字符的联系人"
		    	   	},
		    	   	mobile:{
		    	   		maxlength:"请输入30字以内的联系电话"
		    	   	},
		    	   	tmpTip:{
		    	   		maxlength:"请输入500字符以内的补充内容"
		    	   	},
		    		tmpTipRefuse:{
		    			maxlength:"请输入200字符以内的补充内容"
		    		}
		    	},
		    	errorPlacement:function(label, element){
		    		label.appendTo($(element).parent());
		    		$(".c_section span.error").css("margin","5px 0 10px 88px");
		    	},
		    	submitHandler:function(form){
		    		if($("#noticeType").val() == 0){
		    			 var upId = upTemId;
		    			 //如果$(".notice_main #tmpname")查找不行或者较慢，可以改为 #editForm #tmpname示例
		    			 var tmpname = $(".notice_main #tmpname").val();
		    			 var site = $(".notice_main #site").val();
		    			 var linkman = $(".notice_main #linkman").val();
		    			 var mobile = $(".notice_main #mobile").val();
		    			 var tmpTip = $(".notice_main #tmpTip").val();
			    		 //ajax调用
                                         var href = "/templates/editInterviewTemplate.json";
                                         var obj = {id:upId,alis:tmpname,content:tmpTip,interviewAddress:site,linkMan:linkman,linkPhone:mobile};					
                                         callAjax(href,obj);		    			 
		    		}else{
		    			 var upId = upTemId;
		    			 //如果$(".notice_main #tmpname")查找不行或者较慢，可以改为 #editForm #tmpname示例
		    			 var tmpname = $(".notice_main #tmpname").val();
		    			 var tmpTip = $(".notice_main #tmpTip").val();

			    		 //ajax调用
                                         var href = "/templates/editRefuseTemplate.json";
                                         var obj = {id:upId,alis:tmpname,content:tmpTip};					
                                         callAjax(href,obj);			    			
		    		}	    		
		    		isLeave = true;
					addFlag = false;
					$("#addTmp").removeClass("grey");
		    	}
			});					
		}
	});
	//ajax公共调用方法
	function callAjax(href,obj){
                 $.ajax({
                    type : 'POST',
                    cache : false,
                    dataType : 'json',
                    url  : SLPGER.root  + 'href',
                    data : obj, 
                    success:function(result){
                        if(result.status == 1){
                            window.location.reload();
                        }else{
                            $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                        }
                    }
                });
	}
        
        //公共弹窗效果
        function colorbox(object){
            $.dialog({
                    title:object.title,
                    content:object.content,
                    padding:'10px 20px',
                    lock:true,
                    ok:function(){
                        var obj = {id:object.id};
                        callAjax(object.href,obj);  
                    },
                    cancel:function(){}
		});
        }
        
        function placeholderSupport() {
                return "placeholder" in document.createElement("input")
        }
        
        function placeholderFn() {
                placeholderSupport() || $("[placeholder]").focus(function() {
                        var a = $(this);
                        a.val() == a.attr("placeholder") && (a.val(""), a.removeClass("placeholder"))
                }).blur(function() {
                        var a = $(this);
                        ("" == a.val() || a.val() == a.attr("placeholder")) && (a.addClass("placeholder"), a.val(a.attr("placeholder")))
                }).blur(), "" === $("[placeholder]").value && ($("[placeholder]").value = $("[placeholder]").attr("placeholder"))
        }
        
})();