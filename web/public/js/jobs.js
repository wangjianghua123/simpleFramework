/**
 * @name 职位管理操作相关
 * @author wjh
 */
;(function($){
    $.qglobal.jobs = {
	//相关设置
        settings: {
            card_tea_btn: '.J_tea_card'
        },
		//初始化
        init: function(options){
            options && $.extend($.qglobal.jobs.settings, options);
            var s = $.qglobal.jobs.settings;
        },
	dcheckpj_form: function(form){
            form.ajaxForm({
                beforeSubmit: function(){
		    var is_stf = form.find("input:radio[name='is_stf']:checked").val();
                    var stf_reason = form.find('#stf_reason').val();
                    var stf_help = form.find('#stf_help').val();
                    var prog_content = form.find('#prog_content').val();
                    var services = form.find('#services').val();
                    if(is_stf  && is_stf != 1){ //不满意的情况下
                            if(stf_reason == ''){
                                    $.qglobal.tip({content:'请输入不满意原因！', icon:'error'});
                                    $("#stf_reason").focus();
                                    return !1;
                            }
                            if(stf_help == ''){
                                    $.qglobal.tip({content:'请输入对您的帮助有哪些！', icon:'error'});
                                    $("#stf_help").focus();
                                    return !1;
                            }
                            if(prog_content == ''){
                                    $.qglobal.tip({content:'请输入我们需要在哪些方面改进！', icon:'error'});
                                    $("#prog_content").focus();
                                    return !1;
                            }
                            if(services == ''){
                                    $.qglobal.tip({content:'请输入在申论学习方面还需要哪些服务！', icon:'error'});
                                    $("#services").focus();
                                    return !1;
                            }
                    }else if(typeof(is_stf) == "undefined"){
                            $.qglobal.tip({content:'请先选择满意度评价！', icon:'error'});
                            return !1;
                    }
                },
                success: function(result){
                    if(result.status == 1){
                        $.qglobal.tip({content:result.msg, icon:'success',time:2000});
                        $.dialog.get('my_pj').close(); //关闭弹窗
                    } else {
                        $.qglobal.tip({content:result.msg, icon:'error'});
                    }
                },
                dataType: 'json'
            });
        },
        //职位提交检测
        jobs_form: function(form){
            //验证
            $.formValidator.initConfig({formid:'J_jobs_form',autotip:true});
            //学员名验证
            $('#J_username').formValidator({onshow:' ',onfocus:lang.username_tip, oncorrect: '用户名可用'})
            .inputValidator({min:1,onerror:lang.please_input+lang.username})
            .inputValidator({max:20,onerror:lang.username_tip})
            .ajaxValidator({
                type: 'get',
                url: SLPGER.root + '/?m=user&a=ajax_check',
                data: 'type=username',
                datatype: 'json',
                async:'false',
                success: function(result){
                    return result.status == '1' ? !0 : !1;
                },
                buttons: $('#J_regsub'),
                onerror: lang.username_exists,
                onwait : lang.wait
            });
            //密码验证
            $('#J_password').formValidator({onshow:' ',onfocus:lang.password_tip, oncorrect: '密码正确'})
            .inputValidator({min:6,onerror:lang.password_too_short})
            .inputValidator({max:20,onerror:lang.password_too_long});

			//确认密码验证
            $('#J_repassword').formValidator({onshow:' ',onfocus:lang.repassword_tip, oncorrect: '确认密码正确'})
            .inputValidator({min:1,onerror:lang.repassword_empty})
            .compareValidator({desid:'J_password',operateor:'=',onerror:lang.passwords_not_match});

			//真实姓名验证
			$('#J_real_name').formValidator({onshow:' ',onfocus:lang.realname_tip, oncorrect: '真实姓名可用'})
            .inputValidator({min:1,onerror:lang.please_input+lang.realname})
            .inputValidator({max:10,onerror:lang.realname_tip})
			.regexValidator({regexp:"[^\x00-\xff]",onerror:lang.realname_format_error});

			//手机号验证
			$("#J_mobile").formValidator({onshow:' ',onfocus:lang.mobile_tip,oncorrect:'手机号可用'})
			.inputValidator({min:1,onerror:lang.please_input+lang.mobile})
			.regexValidator({regexp:"mobile",datatype:"enum",onerror:lang.mobile_format_error})
			.ajaxValidator({
				type: 'get',
                url: SLPGER.root + '/?m=user&a=ajax_check',
                data: 'type=mobile',
                datatype: 'json',
                async:'false',
                success: function(result){
                    return result.status == '1' ? !0 : !1;
                },
                buttons: $('#J_regsub'),
                onerror: lang.mobile_exists,
                onwait : lang.wait
			});

			//参加考试类型验证
			$("#J_exam_type").formValidator({onShow:' ',onFocus:lang.exam_type_tip,onCorrect:'备考项目可用'})
			.inputValidator({min:1,onError:lang.exam_type_error});
        }
    };
    $.qglobal.jobs.init(); //学员
})(jQuery);