/**
 * @name 前台UI&TOOLS
 * @author wjh
 */
;
(function ($) {
    $.qglobal.init = function () {
        $.qglobal.ui.init();
    }
    $.qglobal.ui = {
        init: function () {
            $.qglobal.ui.return_top(); //返回顶部
            $.qglobal.ui.refresh_resume();//刷新简历
            $.qglobal.ui.default_resume();//默认简历
            $.qglobal.ui.refresh_jobs();//刷新职位
            $.qglobal.ui.refresh_alljobs();//刷新所有在线职位
            $.qglobal.ui.collect_jobs();//收藏和取消收藏职位
            $.qglobal.ui.jobs_delivery();//投递简历
        },
        //返回顶部
        return_top: function () {
            $('#J_returntop')[0] && $('#J_returntop').returntop();
        },
        //刷新简历
        refresh_resume: function () {
            $('.J_refresh').live('click', function () {
                var did = $(this).attr('data-id');
                if (did == 0) {
                    $.qglobal.tip({content: "请选择一行进行操作", icon: 'error', time: 3000});
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'resume/refresh',
                    data: {id: did},
                    success: function (result) {
                        if (result.status == 0) {
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                            $("#reftime_" + did).html(result.data);
                        }
                    }
                });
            });
        },
        //默认简历
        default_resume: function () {
            $('.zg_szmr').live('click', function () {
                var did = $(this).attr('data-id');
                if (did == 0) {
                    $.qglobal.tip({content: "请选择一行进行操作", icon: 'error', time: 3000});
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'resume/default',
                    data: {id: did},
                    success: function (result) {
                        if (result.status == 0) {
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                            $("#J_setdefault_" + did).html('已默认');
                            $("#J_setdefault_" + did).removeClass('zg_szmr');
                            window.location.reload();
                        }
                    }
                });
            });
        },
        //刷新职位
        refresh_jobs: function () {
            $('.J_job_refresh').live('click', function () {
                var did = $(this).attr('data-id');
                if (did == 0) {
                    $.qglobal.tip({content: "职位id为空", icon: 'error', time: 3000});
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'jobs/refreshjob',
                    data: {job_id: did},
                    success: function (result) {
                        if (result.status == 0) {
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                            $("#J_refresh_time_" + did).html("刷新时间：" + result.data);
                        }
                    }
                });
            });
        },
        //刷新所有在线职位
        refresh_alljobs: function () {
            $('#J_refresh_alljobs').live('click', function () {
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'jobs/refreshonlinejobs',
                    data: '',
                    success: function (result) {
                        if (result.status == 0) {
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                        }
                    }
                });
            });
        },
        collect_jobs: function () {
            $('#J_job_collect').live('click', function () {
                var did = $(this).attr('data-id');
                var dflag = $(this).attr('data-flag');
                var cid = $(this).attr('data-cid');
                if (did == 0) {
                    $.qglobal.tip({content: "职位id为空", icon: 'error', time: 3000});
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'jobs/collectjobs',
                    data: {job_id: did, flag: dflag, c_id: cid},
                    success: function (result) {
                        if (result.status == 1) {
                            if (dflag == 1) { //收藏
                                $('#J_job_collect').removeClass('sc').addClass("yshouc");
                                $('#J_job_collect').attr("data-flag", 2);
                                $('#J_job_collect').html('已收藏');
                                $('.shouc p').removeClass('quxiao').addClass("scang");
                                $('.shouc em').html('已收藏该职位');
                                if(cid == 0){
                                    $('#J_job_collect').attr('data-cid',result.data);
                                }
                            } else if (dflag == 2) { //取消收藏
                                $('#J_job_collect').removeClass('yshouc').addClass("sc");
                                $('#J_job_collect').attr("data-flag", 1);
                                $('#J_job_collect').html('收藏职位');
                                $('.shouc p').removeClass('scang').addClass("quxiao");
                                $('.shouc em').html('已取消收藏')
                            }
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                        } else if(result.status == 2) {
                            $.qglobal.ui.dlogin();
                        }else{
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        }
                    }
                });
            });
        },
        jobs_delivery: function () {
            $('#J_job_delivery').live('click', function () {
                

                return 0;
                var jobid = $(this).attr('data-id');
                if (jobid == 0) {
                    $.qglobal.tip({content: "职位id为空", icon: 'error', time: 3000});
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: SLPGER.root + 'jobs/deliveryjobs',
                    data: {
                        job_id: jobid
                    },
                    success: function (result) {
                        if (result.status == 0) {
                            $.qglobal.tip({content: result.msg, icon: 'error', time: 3000});
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'success', time: 3000});
                        }
                    }
                });
            });
        },
        dlogin: function(){
            $.getJSON(SLPGER.root + 'foreuser/dlogin', function(result){
                if(result.status == 0){
                    $.slglobal.tip({content:result.msg, icon:'error'});
                }else{
                    $.dialog({id:'dlogin', title:"登录", content:result.data, padding:'', fixed:true, lock:true,width:"620px"});
                    $.qglobal.ui.dlogin_form($('#J_dlogin_form'));
                }
            });
        },
        dlogin_form: function(form){
            form.ajaxForm({
                beforeSubmit: function(){
                    var username = form.find('#J_username').val(),
                        password = form.find('#J_password').val();
                    if(username == ''){
                        $.qglobal.tip({content:'请输入邮箱或者手机号！', icon:'error'});
                        form.find('#J_username').focus();
                        return !1;
                    }
                    if(password == ''){
                        $.qglobal.tip({content:'请输入密码！', icon:'error'});
                        form.find('#J_password').focus();
                        return !1;
                    }
                },
                success: function(result){
                    if(result.status == 1){
                        $.qglobal.tip({content:result.msg, icon:'success',time:2000});
                        setTimeout("window.location.reload()",2000);
                    } else {
                        $.qglobal.tip({content:result.msg, icon:'error'});
                    }
                },
                dataType: 'json'
            });
        }//最后一个这里不加逗号
    }
    $.qglobal.init();
})(jQuery);