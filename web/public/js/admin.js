/**
 * **********************后台操作JS************************
 * ajax 状态显示
 * confirmurl 操作询问
 * showdialog 弹窗表单
 * attachment_icon 附件预览效果
 * preview 预览图片大图
 * cate_select 多级菜单动态加载
 * author wjh
 */
;$(function($){

	//确认操作
	$('.J_confirmurl').live('click', function(){
		var self = $(this),
                uri = self.attr('data-uri'),
                acttype = self.attr('data-acttype'),
                title = (self.attr('data-title') != undefined) ? self.attr('data-title') : '提示消息',
                msg = self.attr('data-msg'),
                dshow = self.attr('data-show') == undefined ? 0 : 1,
                did = self.attr('data-id'),
                dtype = self.attr('data-type');
                //初始化弹窗 必须放在弹窗启动之前 不然没有效果
                if(dshow == 1){
                    (function(d) {
                        d['okValue'] = '确认';
                        d['cancelValue'] = '取消';
                        d['title'] = title;
                    })($.dialog.defaults);
                }
		$.dialog({
                    title:title,
                    content:msg,
                    padding:'10px 20px',
                    lock:true,
                    ok:function(){
                        if(acttype == 'ajax'){
                            $.getJSON(uri, function(result){
                                if(result.status == 1){
                                    $.qglobal.tip({content:result.msg,icon:'success'},5000);
                                    if(dtype == 1){ //删除操作
                                        $("#tr_"+did).remove();
                                    }else{
										if(dtype == 'getcount'){
										   getCounts();
										}else{
											window.location.reload();
										}
                                    }
                                    var trlen = $(".J_Itemsdiv .oItemJianli").length; //获取显示的tr个数 然后减去第一行标题
                                    if(trlen == 0){
                                         window.location.reload();
                                    }
                                }else{
                                    $.qglobal.tip({content:result.msg, icon:'error'},5000);
                                }
                            });
                        }else{
                           location.href = uri;
                        }
                    },
                    cancel:function(){}
		});
	});
	
	//弹窗表单
	$('.J_showdialog').live('click', function(){
		var self = $(this),
                dtitle = self.attr('data-title'),
                did = self.attr('data-id'),
                ddid = self.attr('data-did'),
                duri = self.attr('data-uri'),
                dwidth = parseInt(self.attr('data-width')),
                dheight = undefined == self.attr('data-height') ? 0 : parseInt(self.attr('data-height')),
                acttype = self.attr('data-acttype'),
                dtype = self.attr('data-type'),
                dok = self.attr('data-ok'),
                dcancel = self.attr('data-cancel'),
                dpadding = (self.attr('data-padding') != undefined) ? self.attr('data-padding') : '';
                //初始化弹窗 必须放在弹窗启动之前 不然没有效果
                (function(d) {
                    d['okValue'] = dok;
                    d['cancelValue'] = dcancel;
                    d['title'] = dtitle;
                })($.dialog.defaults);
		$.dialog({id:did}).close();
		$.dialog({
                    id:did,
                    title:dtitle,
                    width:dwidth ? dwidth : 'auto',
                    height:dheight ? dheight : 'auto',
                    padding:dpadding,
                    lock:true,
                    ok:function(){
                        var info_form = this.dom.content.find('#info_form');
                        if(info_form[0] != undefined){
                            if(acttype == "ajax"){
                                var info_form_action = this.dom.content.find('#info_form').attr('action'); //获取form的action
                                $.ajax({
                                    type : 'POST',
                                    cache : false,
                                    dataType : 'JSON',
                                    url  : info_form_action,
                                    data : $('#info_form').serialize(), //form表单序列化
                                    success:function(result){ //用ajax方法发送信息到当前Action中的main方法                                    
                                        if(result.status == 1){
                                            $.dialog({id:did}).close();
                                            $.qglobal.tip({content:result.msg, icon:'success' ,time:2000});
                                            if(dtype == 1){ //移除操作
                                                $("#tr_"+ddid).remove();
                                            }else{
                                                window.location.reload();
                                            }
                                            var trlen = $(".J_Itemsdiv div:visible").length; //获取显示的tr个数 然后减去第一行标题
                                            if(trlen == 0){
                                                 window.location.reload();
                                            }
                                        }else{
                                            //$.dialog({id:did}).close();
                                            $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                                        }
                                    }
                                });
                            }else{
                                info_form.submit();
                            }
                            return false;
                        }
                    },
                    cancel:function(){}
		});
		$.getJSON(duri, function(result){
                    if(result.status == 1){
                        $.dialog.get(did).content(result.data);
                        return false;
                    }else if(result.status == 2){ //判断是否登录过
                        $.dialog({id:did}).close();
                        $.qglobal.ui.dlogin();
                    }else{
                        $.dialog({id:did}).close();
                        $.qglobal.tip({content:result.msg, icon:'error'});
                        if(dtype == 6){ //转发弹窗显示
                           positionD(result.data);
                        }
                    }
		});
                function positionD(data){
                    //计算显示位置
                    var p = $(".oZfAlert").offset(),
                        l = 0,
                        t = 0,
                        w = $(window).width(),
                        h = $(window).height();
                    if(w < 1481){
                        l = w - 100;
                    }else{
                        l = 1481;
                    }
                    if(h < 911){
                        t = h - 100;
                    }else{
                        t = p.top + (h - p.top);
                    }
                    $('.oZfAlert').css({
                        top: t + "px",
                        left: l + "px"
                    });
                    $(".oZfAlert").show();
                    $("#show_alertemail").html(data);
                }
		return false;
	});
        
        
        
        //弹窗放视频
	$('.J_showmediadialog').live('click', function(){
		var self = $(this),
                dtitle = self.attr('data-title'),
                did = self.attr('data-id'),
                ddid = self.attr('data-did'),
                duri = self.attr('data-uri'),
                dcancel = self.attr('data-cancel'),
                dwidth = parseInt(self.attr('data-width')),
                dheight = undefined == self.attr('data-height') ? 0 : parseInt(self.attr('data-height')),
                dtype = self.attr('data-type');
                //初始化弹窗 必须放在弹窗启动之前 不然没有效果
                (function(d) {
                    d['cancelValue'] = dcancel;
                    d['title'] = dtitle;
                })($.dialog.defaults);
		$.dialog({id:did}).close();
		$.dialog({
                    id:did,
                    title:dtitle,
                    width:dwidth ? dwidth : 'auto',
                    height:dheight ? dheight : 'auto',
                    padding:'',
                    lock:true,
                    ok:false,
                    cancel:true
		});
		$.getJSON(duri, function(result){
                    if(result.status == 1){
                        $.dialog.get(did).content(result.data);
                        return false;
                    }else{
                        $.dialog({id:did}).close();
                        $.qglobal.tip({content:result.msg, icon:'error'});
                    }
		});
	});
	
	//附件预览
	$('.J_attachment_icon').live('mouseover', function(){
		var ftype = $(this).attr('file-type');
		var rel = $(this).attr('file-rel');
		switch(ftype){
                    case 'image':
                        if(!$(this).find('.attachment_tip')[0]){
                                $('<div class="attachment_tip"><img src="'+rel+'" /></div>').prependTo($(this)).fadeIn();
                        }else{
                                $(this).find('.attachment_tip').fadeIn();
                        }
                        break;
		}
	}).live('mouseout', function(){
		$('.attachment_tip').hide();
	});
	
	$('.J_attachment_icons').live('mouseover', function(){
		var ftype = $(this).attr('file-type');
		var rel = $(this).attr('file-rel');
		switch(ftype){
                    case 'image':
                        if(!$(this).find('.attachment_tip')[0]){
                                $('<div class="attachment_tip" style="width:160px; height:80px;"><img width="160" height="80" src="'+rel+'" /></div>').prependTo($(this)).fadeIn();
                        }else{
                                $(this).find('.attachment_tip').fadeIn();
                        }
                        break;
		}
	}).live('mouseout', function(){
		$('.attachment_tip').hide();
	});
        
        
        //全部操作
        $('a[data-tdtype="all_action"]').live('click', function() {
                var btn = this;
                var ids = '';
                var uri = $(btn).attr('data-uri'),
                msg = $(btn).attr('data-msg'),
                acttype = $(btn).attr('data-acttype'),
                title = ($(btn).attr('data-title') != undefined) ? $(this).attr('data-title') : '提示信息';
                if(msg != undefined){
                    $.dialog({
                        id:'confirm',
                        title:title,
                        width:360,
                        padding:'10px 20px',
                        lock:true,
                        content:msg,
                        ok:function(){
                                action();
                        },
                        cancel:function(){}
                    });
                }else{
                    action();
                }
                function action(){
                        if(acttype == 'ajax_form'){
                            var did = $(btn).attr('data-id'),
                            dwidth = parseInt($(btn).attr('data-width')),
                            dheight = parseInt($(btn).attr('data-height'));
                            $.dialog({
                                id:did,
                                title:title,
                                width:dwidth ? dwidth : 'auto',
                                height:dheight ? dheight : 'auto',
                                padding:'',
                                lock:true,
                                ok:function(){
                                    var info_form = this.dom.content.find('#info_form');
                                    if(info_form[0] != undefined){
                                        info_form.submit();
                                        return false;
                                    }
                                },
                                cancel:function(){}
                            });
                            $.getJSON(uri, function(result){
                                if(result.status == 1){
                                    $.dialog.get(did).content(result.data);
                                }else{
                                    $.dialog({id:did}).close();
                                    $.qglobal.tip({content:result.msg, icon:'error'});
                                }
                            });
                        }else if(acttype == 'ajax'){
                            $.getJSON(uri, function(result){
                                if(result.status == 1){
                                    $.qglobal.tip({content:result.msg});
                                    window.location.reload();
                                }else{
                                    $.qglobal.tip({content:result.msg, icon:'error'});
                                }
                            });
                        }else{
                            location.href = uri;
                        }
                }
        });
        
        
        //批量操作
       $('input[data-tdtype="batch_action"]').live('click', function() {
            var btn = this;
            if($('.J_checkitem:checked').length == 0){
                $.qglobal.tip({content:'请先选择后再操作', icon:'error'});
                return false;
            }
            var ids = '';
            $('.J_checkitem:checked').each(function(){
                    ids += $(this).val() + ',';
            });
            ids = ids.substr(0, (ids.length - 1));
            var uri = $(btn).attr('data-uri') + $(btn).attr('data-name') + '/' + ids,
            msg = $(btn).attr('data-msg'),
            acttype = $(btn).attr('data-acttype'),
            title = ($(btn).attr('data-title') != undefined) ? $(this).attr('data-title') : '提示信息',
		    dtype = ($(btn).attr('data-type') != undefined) ? $(this).attr('data-type') : 0,
            dok = $(btn).attr('data-ok'),
            dcancel = $(btn).attr('data-cancel');
            //初始化弹窗 必须放在弹窗启动之前 不然没有效果
            (function(d) {
                d['okValue'] = dok;
                d['cancelValue'] = dcancel;
                d['title'] = title;
            })($.dialog.defaults);
            if(msg != undefined){
                $.dialog({
                    id:'confirm',
                    title:title,
                    width:300,
                    padding:'10px 20px',
                    lock:true,
                    content:msg,
                    ok:function(){
                       action();
                    },
                    cancel:function(){}
                });
            }else{
                action();
            }
            function action(){
                    if(acttype == 'ajax_form'){
                            var did = $(btn).attr('data-id'),
                            dwidth = parseInt($(btn).attr('data-width')),
                            dheight = parseInt($(btn).attr('data-height'));
                            $.dialog({
                                id:did,
                                title:title,
                                width:dwidth ? dwidth : 'auto',
                                height:dheight ? dheight : 'auto',
                                padding:'',
                                lock:true,
                                ok:function(){
                                    var info_form = this.dom.content.find('#info_form');
                                    if(info_form[0] != undefined){
                                        info_form.submit();
                                        return false;
                                    }
                                },
                                cancel:function(){}
                            });
                            $.getJSON(uri, function(result){
                                if(result.status == 1){
                                    $.dialog.get(did).content(result.data);
                                }else{
                                    $.dialog({id:did}).close();
                                    $.qglobal.tip({content:result.msg, icon:'error'});
                                }
                            });
                    }else if(acttype == 'ajax'){
                            $.getJSON(uri, function(result){
                                if(result.status == 1){
                                    $.qglobal.tip({content:result.msg});
									if(dtype == 'getcount'){
										getCounts();
									}else{
										window.location.reload();
									}
                                }else{
                                    $.qglobal.tip({content:result.msg, icon:'error'});
                                }
                            });
                    }else{
                            location.href = uri;
                    }
            }
    });
});