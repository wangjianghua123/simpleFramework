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
                            var href = "/templates/setdefaultinterview";
                            var obj = {id:id};					
                            callAjax(href,obj);				
			}else{
                            var href = "/templates/setdefaultrefuse";
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
		
	
		//初始化修改页面逻辑
		function initUpForm(othis){
			isLeave = false;
			var _this =$(othis);
                        //获取模板id
			upTemId = $(othis).parent().parent().parent().data('temid');
			var tmpName = $(othis).parent().parent().find("p").text();
			if($("#noticeType").val() == 0){
				var address = $(othis).parent().parent().parent().find(".zgm_mb_bd span").eq(0).text();
				var linkman = $(othis).parent().parent().parent().find(".zgm_mb_bd span").eq(1).text();
				var mobile = $(othis).parent().parent().parent().find(".zgm_mb_bd span").eq(2).text();
				var tip = $(othis).parent().parent().parent().find(".zgm_mb_bd span").eq(3).text();
			}else{
				var tip = $(othis).parent().parent().parent().find(".zgm_mb_bd span").eq(0).text();
			}
			//赋值
			$("#add_template #cancel").text("取消修改");
			//隐藏列表信息 追加修改框
			$(othis).parent().parent().parent().hide().after($("#add_template").html());
			//对修改框id赋值，用来区分添加与修改
			$(othis).parent().parent().parent().next().attr("id","updateForm");
                        $(othis).parent().parent().parent().next().find(".zgm_mb_li").attr("id","updateForm_"+upTemId); 
			//校验
			validUpForm();
                        placeholderFn();
                        $("#tmpid").val(upTemId); //赋值修改模板id
			$("#tmpname").val(tmpName);
			//面试通知模板
			if($("#noticeType").val() == 0){
				$("#site").val(address);
				$("#linkman").val(linkman);
				$("#mobile").val(mobile);
			}
			$("#tmpTip").val(tip);	
			//取消修改
			$("#updateForm #cancel").bind("click",function(){
				//判断是否离开页面或者当前可编辑框未保存
				isLeave = true;
				//恢复添加按钮
				$("#addTmp").removeClass("grey");
				//删除当前修改框
				$(this).parent().parent().parent().parent().parent().parent().remove();
				//显示修改前的该条列表信息
				$(_this).parent().parent().parent().show();
			});				
		}
                
		//创建cookie
		function closenlraf(a,name){
			if(a){
				$.cookie(name,"true",{path:"/",expires:130,domain:"www.kjiuye.com"});
			}else{
				$.cookie(name,"true",{path:"/",domain:"www.kjiuye.com"});
			}
		}
		
		//点击修改按钮触发修改模板事件
		$(".updateTmp").bind("click",function(){
			//获取模板id
			upTemId = $(this).data("temid");
			//确定当前修改模板唯一标识
			if(tmpIdt != -1){
				tmpIds = tmpIdt;
			}else{
				tmpIds = $(this).parent().parent().parent().data("temid");
			}
			flag = tmpIdt = $(this).parent().parent().parent().data("temid");
			//防止未保存
			if(!isLeave){
                                //当href为false的时候就在弹窗中把isLeave改为true
			        colorbox({href:false,tmpIds:tmpIds,flag:flag,content:"您当前编辑的内容尚未保存，确定不保存吗？",title:"提示信息"});
                                //当colorbox弹框直接返回后 直接就开始走下面的内容
                                return false;
			}
                        
                        //此处为false 防止直接打开多个修改界面
                        isLeave = false;
			
			var _this =$(this);
			var tmpName = $(this).parent().parent().find("p").text();
			if($("#noticeType").val() == 0){
                                //把$(this).parent().parent().next().find 改成 $(this).parent().parent().parent().find 前一个找到下一个模块的数据
				var address = $(this).parent().parent().parent().find(".zgm_mb_bd span").eq(0).text();
				var linkman = $(this).parent().parent().parent().find(".zgm_mb_bd span").eq(1).text();
				var mobile = $(this).parent().parent().parent().find(".zgm_mb_bd span").eq(2).text();
				var tip = $(this).parent().parent().parent().find(".zgm_mb_bd span").eq(3).text();
			}else{
				var tip = $(this).parent().parent().parent().find(".zgm_mb_bd span").eq(0).text();
			}
			//赋值
			$("#add_template #cancel").text("取消修改");
			$(this).parent().parent().parent().hide().after($("#add_template").html());
			$(this).parent().parent().parent().next().attr("id","updateForm");
                        $(this).parent().parent().parent().next().find(".zgm_mb_li").attr("id","updateForm_"+upTemId);
			validUpForm();
			placeholderFn();
                        $("#tmpid").val(upTemId); //赋值修改模板id
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
                            $(this).parent().parent().parent().parent().parent().parent().remove();
                            $(_this).parent().parent().parent().parent().parent().find('.zgm_mb_li').show();
			});				
		});
		
		//点击添加按钮触发添加模板事件
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
                            colorbox({href:false,tmpIds:tmpIds,flag:flag,formId:"addForm", content:"您当前修改或添加的内容尚未保存，请先保存所添加或修改的内容",title:"提示信息"});
                            //当colorbox弹框直接返回后 直接就开始走下面的内容
                            return false;
			}else{
                            //添加一次后，添加按钮置灰
                            $("#addTmp").addClass("grey");
			}
			//初始化添加页面
			$("#add_template #cancel").text("取消添加");
			$(".default").parent().parent().after($("#add_template").html());
			$(".default").parent().parent().next().attr("id","addForm");
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
				$(this).parent().parent().parent().parent().parent().remove();
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
			$(".default").parent().parent().after($("#add_template").html());
			$(".default").parent().parent().next().attr("id","addForm");
                        //将打开的隐藏
                        var uptmpid = $("#updateForm").find("#tmpid").val();
                        if(uptmpid != -1){
                            $("#updateForm_"+uptmpid).remove();
                            $("#tr_"+uptmpid).show();
                        }
			validForm();
			placeholderFn();
			//取消添加
			$("#addForm #cancel").bind("click",function(){
                            isLeave = true;
                            addFlag = false;
                            $("#addTmp").removeClass("grey");
                            $(this).parent().parent().parent().parent().parent().parent().remove();
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
                    var demo = $("#addForm").Validform({
                        btnSubmit:".save", 
                        showAllError:true,
                        ajaxPost:true,
                        tiptype:function(msg,o,cssctl){
                            if(o.type==2){
                                $('#J_'+o.obj.context.name).attr('class','login_tip3').html('');
                            }else if(o.type==3){
                                 $('#J_'+o.obj.context.name).html(msg);
                            } 
                        }
                    });
                    if ($("#noticeType").val() == 0) {
                        demo.addRule([{
                                ele:'input[name="tmpname"]',
                                datatype:"*1-20",
                                nullmsg:"<em></em>请输入模板名称！",
                        },
                        {
                                ele:'input[name="site"]',
                                datatype:"*1-60",
                                nullmsg:"<em></em>请输入面试地点！",
                        },
                        {
                                ele:'input[name="linkman"]',
                                datatype:"*2-8",
                                nullmsg:"<em></em>请再次输入联系人！",
                        },
                        {
                                ele:'input[name="mobile"]',
                                datatype:"m|/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/",
                                nullmsg:"<em></em>请输入联系电话！",
                        }]);
                        demo.config({
                            showAllError:true,
                            url:"/templates/insertinterview",
                            ajaxpost:{
                                success:function(result){
                                    if(result.status == 1){
                                        isLeave = true;
                                        window.location.reload();
                                    }else{
                                        $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                                    }
                                }
                            }
                        })
                    }else{//不合适通知模板
                        demo.addRule([{
                                ele:'input[name="tmpname"]',
                                datatype:"*1-20",
                                nullmsg:"<em></em>请输入模板名称！",
                        },
                        {
                                ele:'textarea[name="tmpTip"]',
                                datatype:"*1-500",
                                nullmsg:"<em></em>请输入联系电话！",
                        }]);
                        demo.config({
                            showAllError:true,
                            url:"/templates/addrefuse",
                            ajaxpost:{
                                success:function(result){
                                    if(result.status == 1){
                                        isLeave = true;
                                        window.location.reload();
                                    }else{
                                        $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                                    }
                                }
                            }
                        })
                    }   //endif
		}
		//修改校验
		function validUpForm(){ 
                    //校验
                     var demo = $("#updateForm").Validform({
                        btnSubmit:".save", 
                        showAllError:true,
                        ajaxPost:true,
                        tiptype:function(msg,o,cssctl){
                            if(o.type==2){
                                $('#J_'+o.obj.context.name).attr('class','login_tip3').html('');
                            }else if(o.type==3){
                                 $('#J_'+o.obj.context.name).html(msg);
                            } 
                        }
                    });
                    if ($("#noticeType").val() == 0) {
                        demo.addRule([{
                                ele:'input[name="tmpname"]',
                                datatype:"*1-20",
                                nullmsg:"<em></em>请输入模板名称！",
                        },
                        {
                                ele:'input[name="site"]',
                                datatype:"*1-60",
                                nullmsg:"<em></em>请输入面试地点！",
                        },
                        {
                                ele:'input[name="linkman"]',
                                datatype:"*2-8",
                                nullmsg:"<em></em>请再次输入联系人！",
                        },
                        {
                                ele:'input[name="mobile"]',
                                datatype:"m|/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/",
                                nullmsg:"<em></em>请输入联系电话！",
                        }]);
                        demo.config({
                            showAllError:true,
                            url:"/templates/editinterview",
                            data:{id:upTemId},
                            ajaxpost:{
                                success:function(result){
                                    if(result.status == 1){
                                        isLeave = true;
                                        window.location.reload();
                                    }else{
                                        $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                                    }
                                }
                            }
                        })
                    }else{
                        demo.addRule([{
                                ele:'input[name="tmpname"]',
                                datatype:"*1-20",
                                nullmsg:"<em></em>请输入模板名称！",
                        },
                        {
                                ele:'textarea[name="tmpTip"]',
                                datatype:"*1-500",
                                nullmsg:"<em></em>请输入联系电话！",
                        }]);
                        demo.config({
                            showAllError:true,
                            url:"/templates/editrefuse",
                            ajaxpost:{
                                success:function(result){
                                    if(result.status == 1){
                                        isLeave = true;
                                        window.location.reload();
                                    }else{
                                        $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                                    }
                                }
                            }
                        })
                    }//endif
		}
                
                
                //公共弹窗效果
                function colorbox(object){
                    var flags = object.href;
                    $.dialog({
                            title:object.title,
                            content:object.content,
                            padding:'10px 20px',
                            lock:true,
                            ok:function(){
                                if(false === flags){
                                    //防止未保存--弹窗-确定按钮
                                    if(addFlag){
                                        $("#addTmp").removeClass("grey");
                                    }
                                    var formId = $("#zgm_mb_tabd form").eq(0).attr("id");
                                    if(object.formId){
                                        formId = object.formId;
                                    }
                                    if(formId == "addForm"){ //添加模板
                                            if(object.tmpIds != -1){
                                                    $(".template_list").each(function( index ) {
                                                            if($(this).data("temid") == object.flag){
                                                                    $("#addForm").remove();
                                                                    var othis = $(this).find(".updateTmp");
                                                                    initUpForm(othis);
                                                                    addFlag = false;
                                                                    isLeave = false;
                                                            }	
                                                    });
                                            }else{
//                                                var othis = $(this).find(".addTmp");
//                                                initAdd(othis);
                                                 isLeave = false;
                                            }
                                    }else{ //修改模板
                                            if(object.tmpIds != -1){
                                                   $(".template_list").each(function( index ) {
                                                            if($(this).data("temid") == object.tmpIds){
                                                                    $(this).next().remove();
                                                                    $(this).show();
                                                                    isLeave = false;						
                                                            }
                                                    });
                                                    $(".template_list").each(function( index ) {
                                                            if($(this).data("temid") == object.flag){
                                                                    var othis = $(this).find(".updateTmp");
                                                                    initUpForm(othis);
                                                                    addFlag = false;
                                                                    isLeave = false;
                                                            }
                                                    });						
                                            }else{
                                                    $(".template_list").each(function( index ) {
                                                            if($(this).data("temid") == object.flag){
                                                                    $(this).next().remove();
                                                                    $(this).show();
                                                                    var othis = $(this).find(".updateTmp");
                                                                    initAdd();
                                                                    isLeave = false;						
                                                            }						
                                                    });
                                            }			
                                    }
                                    return;
                                }
                                var obj = {id:object.id};
                                callAjax(object.href,obj);  
                            },
                            cancel:function(){}
                        });
                }
	});
	//ajax公共调用方法
	function callAjax(href,obj){
                 $.ajax({
                    type : 'POST',
                    cache : false,
                    dataType : 'json',
                    url  : SLPGER.root  + href,
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