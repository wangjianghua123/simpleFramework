/**
 * @name 附件简历
 * @author fmj
 */
var rid="", isEdit = false,basicEdit = false,expEdit = false,appendEdit = false;
var istype = 0;
var emailok = false;//邮箱是否可用 默认不可用，验证通过后方可用
$(function(){
    init(),getinitcity(),initcalendar(),basicvalidate(),expinfovalidate(),getresumeintegrity(); 
});
function init(){
    //基本信息
    rid = $("#rid").val();
    if(rid!=""){
        isEdit = true;basicEdit = true;expEdit = true;
        $("#zg_basic_exist").show();
        $("#zg_basic_empty").hide();
        //求职意向
        $("#zg_exp_empty").hide();
        $("#zg_exp_exist").show();
        $("#zg_exp_null").hide();
        //附件简历
        if ($("#hid_attch_id").val() == "") {
            $("#zg_append_empty").show();
            $("#zg_append_exist").hide();
        } else {
            $("#zg_append_empty").hide();
            $("#zg_append_exist").show();
        }
    }else{
        $("#zg_basic_exist").hide();
        $("#zg_basic_empty").show();
        //求职意向
        $("#zg_exp_empty").hide();
        $("#zg_exp_exist").hide();
        $("#zg_exp_null").show();
        //附件简历
        $("#zg_append_empty").show();
        $("#zg_append_exist").hide();   
        $("#f_exp").hide();
    }
    //右侧(编辑/取消)按钮
    $("p.zg_t_r font").click(function(){
        if($(this).text() == "编辑" || $(this).text() == "添加"){
            addoredit(this);
        }else{
            //右侧取消按钮
            cancel(this);
        }
    });
    
    //上传简历按钮
    $(".zg_t_all").click(function(){
        addoredit(this);
    });
    
    //编辑框底部的取消按钮
    $(".zg_qxt").click(function(){
        cancel(this);
    });
    //编辑XX按钮
    $(".zg_null_link").click(function(){
        rid = $("#rid").val();
        itemid = $(this).attr("data-item");
        clearinput(itemid);
        if(itemid == "exp"){
            if(basicEdit === true && isEdit === true){
                $("#f_"+itemid).text("取消");
                $("#zg_"+itemid+"_null").hide();
                $("#zg_"+itemid+"_exist").hide();
                $("#zg_"+itemid+"_empty").show();
                isEdit = false;
            }else{
                $.qglobal.tip({content:"您还没有保存基本信息，请先保存基本信息", icon:'error' ,time:2000});
            }
        }else{
            if(expEdit === true){
                if(isEdit === true){//判断是否有编辑框在用
                    $("#f_"+itemid).text("取消");
                    $("#zg_"+itemid+"_null").hide();
                    $("#zg_"+itemid+"_exist").hide();
                    $("#zg_"+itemid+"_empty").show();
                    isEdit = false;
                }else{
                    $.qglobal.tip({content:"您还有未保存信息,请先保存后再进行编辑", icon:'error' ,time:2000});
                }
            }else{
                $.qglobal.tip({content:"您还没有填写求职意向信息，请先填写求职意向信息", icon:'error' ,time:2000});
            }
        }
    });

    //点击菜单赋值给文本框
    $(".option a").click(function(){
        var value=$(this).text();
        var dataid = $(this).attr("data-id");
        $(this).parent().siblings(".select_txt").text(value);
        $(this).parent().parent().parent().parent().parent().children("input").val(dataid);
    });
    
    //到岗时间选择
    $(".arrivaltime").click(function(){
        var rid = $("#rid").val();
        var did = $(this).attr("data-id");
        if(rid===""){
            $.qglobal.tip({content:"您还没有保存基本信息，请先保存基本信息", icon:'error' ,time:2000});
        }
        var data = { rid:rid,did:did };
        $.ajax({
            type: "POST",
            data: data,
            url:"/resume/editarrival",
            datatype:"json"
        }).done(function(result){
            if(1 === result.status){
                getresumeintegrity();
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            }else{
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        });
    });
    
    //确认操作
    $('.F_comfirmdialog').live('click', function() {
        var self = $(this),
                uri = self.attr('data-uri'),
                item  = self.attr('data-item'),
                acttype = self.attr('data-acttype'),
                title = (self.attr('data-title') != undefined) ? self.attr('data-title') : '提示消息',
                msg = self.attr('data-msg'),
                dshow = self.attr('data-show') == undefined ? 0 : 1,
                did = self.attr('data-id'),
                dtype = self.attr('data-type'),
                trlen = $(".right_tab tr:visible").length - 1; //获取显示的tr个数 然后减去第一行标题
        //初始化弹窗 必须放在弹窗启动之前 不然没有效果
        if (dshow == 1) {
            (function(d) {
                d['okValue'] = '确认';
                d['cancelValue'] = '取消';
                d['title'] = title;
            })($.dialog.defaults);
        }
        $.dialog({
            title: title,
            content: msg,
            padding: '10px 20px',
            lock: true,
            ok: function() {
                if (acttype == 'ajax') {
                    $.getJSON(uri, function(result) {
                        if (result.status == 1) {
                            $.qglobal.tip({content: result.msg});
                            if (dtype == 1) { //删除操作
                                $("#tr_" + did).parent().html("");
                                $("#zg_"+item+"_exist").hide();
                                $("#zg_"+item+"_empty").show();
                                $("#hid_attch_id").val("");
                                getresumeintegrity();
                            } else {
                                window.location.reload();
                            }
                            if (trlen - 1 == 0) { //当页面数据被处理完毕的时候重新刷新页面
                                window.location.reload();
                            }
                        } else {
                            $.qglobal.tip({content: result.msg, icon: 'error'}, 5000);
                        }
                    });
                } else {
                    location.href = uri;
                }
            },
            cancel: function() {
            }
        });
    });
    
    $("#btnpre").live("click",function(){
        rid = $("#rid").val();
        if(rid === ""){
           $.qglobal.tip({content: "请先填写简历基本信息", icon: 'error'}, 5000);
           return false;
        }
        $(this).attr("href",SLPGER.root+"resume/preview/rid/"+rid);
    });
}

//基本信息验证
function basicvalidate(){
    $("#basicinfoForm").validate({
        rules: {
            u_name: {
                required: true
            },
            u_phone:{
                required: true,
                isMobile: true,
                maxlength: 11
            },
            u_email:{
                required: true,
                email: true,
                maxlength: 100
            },
            u_gender:{
                required : true
            },
            u_birth:{
                required : true
            },
            u_education:{
                required : true
            },
            u_addr:{
                required : true
            },
            u_experience:{
                required : true
            }
        },
        messages: {
            u_name:{
                required:"必填"
            },
            u_phone:{
                required: "必填",
                isMobile: "请输入正确的手机号码",
                maxlength: "请输入11位正确的手机号码"
            },
            u_email:{
                required: "必填",
                email: "请输入正确的邮箱地址",
                maxlength: "输入100个字符以内的邮箱地址"
            },
            u_gender:{
                required : "必选"
            },
            u_birth:{
                required : "必填"
            },
            u_education:{
                required : "必选"
            },
            u_addr:{
                required : "必填"
            },
            u_experience:{
                required : "必选"
            }
        },
        submitHandler: function(form) {
            var issubmit = submit_sure();
            if(issubmit === false){
                return false;
            }
            var name = $("#u_name").val(),phone = $("#u_phone").val(),email = $("#u_email").val(),birth = $("#u_birth").val(),gender = $('input[name="u_gender"]:checked ').val(),
            edu = $("#hid_basic_edu").val(),addr = $("#u_addr").val(),workexp = $("#hid_basic_workexp").val(),edu_name = $("#hid_basic_eduname").text(),exp_name = $("#hid_basic_workexpname").text();
            $(form).find(":submit").attr("disabled", true);
            var data = {name: name,phone:phone,email:email,birth:birth,gender:gender,education:edu,addr:addr,experience:workexp,edu_name:edu_name,exp_name:exp_name,rstatus:1};
            rid = $("#rid").val();
            if(rid!=""){
                data.rid = rid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/addbasic",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    //保存基本信息成功
                    $("#rid").val(result.data.rid);
                    $("#e_name").html(result.data.name);
                    $("#e_phone").html(result.data.phone);
                    $("#e_email").html(result.data.email);
                    $("#e_birth").html(result.data.birth);
                    $("#e_gender").html((result.data.gender==0) ? "男" : "女");
                    $("#e_education").html(result.data.edu_name);
                    $("#e_addr").html(result.data.address);
                    $("#e_workexp").html(result.data.exp_name);
                    regain("basic");
                }else{
                    $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//求职意向验证
function expinfovalidate(){
    $("#expjobForm").validate({
        rules: {
            expjobid:{
                required : true
            },
            expcity:{
                required : true
            },
            expsalary:{
                required : true
            },
            expjobnature:{
                required : true
            }
        },
        messages: {
            expjobid:{
                required:"必选"
            },
            expcity:{
                required:"必选"
            },
            expsalary:{
                required:"必选"
            },
            expjobnature:{
                required:"必选"
            }
        },
        submitHandler: function(form) {
            var rid = $("#rid").val();
            var expjobid = $("#expjobid").val(),expjobname = $("#select_expjob").val(),expindustry = $("#expindustry").val(),expinduname = $("#select_industry").text(),expcity = $("#expcity").val(),expcityname = $("#select_city").val(),
                    expsalary = $("#expsalary").val(),expsalaryname = $("#select_salary").text(),expjobnature = $("#expjobnature").val(),expjobnaturename = $("#select_jobnature").text(),remarks = $("#remarks").val();
            $(form).find(":submit").attr("disabled", true);
            data = {rid:rid,jobid:expjobid,jobname:expjobname,industry:expindustry,industryname:expinduname,city:expcity,cityname:expcityname,salary:expsalary,salaryname:expsalaryname,jobnature:expjobnature,jobnaturename:expjobnaturename,remarks:remarks,type:"append"};
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveexpinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    //保存求职意向信息成功
                    $("#e_jobname").html(result.data.jobname);
                    $("#e_industry").html(result.data.industry);
                    $("#e_city").html(result.data.city);
                    $("#e_salary").html(result.data.salary);
                    $("#e_jobnature").html(result.data.jobnature);
                    $("#e_remarks").html(result.data.remarks);
                    isExp = true;
                    regain("exp");
                    window.location.href = SLPGER.root + "resume/appendix/rid/"+rid;
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//取消按钮
function cancel(obj){
    rid = $("#rid").val();
    itemid = $(obj).attr("data-item");
    isEdit = true;
    rid = $("#rid").val();
    var itemid = $(obj).attr("data-item");
    if(itemid != "basic"){
        if(itemid!="edu" && itemid!="active" && itemid!="work"){
             $("#f_"+itemid).text("编辑");
        }else{
             $("#f_"+itemid).text("添加");
        }
        $("#zg_"+itemid+"_empty").hide();
        switch(itemid){
            case "exp":
                if(expEdit === true){
                    showexist("exp");
                }else{
                    hideexist("exp");
                }
                break;
            case "append":
                if(eduEdit === true){
                    showexist("edu");
                }else{
                    hideexist("edu");
                }
                break;
            default:
                return;
        }
    }else{
        if(rid!=""){
            $("#f_"+itemid).text("编辑");
            basicEdit = true;
            $("#zg_"+itemid+"_exist").show();
            $("#zg_"+itemid+"_empty").hide();
        }else{
            clearinput(itemid);
        }
    }
}

//编辑/添加按钮
function addoredit(obj) {
    rid = $("#rid").val();
    itemid = $(obj).attr("data-item");
    if (itemid != "basic" && itemid != "exp") {
        if($("#zg_exp_exist").css("display")!="none" && $("#zg_basic_exist").css("display")!="none"){
            expEdit = basicEdit = isEdit = true;
        }
        //基本信息和求职意向信息为必填项
        if (expEdit === true) {
            if (isEdit === true) {
                if(itemid!="append"){
                    $(obj).text("取消");
                    $("#zg_" + itemid + "_exist").hide();
                    $("#zg_" + itemid + "_null").hide();
                    $("#zg_" + itemid + "_empty").show();
                }else{
                    document.getElementById('appendresume').click();
                }
            } else {
                $.qglobal.tip({content: "您还有未保存信息，请先保存后再进行编辑", icon: 'error', time: 2000});
            }
        } else {
            $.qglobal.tip({content: "您还没有填写求职意向信息，请先填写求职意向信息", icon: 'error', time: 2000});
        }
    } else {
        if (itemid != "basic") {
            if (basicEdit === true && isEdit === true) {
                $(obj).text("取消");
                $("#zg_" + itemid + "_exist").hide();
                $("#zg_" + itemid + "_null").hide();
                $("#zg_" + itemid + "_empty").show();
                isEdit = false;
            } else {
                $.qglobal.tip({content: "您还没有保存基本信息，请先保存基本信息", icon: 'error', time: 2000});
            }
        } else {
            if (isEdit === true) {
                isEdit = false;
                $("#f_" + itemid).text("取消");
                $("#zg_" + itemid + "_exist").hide();
                $("#zg_" + itemid + "_empty").show();
            } else {
                $.qglobal.tip({content: "您还没有保存基本信息，请先保存基本信息", icon: 'error', time: 2000});
            }
        }
    }
}

//清空内容
function clearinput(itemid){
    $("#zg_"+itemid+"_empty input[type='text']").val('');
    $("#zg_"+itemid+"_empty textarea").val('');
    $("#zg_"+itemid+"_empty .select_box span").text('');
    $("#zg_"+itemid+"_empty input[type='hidden']").val('');
    $("span.error").hide();
    $(".clear").click();
}

//成功保存基本信息后
function regain(itemid,haveitem){
    isEdit = true;//更新编辑状态
    $("p.zg_t_r font").text("编辑");
    switch(itemid){
        case "basic":
            basicEdit = true;
            break;
        case "exp":
            expEdit = true;
            break;
    }
    if(haveitem==0){
        $("#zg_"+itemid+"_exist").hide();
        $("#zg_"+itemid+"_empty").hide();
        $("#zg_"+itemid+"_null").show();
    }else{
        $("#zg_"+itemid+"_null").hide();
        $("#zg_"+itemid+"_empty").hide();
        $("#zg_"+itemid+"_exist").show();
    }
    getresumeintegrity();
}

//显示已填写内容
function showexist(itemid){
    $("#zg_"+itemid+"_exist").show();
    $("#zg_"+itemid+"_null").hide();
}

//隐藏已填写内容
function hideexist(itemid){
    $("#zg_"+itemid+"_exist").hide();
    $("#zg_"+itemid+"_null").show();
}

//初始化期望城市
function getinitcity(){
    $(document).click(function(){
        $('.box_city').hide();
        $('.boxUpDown').hide();
        $('.selectr').removeClass('selectrFocus');
    });
    $('.box_city').bind('click',function(e){e.stopPropagation();});

    //选城市名称
    $('.box_city').on('mouseenter','.city_main li',function(){
        $(this).children('ul').show();
        //城市名称三级菜单位置
        var sheight = '';
        $('.box_city .city_main').each(function(){
            $(this).children('li').each(function(i){
                sheight = $('.box_city').height() - ($(this).offset().top - $(this).parents('.box_city').offset().top + 32);
                if(sheight < $(this).children('.city_sub').height()){
                    if(navigator.userAgent.indexOf("MSIE")>0 && navigator.appVersion.match(/7./i)=="7."){
                        $(this).children('.city_sub').css({marginTop:'-30' - $(this).children('.city_sub').height() + 'px'});
                    }else{
                        $(this).children('.city_sub').css({marginTop:'-44' - $(this).children('.city_sub').height() + 'px'});
                    }
                }
            });
        });
    });
    $('.box_city').on('mouseleave','.city_main li',function(){
        $(this).children('ul').hide();
    });

    $('#select_city').bind('click',function(e){
        e.stopPropagation();
        $('.boxUpDown').hide();
        $('.selectr').removeClass('selectrFocus');
        $(this).addClass('selectrFocus');
        $('.box_city').show();
    });
    $('.box_city').on('click','.job_sub li',function(e){
        e.stopPropagation();
        var category = $(this).parent('ul.job_sub').siblings('span').text();
        var position = $(this).text();
        $('#select_city').css("color","#333").val(category).removeClass('selectrFocus');
        $('#select_city').val(position);
        $('#expcity').val($(this).attr("data-id"))
        $(this).parents('.job_sub').hide();
        $('.box_city').hide();
    });

    //城市名称三级菜单位置
    $('.box_city .city_main').each(function(){
        var sheight = '';
        $(this).children('li').each(function(i){
            if(i%3 == 1){
                $(this).children('.job_sub').css({marginLeft:'-160px'});
            }else if(i%3 == 2){
                $(this).children('.job_sub').css({marginLeft:'-310px'});
            }
        });
    });
}

//附件简历上传
function resumechange(){
    rid = $("#rid").val();
    if(rid == ""){
         $.qglobal.tip({content: "请先填写简历基本信息", icon: 'error', time: 2000});
         return false;
    }
    var data = { rid:rid };
    $("#appendupload").ajaxSubmit({
        dataType: 'json',
        data:data,
        success: function(result) {
            if (1 === result.status) {
                //上传成功
                $("#hid_attch_id").val(result.data.fileid);
                regain("append");
                $("#zg_append_exist .zgOff").html(result.data.filename+'<em id="tr_'+result.data.fileid+'" class="F_comfirmdialog" data-acttype="ajax" data-uri="'+SLPGER.root+"resume/delattach/id/"+result.data.fileid+"/rid/"+rid+'" data-true="1" data-msg="删除附件则此附件简历将不可用于找工作，确定要删除此附件简历吗？" data-type="1" data-id="'+result.data.fileid+'" data-item="append"></em>');
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }
    });
}

//图片上传
function headimgupload(){
    $("#myupload").ajaxSubmit({
        dataType: 'json',
        success: function(result) {
            if (1 === result.status) {
                //上传成功，显示裁剪页面
                selfcropzoom(SLPGER.root + result.data.path);
                $("#cboxOverlay").show();
                $("#colorbox").show();
                $("#r").next("div").hide();
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }
    });
}

//图片裁剪
function selfcropzoom(uploadimgpath){
    var cropzoom = $('#cropzoom_container').cropzoom({
        width: 360,
        height: 360,
        bgColor: '#ccc',
        enableRotation: true,
        enableZoom: true,
        selector: {
            w: 252,
            h: 252,
            showPositionsOnDrag: true,
            showDimetionsOnDrag: false,
            centered: true,
            bgInfoLayer: '#fff',
            borderColor: 'blue',
            animated: false,
            maxWidth: 252,
            maxHeight: 252,
            borderColorHover: 'yellow'
        },
        image: {
            source: uploadimgpath,
            width: 252,
            height: 252,
            minZoom: 252,
            maxZoom: 252
        }
    });
    //裁剪按钮
    $("#crop").click(function() {
        var cutult = SLPGER.root + "resume/cutImg/fromtype/face";
        cropzoom.send(cutult, 'POST', {}, function(imgRet) {
            //var imghtml = "<img src=" + SLPGER.root+imgRet.data.path + " width='140' height='160' />";
            $("#logoimg").attr("src",SLPGER.root+imgRet.data.path);
            closebox();
        });
    });
}

//关闭图片裁剪窗口
function closebox(){
    $("#cboxOverlay").hide();
    $("#colorbox").hide();
}
//获取简历完整度
function getresumeintegrity(){
    var allnum = 0;
    if($("#rid").val()!=""){
        allnum += 25;
    }
    if($("#e_jobname").text()!=""){
        allnum += 25;
    }
    if($(".select_txt1").text()!="请选择到岗时间"){
        allnum += 25;
    }
    if($("#hid_attch_id").val()!=""){
        allnum += 25;
    }
    $("#integrity").html(allnum+"%");
}
function submit_sure(){
    var gnl = confirm("基本信息将会应用于所有的简历，您确定要提交吗?");
    if (gnl == true) {
        return true;
    } else {
        return false;
    }
}
function clearcontent(obj){
    $('input[name="phone"]').val('');
    $('input[name="email"]').val('');
    $('input[name="verify"]').val('');
    $("#J_phoneTip").attr("class","");
    $("#J_phoneTip").html("");
    $(obj).parent().parent().hide();
    $(".popIframe").hide();
}

//底部保存按钮
$(".zg_t_bcz").live("click",function(){
    if($("#e_jobname").text()!="" && $("#rid").val()!=""){
        $.qglobal.tip({content:"您的简历已经创建成功！", icon:'success' ,time:3000});
        setTimeout(function(){
            window.location.href = SLPGER.root + "resume/index";
        },3000);
    }else{
        $.qglobal.tip({content:"您还没有保存简历求职意向信息！", icon:'error' ,time:3000});
    }
});