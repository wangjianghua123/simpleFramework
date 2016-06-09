/**
 * 在线添加简历
 */
var basicEdit = false;
var expEdit = false;
var isNewEdu = false;//是否为新增教育经历
var isNewActive = false;
var isNewWork = false;
var eid = "";//简历教育经历编号
var rid = "";//简历编号
var riid = "";//简历详情编号(用于获奖经历、证书及技能、自我评价)
var raid = "";//在校经历编号
var wid = "";//工作经历(工作经历)
var isEdit = false;//是否可编辑 默认不可编辑
var jtid = 0;
var itemid = "";
var istype = 0;
var rdid = 0;//作品展示id
var emailok = false;//邮箱是否可用 默认不可用，验证通过后方可用
$(function(){
    rid = $("#rid").val();
    if(rid==""){init();}
    getinitcity(),initcalendar(),initjobtemplate();
    //取消使用模板
    $("#modcancel").live("click",function(){
        $("#hid_mod_id").val("");
        $("#zwname").text("");
        $("#jyname").text("");
        $("#lgname").text("");
        $(".zg_jl_xx span").html('请点击此处选择：');
        $(".zg_jl_xx select_template").show();
        $(".zg_jl_xx modcancel").hide();
        cancelusingtemplate("interest");
        cancelusingtemplate("win");
        cancelusingtemplate("skill");
        cancelusingtemplate("eval");
        cancelusingtemplate("edu");
        cancelusingtemplate("work");
        cancelusingtemplate("active");
        $("#zg_edu_exist").html("");
        $("#zg_active_exist").html("");
        $("#zg_work_exist").html("");
        rid = $("#rid").val();
        if(rid!=""){
            window.location.href =  SLPGER.root + "resume/online"+"/rid/"+rid+"/jtid/0";
        }else{
            window.location.href =  SLPGER.root + "resume/online/jtid/0";
        }
    });
    
    jtid = $("#hid_mod_id").val();
    if(jtid!=0 && typeof(jtid)!="undefined" && jtid != -1){//选择模板并给页面内容赋值
        showtemplatecontent();
    }
    if(rid!=""){
        isEdit = true;
        basicEdit = true;
        expEdit = true;
        showexist("basic");
        showexist("exp");
        if($("#zg_edu_exist").children("div").length > 0){
            showexist("edu");
            $("#zg_edu_exist .zg_t_kc").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dt").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dd").css("color","#333");
        }
        if($("#zg_active_exist").children("div").length > 0){
            showexist("active");
        }
        if($("#zg_work_exist").children("div").length > 0){
            showexist("work");
        }
        if($("#e_skill").text()!=""){
            showexist("skill");
        }
        if($("#e_interest").text()!=""){
            showexist("interest");
        }
        if($("#e_win").text()!=""){
            showexist("win");
        }
        if($("#e_eval").text()!=""){
            showexist("eval");
        }
        if($("#e_workexp").text()=="应届生"){
            $(".zg_t_l_8").text("实习经历");
            $("#zg_work_null .zg_null_link").text("添加实习经历");
            $("#s_shsj").text("实习经历");
        }
        if($("#zg_handwork_exist").children("div:eq(0)").children("div").length > 0 || $("#zg_handwork_exist").children("div:eq(1)").children("div").length > 0){
            showexist("handwork");
        }
        getresumeintegrity();
    }

    //右侧(编辑/取消)按钮
    $("p.zg_t_r font").click(function(){
        rid = $("#rid").val();
        itemid = $(this).attr("data-item");
        if($(this).text() == "编辑"){
            if(itemid!="basic" && itemid != "exp"){
                //基本信息和求职意向信息为必填项
                if(expEdit === true){
                    if(isEdit === true){
                        isEdit = false;
                        $(this).text("取消");
                        $("#zg_"+itemid+"_exist").hide();
                        $("#zg_"+itemid+"_null").hide();
                        $("#zg_"+itemid+"_empty").show();
                    }else{
                        $.qglobal.tip({content:"您还有未保存信息，请先保存后再进行编辑", icon:'error' ,time:2000});
                    }
                }else{
                    $.qglobal.tip({content:"您还没有填写求职意向信息，请先填写求职意向信息", icon:'error' ,time:2000});
                }
            }else{
                if(itemid != "basic"){
                    if(basicEdit === true && isEdit === true ){
                        $(this).text("取消");
                        $("#zg_"+itemid+"_exist").hide();
                        $("#zg_"+itemid+"_null").hide();
                        $("#zg_"+itemid+"_empty").show();
                        isEdit = false;
                    }else{
                        $.qglobal.tip({content:"您还没有保存基本信息，请先保存基本信息", icon:'error' ,time:2000});
                    }
                }else{
                    if(isEdit === true){
                        isEdit = false;
                        $("#f_"+itemid).text("取消");
                        $("#zg_"+itemid+"_exist").hide();
                        $("#zg_"+itemid+"_empty").show();
                    }else{
                        $.qglobal.tip({content:"您还有未保存信息，请先保存后再进行编辑", icon:'error' ,time:2000});
                    }
                }
            }
        }else{
            //右侧取消按钮
            cancel(itemid);
        }
    });

    //编辑框底部的取消按钮
    $(".zg_qxt").live("click",function(){
        itemid = $(this).attr("data-item");
        cancel(itemid);
    });
    
    //编辑XX按钮
    $(".zg_null_link").click(function(){
        rid = $("#rid").val();
        itemid = $(this).attr("data-item");
        if(itemid == "exp"){
            if(basicEdit === true && isEdit === true){
                clearinput(itemid);
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
                    clearinput(itemid);
                    $("#f_"+itemid).text("取消");
                    $("#zg_"+itemid+"_exist").hide();
                    $("#zg_"+itemid+"_empty").show();
                    $("#zg_"+itemid+"_null").hide();
                    switch(itemid){
                        case "edu":
                        case "active":
                        case "work":
                        case "handwork":
                            isNewWork = isNewEdu = isNewActive = true;
                            $("#zg_"+itemid+"_null").css("height","58px");
                            $("#zg_"+itemid+"_null").css("line-height","58px");
                            $("#"+itemid+"Form").insertAfter($("#zg_"+itemid+"_exist").children("div:last-child")).show();
                            $("#zg_"+itemid+"_exist").show();
                            $("#divupload").show();
                            break;
                    }
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
    $(".option a").live("click",function(){
        var value = $(this).text();
        if($(this).parent().parent().children("span").attr("id") == "hid_basic_workexpname"){
            if(value == "应届生"){
                $(".zg_t_l_8").text("实习经历");
                $("#zg_work_null .zg_null_link").text("添加实习经历");
                $("#s_shsj").text("实习经历");
            }else{
                $(".zg_t_l_8").text("工作经历");
                $("#zg_work_null .zg_null_link").text("添加工作经历");
                $("#s_shsj").text("工作经历");
            }
        }
        var dataid = $(this).attr("data-id");
        $(this).parent().siblings(".select_txt").text(value);
        $(this).parent().parent().parent().parent().parent().children("input").val(dataid);
        $(this).parent().parent().parent().parent().parent().children("span.error").hide();
    });
    
    //到岗时间
    $(".arrivaltime").click(function(){
        rid = $("#rid").val();
        var did = $(this).attr("data-id");
        if(rid==""){
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
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
                getresumeintegrity();
            }else{
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        });
    });
    
    //确认操作 用于刷新页面局部内容 而不是刷新整个页面
    $('.F_comfirmdialog').live('click', function() {
        var self = $(this),
        uri = self.attr('data-uri'),
        itemid  = (self.attr('data-item') != undefined) ? self.attr('data-item') : '',
        title = (self.attr('data-title') != undefined) ? self.attr('data-title') : '提示消息',
        msg = self.attr('data-msg');
        $.dialog({
            title: title,
            content: msg,
            padding: '10px 20px',
            lock: true,
            ok: function() {
                $.getJSON(uri, function(result) {
                    if (result.status == 1) {
                        $.qglobal.tip({content: result.msg});
                        if(itemid!="append"){
                            if(itemid!="handwork"){
                                self.parent().parent().parent().remove();
                                if($("#zg_"+itemid+"_exist").children("div").length == 0){
                                    $("#zg_"+itemid+"_exist").hide();
                                    $("#zg_"+itemid+"_null").css("height","118px");
                                    $("#zg_"+itemid+"_null").css("line-height","118px");
                                    $("#zg_"+itemid+"_null").show();
                                    $("#hid_"+itemid).val(0);
                                    getresumeintegrity();
                                }
                            }else{
                                //删除作品展示
                                var childname = $(self).attr("data-child");
                                if(childname == "upload"){
                                    self.parent().parent().parent().remove();
                                }else{
                                    self.parent().parent().remove();
                                }
                                
                                if($("#"+childname+"content").children("div").length == 0){
                                    $("#"+childname+"content").hide();
                                }
                                if($("#uploadcontent").children("div").length == 0 && $("#onlinecontent").children("div").length == 0){
                                    $("#zg_"+itemid+"_exist").hide();
                                    $("#zg_"+itemid+"_null").css("height","118px");
                                    $("#zg_"+itemid+"_null").css("line-height","118px");
                                    $("#zg_"+itemid+"_null").show();
                                }
                            }
                        }else{//附件上传
                            $("#zg_" + item + "_exist").hide();
                            $("#zg_" + item + "_empty").show();
                        }
                    } else {
                        $.qglobal.tip({content: result.msg, icon: 'error'}, 5000);
                    }
                });
            },
            cancel: function() { }
        });
    });
    
    //简历预览按钮
    $("#btnpre").live("click",function(){
        rid = $("#rid").val();
        if(rid == ""){
           $.qglobal.tip({content: "请先填写简历基本信息", icon: 'error'}, 5000);
           return false;
        }
        $(this).attr("href",SLPGER.root+"resume/preview/rid/"+rid);
    });
    
    //保存基本信息
    $("#btnsavebasic").live("click",function(){
        basicvalidate();
    });
    
    //保存求职意向
    $("#btnsaveexp").live("click",function(){
        expvalidate();
    });
    
    //保存教育经历
    $("#btnsaveedu").live("click",function(){
        eduvalidate();
    });
    
    //保存获奖经历
    $("#btnsavewin").live("click",function(){
        winvalidate();
    });
    
    //保存社团活动
    $("#btnsaveactive").live("click",function(){
        activevalidate();
    });
    
    //保存工作经历
    $("#btnsavework").live("click",function(){
        workvalidate();
    });
    
    //保存特长兴趣
    $("#btnsaveinterest").live("click",function(){
        interestvalidate();
    });
    
    //保存证书及技能
    $("#btnsaveskill").live("click",function(){
        skillvalidate();
    });
    
    //保存自我评价
    $("#btnsaveeval").live("click",function(){
        evalvalidate();
    });
    
    //保存作品展示--图片
    $("#btnsavehandworkupload").live("click",function(){
        handworkuploadvalidate();
    });
    
    //保存作品展示--在线作品
    $("#btnsavehandworkonline").live("click",function(){
        handworkonlinevalidate();
    });                   
});

//页面加载
function init(){
    showempty("basic"),shownull("exp"),shownull("edu"),shownull("win"),shownull("active"),shownull("work"),shownull("interest"),shownull("skill"),shownull("eval");
}

//使用模板
function usetemplates(itemid,content){
    if($("#hid_"+itemid).val()==0){
        $("#u_"+itemid).text(content);
        $("#e_"+itemid).text(content);
        $("#zg_"+itemid+"_exist .zg_t_ms").css("color","#999");
        $("#font_"+itemid).show();
        showexist(itemid);
    }else{
        $("#zg_"+itemid+"_exist .zg_t_ms").css("color","#333");
    }
}

//取消使用模板
function cancelusingtemplate(itemid){
    shownull(itemid);
    $("#u_"+itemid).text("");
    $("#e_"+itemid).text("");
    $(".zg_tx_sx").show();
    getresumeintegrity();
}

//取消按钮
function cancel(itemid) {
    isEdit = true;
    switch(itemid){
        case "basic":
            if (basicEdit === false) {
                clearinput(itemid);
            } else {
                showexist(itemid);
            }
            break;
       case "exp":
            if (expEdit === true) {
                showexist(itemid);
            } else {
                shownull(itemid);
            }
           break;
           case "skill":
           case "win":
           case "interest":
           case "eval":
               showexist(itemid);
               break;
           default:
               clearinput(itemid);
               showexist(itemid);
               break;
    }
}

//显示添加内容页面
function showempty(itemid){
    $("#zg_"+itemid+"_empty").show();
    $("#zg_"+itemid+"_exist").hide();
    $("#zg_"+itemid+"_null").hide();
}

//显示已存在内容页面
function showexist(itemid){
    $("#zg_"+itemid+"_empty").hide();
    $("span.error").hide();
    switch(itemid){
        case "edu":
        case "active":
        case "work":
            eid = wid = raid = '';
            $("#"+itemid+"Form").insertAfter($("#zg_"+itemid+"_exist"));
            if($("#zg_"+itemid+"_exist").children("div").length > 0){
                $("#zg_"+itemid+"_exist").show();
                $("#zg_"+itemid+"_exist").children("div").show();
                $("#zg_"+itemid+"_null").css("height","58px");
                $("#zg_"+itemid+"_null").css("line-height","58px");
                $("#zg_"+itemid+"_null").show();
                $("#zg_"+itemid+"_exist").children("div:last-child").css("border-bottom","none");
            }else{
                $("#zg_"+itemid+"_null").css("height","118px");
                $("#zg_"+itemid+"_null").css("line-height","118px");
                $("#zg_"+itemid+"_null").show();
                $("#zg_"+itemid+"_exist").hide();
            }
            break;
        case "win":
        case "interest":
        case "skill":
        case "eval":
            $("#f_"+itemid).show();
            if($("#zg_"+itemid+"_exist .zg_txa pre").text() == ""){
                $("#f_"+itemid).text("添加");
                $("#zg_"+itemid+"_null").show();
                $("#zg_"+itemid+"_exist").hide();
            }else{
                $("#f_"+itemid).text("编辑");
                $("#zg_"+itemid+"_null").hide();
                $("#zg_"+itemid+"_exist").show();
            }
            break;
        case "basic":
        case "exp":
            $("#f_"+itemid).show();
            $("#f_"+itemid).text("编辑");
            $("#zg_"+itemid+"_exist").show();
            $("#zg_"+itemid+"_null").hide();
            break;
        case "handwork":
            $(".handwork_tab").children("span:eq(1)").addClass("uponline");
            if($("#zg_"+itemid+"_exist").children("div:eq(0)").children("div").length==0 && $("#zg_"+itemid+"_exist").children("div:eq(1)").children("div").length ==0 ){
                $("#zg_"+itemid+"_null").show();
                $("#zg_"+itemid+"_exist").hide();
            }else{
                $("#zg_"+itemid+"_null").css("height","58px");
                $("#zg_"+itemid+"_null").css("line-height","58px");
                $("#zg_"+itemid+"_null").show();
                $("#zg_"+itemid+"_exist").show();
                $("#hwpicupload").attr("src","");
                $("#btnuploadstyle").css("background","url("+SLPGER.root+"public/images/oFilePicCom.jpg"+") center center");
                $("#pictitle").val("");            
                $("#u_picdes").val("");$("#o_picdes").val("");
                $("#divupload").prev("div").show();
                $("#onlinecontent").show();
                $("#uploadcontent").show();
                if($("#onlineForm").parent().attr("id") == "onlinecontent"){
                    $("#onlineForm").prev("div").show();
                    $("#onlineForm").insertAfter("#divupload").hide();
                }else{
                    $("#divupload").insertAfter("#onlineForm");
                    $("#onlineForm").insertAfter("#divupload").hide();
                }
            }
            break;
    }
}

//显示null
function shownull(itemid){
    $("#f_"+itemid).text("添加");
    $("#zg_"+itemid+"_exist").hide();
    $("#zg_"+itemid+"_empty").hide();
    $("#zg_"+itemid+"_null").show();
}

//成功保存基本信息后
function regain(itemid,haveitem){
    isEdit = true;//更新编辑状态
    $("#font_"+itemid).hide();
    $("#zg_"+itemid+"_exist .zg_t_ms").css("color","#333");
    switch(itemid){
        case "baisc":
            basicEdit = true;
            break;
        case "exp":
            expEdit = true;
            break;
        case "edu":
            $("#zg_edu_exist .zg_t_kc").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dt").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dd").css("color","#333");
            break;
    }
    if(haveitem==0){
        $("#hid_"+itemid).val(0);
        shownull(itemid);
    }else{
        if(itemid!="basic"){
            $("#hid_"+itemid).val(1);
        }
        showexist(itemid);
    }
    $("#zg_"+itemid+"_exist").children("div").css("border-bottom","1px dashed #e6e6e6");
    $("#zg_"+itemid+"_exist").children("div:last-child").css("border-bottom","none");
    getresumeintegrity();
}

//清空内容
function clearinput(itemid){
    if(itemid!="handwork"){
        $("#zg_"+itemid+"_empty input[type='text']:not(:disabled)").val('');
        $("#zg_"+itemid+"_empty textarea").val('');
        $("#zg_"+itemid+"_empty .select_box span").text('');
        $("#zg_"+itemid+"_empty input[type='hidden']").val('');
        if(itemid == "basic"){//清空已上传的头像
            $("#myupload #upimg img").attr("src",SLPGER.root+"/public/images/tx.jpg");
            $("#hid_imgid").val(0);
        }
    }else{
        $("#onlineForm input[type='text']").val('');
        $("#onlineForm .presitelink").text("www.example.com");
        $("#onlineForm .predes").text("这里是你的作品描述");
        $("#onlineForm textarea").val('');
    }
    $("span.error").hide();
    $(".clear").click();
   
}

//基本信息验证
function basicvalidate(){
    $("#basicinfoForm").validate({
        rules: {
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
            u_name: {
                required: true
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
            u_name:{
                required:"必填"
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
            var name = $("#u_name").val(),phone = $("#u_phone").val(),email = $("#u_email").val(),birth = $("#u_birth").val(),gender = $('input[name="u_gender"]:checked ').val(),logoid = $("#hid_imgid").val(),
            edu = $("#hid_basic_edu").val(),addr = $("#u_addr").val(),workexp = $("#hid_basic_workexp").val(),edu_name = $("#hid_basic_eduname").text(),exp_name = $("#hid_basic_workexpname").text();
            $(form).find(":submit").attr("disabled", true);
            var data = {name: name,phone:phone,email:email,birth:birth,gender:gender,logoid:logoid,education:edu,addr:addr,experience:workexp,edu_name:edu_name,exp_name:exp_name};
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
                    $("#e_birth").html(result.data.birth);
                    $("#e_gender").html((result.data.gender==0) ? "男" : "女");
                    $("#e_education").html(result.data.edu_name);
                    $("#e_addr").html(result.data.address);
                    $("#e_workexp").html(result.data.exp_name);
                    $("#e_phone").html(result.data.phone);
                    $("#e_email").html(result.data.email);
                    basicEdit = true;
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
function expvalidate(){
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
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var expjobid = $("#expjobid").val(),expjobname = $("#select_expjob").val(),expcity = $("#expcity").val(),expcityname = $("#select_city").val(),
                    expsalary = $("#expsalary").val(),expsalaryname = $("#select_salary").text(),expjobnature = $("#expjobnature").val(),expjobnaturename = $("#select_jobnature").text(),remarks = $("#remarks").val();
            $(form).find(":submit").attr("disabled", true);
            data = {rid:rid,jobid:expjobid,jobname:expjobname,city:expcity,cityname:expcityname,salary:expsalary,salaryname:expsalaryname,jobnature:expjobnature,jobnaturename:expjobnaturename,remarks:remarks};
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
                    expEdit = true;
                    regain("exp");
                    if(typeof(jtid)!="undefined"){
                        jtid = (jtid != -1) ? jtid : 0;
                        window.location.href = SLPGER.root + "resume/online/rid/"+rid+"/jtid/"+jtid;
                    }
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false);
            });
        }
    });
}

//教育经历验证
function eduvalidate(){
    $("#eduForm").validate({
        rules: {
            school : {
                required : true
            },
            professional:{
                required : true
            },
            edu_education:{
                required : true
            },
            startdate:{
                required : true
            },
            enddate :{
                required : true
            },
            majorcourse:{
                required : true
            }
        },
        messages: {
            school : {
                required :"必填"
            },
            professional:{
                required : "必填"
            },
            edu_education:{
                required : "必选"
            },
            startdate:{
                required : "必填"
            },
            enddate :{
                required : "必填"
            },
            majorcourse:{
                required : "必填"
            }
        },
        submitHandler: function(form) {
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var eduschool = $("#zg_edu_empty input[name='school']").val(),eduprofessional = $("#zg_edu_empty input[name='professional']").val(),eduid = $("#hid_edu_edu").val(),education = $("#hid_edu_eduname").text(),eduentrance = $("#zg_edu_empty input[name='entrance']:checked").val(),
                    edustartdate = $("#zg_edu_empty input[name='startdate']").val(),eduenddate = $("#zg_edu_empty input[name='enddate']").val(),edumajorcourse = $("#zg_edu_empty input[name='majorcourse']").val(),eduminorcourse = $("#zg_edu_empty input[name='minorcourse']").val();
            eduentrance = (eduentrance=="on") ? 1 : 0;
            var data = {rid:rid,school:eduschool,professional:eduprofessional,eduid:eduid,education:education,entrance:eduentrance,startdate:edustartdate,enddate:eduenddate,majorcourse:edumajorcourse,minorcourse:eduminorcourse};    
            if(eid!="" && isNewEdu === false){
                data.eid = eid;
            }
            jtid = $("#hid_mod_id").val();
            if(jtid!=""){
                data.jtid = jtid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveeduinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    //保存教育经历信息成功
                    var listhtml = "",previd = "";
                    var isexist = false;
                    if($("#edulist_"+result.data.eid).html()!="" && typeof($("#edulist_"+result.data.eid).html())!="undefined"){
                        isexist = true;
                    }
                    listhtml += '<div class="zg_t_s">';
                    listhtml += '<p class="zg_t_cz"><font class="bianji" onclick="editedu('+result.data.eid+')">编辑</font><font class="F_comfirmdialog" data-uri="'+SLPGER.root+'resume/delresumeedu/eid/'+result.data.eid+'" data-msg="确定要删除此教育经历吗？" data-id="'+result.data.eid+'" data-item="edu" style="color: #ff712d">删除</font></p>';
                    listhtml += '<span id="e_school_'+result.data.eid+'">'+result.data.school+'</span>（<span id="e_startdate_'+result.data.eid+'" style="margin-right:0px;">'+result.data.startdate+'</span>-<span id="e_enddate_'+result.data.eid+'" style="margin-right:0px;">'+result.data.enddate+'</span>）';
                    listhtml += '<span>专业名称：</span><span id="e_prof_'+result.data.eid+'">'+result.data.professional+'</span>';
                    listhtml += '<span>学历：</span><span id="e_edu_'+result.data.eid+'" data-id="'+result.data.eduid+'">'+result.data.education+'</span></div>';
                    listhtml += '<dl class="zg_t_kc"><dt>主修课程：</dt>';
                    listhtml += '<dd id="e_major_'+result.data.eid+'">'+result.data.majorcourse+'</dd>';
                    listhtml += '<dt>辅修课程：</dt><dd id="e_minor_'+result.data.eid+'">'+result.data.minorcourse+'</dd></dl></div>';
                    if(isexist){
                        $("#edulist_"+result.data.eid).html(listhtml);
                    }else{
                        if($("#zg_edu_exist").children("div").length <= 0){
                            listhtml = '<div class="zg_t_a" id="edulist_'+result.data.eid+'">' + listhtml + '</div>';
                            $("#zg_edu_exist").html(listhtml);
                        }else{
                            listhtml = $("#zg_edu_exist").html() + '<div class="zg_t_a" id="edulist_'+result.data.eid+'">' + listhtml + '</div>';
                            $("#zg_edu_exist").html(listhtml);                   
                        }
                    }
                    regain("edu");
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
            });
        }
    });
}

//获奖经历信息验证
function winvalidate(){
    $("#wininfoForm").validate({
        submitHandler : function(form){
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var winexp = $("#u_win").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,type:"win",winexp:winexp}; 
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveintroinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    $("#e_win").html(result.data.content);
                    if(result.data.content == ""){
                        regain("win",0);
                    }else{
                        winEdit = true;
                        regain("win");
                    }
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//在校经历信息验证
function activevalidate(){
    $("#activeForm").validate({
        rules: {
            active_jobname : {
                required : true
            },
            active_jobdepartment:{
                required : true
            },
            active_workstart:{
                required : true
            },
            active_workend:{
                required : true
            },
            active_workperformance :{
                required : true
            }
        },
        messages: {
            active_jobname : {
                required : "必填"
            },
            active_jobdepartment:{
                required : "必填"
            },
            active_workstart:{
                required : "必填"
            },
            active_workend:{
                required : "必填"
            },
            active_workperformance :{
                required : "必填"
            }
        },
        submitHandler: function(form) {
            rid = $("#rid").val();
            if(rid==""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
                return false;
            }
            var jobname = $("#zg_active_empty input[name='active_jobname']").val(),jobdepartment = $("#zg_active_empty input[name='active_jobdepartment']").val(),
                        workstart = $("#zg_active_empty input[name='active_workstart']").val(),workend = $("#zg_active_empty input[name='active_workend']").val(),workperformance = $("#active_workperformance").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,jobname:jobname,jobdepartment:jobdepartment,workstart:workstart,workend:workend,workperformance:workperformance};    
            if(raid!="" && isNewActive === false){
                data.raid = raid;
            }
            jtid = $("#hid_mod_id").val();
            if(jtid!=""){
                data.jtid = jtid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveactiveinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    //保存在校经历信息成功
                    var listhtml = "";
                    var previd = "";
                    var isexist = false;
                    if($("#activelist_"+result.data.raid).html()!="" && typeof($("#activelist_"+result.data.raid).html())!="undefined"){
                        isexist = true;
                    }
                    listhtml += '<div class="zg_t_a" id="activelist_'+result.data.raid+'">';
                    listhtml += '<div class="zg_t_s">';
                    listhtml += '<p class="zg_t_cz"><font class="bianji" onclick="editactive('+result.data.raid+')">编辑</font><font class="F_comfirmdialog" data-uri="'+SLPGER.root+'/resume/delresumeactive/raid/'+result.data.raid+'" data-msg="确定要删除此在校经历吗？" data-id="'+result.data.raid+'" data-item="active" style="color: #ff712d;">删除</font></p>';
                    listhtml += '<span id="e_department_'+result.data.raid+'">'+result.data.jobdepartment+'</span>（<span id="e_startdate_'+result.data.raid+'" style="margin-right:0px;">'+result.data.startdate+'</span>-<span id="e_enddate_'+result.data.raid+'" style="margin-right:0px;">'+result.data.enddate+'</span>）<span class="zg_zhiwu" id="e_jobname_'+result.data.raid+'">'+result.data.jobname+'</span></div>';
                    listhtml += '<pre class="zg_t_ms" style="color:#999;" id="e_workperformance_'+result.data.raid+'">'+result.data.workperformance+'</pre>';
                    if(isexist){
                        $("#activelist_"+result.data.raid).replaceWith(listhtml);
                    }else{
                        $(listhtml).prependTo("#zg_active_exist");
                    }
                    if($("#activeForm").prev("div").css("display") == "none"){
                        previd = $("#activeForm").prev("div").attr("id");
                        if (previd != "zg_active_exist") {
                            $("#activeForm").prev("div").remove();
                        }
                    }
                    regain("active");
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//工作经历信息验证
function workvalidate(){
    $("#workForm").validate({
        rules: {
            work_companyname : {
                required : true
            },
            work_companyaddr:{
                required : true
            },
            work_companyjob:{
                required : true
            },
            work_subordinate:{
                required : true
            },
            workindustry :{
                required : true
            },
            work_start :{
                required : true
            },
            work_end:{
                required : true
            },
            work_jobduties :{
                required : true
            }
        },
        messages: {
            work_companyname : {
                required : "必填"
            },
            work_companyaddr:{
                required : "必填"
            },
            work_companyjob:{
                required : "必填"
            },
            work_subordinate:{
                required : "必填"
            },
            workindustry :{
                required : "必选"
            },
            work_start :{
                required : "必填"
            },
            work_end:{
                required : "必填"
            },
            work_jobduties :{
                required : "必填"
            }
        },
        submitHandler: function(form) {
            rid = $("#rid").val();
            var company = $("#zg_work_empty input[name='work_companyname']").val(),addr = $("#zg_work_empty input[name='work_companyaddr']").val(),
                        companyjob = $("#zg_work_empty input[name='work_companyjob']").val(),subordinate = $("#zg_work_empty input[name='work_subordinate']").val(),
                        industry = $("#hid_workindustry").val(),industryname = $("#work_industry").text(),
                        workstart = $("#zg_work_empty input[name='work_start']").val(),workend = $("#zg_work_empty input[name='work_end']").val(),jobduties = $("#work_jobduties").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,company:company,addr:addr,companyjob:companyjob,subordinate:subordinate,industry:industry,industryname:industryname,workstart:workstart,workend:workend,jobduties:jobduties};    
            if(wid!="" && isNewWork === false){
                data.wid = wid;
            }
            jtid = $("#hid_mod_id").val();
            if(jtid!=""){
                data.jtid = jtid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveworkinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    //保存工作经历信息成功
                    var listhtml = "",previd="";
                    var isexist = false;
                    if($("#worklist_"+result.data.wid).html()!="" && typeof($("#worklist_"+result.data.wid).html())!="undefined"){
                        isexist = true;
                    }
                    listhtml += '<div class="zg_t_a" id="worklist_'+result.data.wid+'">';
                    listhtml += '<div class="zg_t_s">';
                    listhtml += '<p class="zg_t_cz"><font class="bianji" onclick="editwork('+result.data.wid+')">编辑</font><font class="F_comfirmdialog" data-uri="'+SLPGER.root+'resume/delresumework/wid/'+result.data.wid+'" data-msg="确定要删除此工作经历吗？" data-id="'+result.data.wid+'" data-item="work" style="color: #ff712d;">删除</font></p>';
                    listhtml += '<span id="e_company_'+result.data.wid+'">'+result.data.company+'</span><span id="e_companyjob_'+result.data.wid+'">'+result.data.companyjob+'</span>（<span id="e_startdate_'+result.data.wid+'" style="margin-right:0px;">'+result.data.startdate+'</span>-<span id="e_enddate_'+result.data.wid+'" style="margin-right:0px;">'+result.data.enddate+'</span>）</div>';
                    listhtml += '<div id="zg_workspan" class="zg_t_s"><span id="e_addr_'+result.data.wid+'" class="zg_diqu">'+result.data.addr+'</span>';
                    listhtml += '<span id="e_industry_'+result.data.wid+'" class="zg_leixing" data-id="'+result.data.induid+'">'+result.data.industry+'</span><span class="zg_zhiwu">下属人数：</span><span id="e_subordinate_'+result.data.wid+'">'+result.data.subordinate+'</span><span>人</span></div>';
                    listhtml += '<pre class="zg_t_ms" style="color:#999;" id="e_jobduties_'+result.data.wid+'">'+result.data.jobduties+'</pre></div>';
                    if(isexist){
                        $("#worklist_"+result.data.wid).replaceWith(listhtml);
                    }else{
                        $(listhtml).prependTo("#zg_work_exist");
                    }
                    if($("#workForm").prev("div").css("display") == "none"){
                        previd = $("#workForm").prev("div").attr("id");
                        if(previd!="zg_work_exist"){
                            $("#workForm").prev("div").remove();
                        }
                    }
                    regain("work");
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//特长兴趣信息验证
function interestvalidate(){
    $("#interestForm").validate({
        submitHandler : function(form){
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var interest = $("#u_interest").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,type:"interest",interest:interest};  
            riid = $("#hid_win_riid").val();
            if(riid!=""){
                data.riid = riid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveintroinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    $("#hid_win_riid").val(result.data.riid);
                    $("#e_interest").html(result.data.content);
                    if(result.data.content == ""){
                        regain("interest",0);
                    }else{
                        regain("interest");
                    }
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//证书技能信息验证
function skillvalidate(){
    $("#skillForm").validate({
        submitHandler : function(form){
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var jobskill = $("#u_skill").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,type:"skill",jobskill:jobskill};
            riid = $("#hid_win_riid").val();
            if(riid!=""){
                data.riid = riid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveintroinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    $("#hid_win_riid").val(result.data.riid);
                    $("#e_skill").html(result.data.content);
                    if(result.data.content == ""){
                        regain("skill",0);
                    }else{
                        regain("skill");
                    }
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//自我评价信息验证
function evalvalidate(){
    $("#evalForm").validate({
        submitHandler : function(form){
            rid = $("#rid").val();
            if(rid == ""){
                $.qglobal.tip({content:"请先填写简历基本信息", icon:'error' ,time:2000});
            }
            var evaluation = $("#u_eval").val();
            $(form).find(":submit").attr("disabled", true);
            var data = {rid:rid,type:"eval",evaluation:evaluation};    
            riid = $("#hid_win_riid").val();
            if(riid!=""){
                data.riid = riid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url:"/resume/saveintroinfo",
                datatype:"json"
            }).done(function(result){
                if(1 === result.status){
                    $("#hid_win_riid").val(result.data.riid);
                    $("#e_eval").html(result.data.content);
                    if(result.data.content == ""){
                        regain("eval",0);
                    }else{
                        regain("eval");
                    }
                }else{
                     $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
                $(form).find(":submit").attr("disabled", false)
            });
        }
    });
}

//作品展示--图片验证
function handworkuploadvalidate() {
    $("#uploadForm").validate({
        rules: {
            handworkpic: {
                required: true
            }
        }, messages: {
            handworkpic: {
                required: "请选择要上传的图片"
            }
        }, submitHandler: function(form) {
            var pic = $("#handworkpic").val(), pictitle = $("#pictitle").val(), picdes = $("#u_picdes").val();
            var data = {pic: pic};
            if (pictitle != "") {
                data.pictitle = pictitle;
            }
            if (picdes != "") {
                data.picdes = picdes;
            }
            rid = $("#rid").val();
            if (rid == "") {
                $.qglobal.tip({content: "请先填写简历基本信息", icon: 'error', time: 2000});
                return false;
            }
            data.rid = rid;
            rdid = $("#hidrdid").val();
            if(rdid!=""){
                data.rdid = rdid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url: "/resume/savehandwork/type/1",
                datatype: "json"
            }).done(function(result) {
                if(result.status != 1){
                    $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
                    return false;
                }
                //保存图片作品成功
                var listhtml = "";
                var previd = "";
                var isexist = false;
                if($("#uploadlist_"+result.data.rdid).html()!="" && typeof($("#uploadlist_"+result.data.rdid).html())!="undefined"){
                    isexist = true;
                }
                listhtml += '<div class="uploadshow" id="uploadlist_'+result.data.rdid+'" data-id="'+result.data.rdid+'">';
                listhtml += '<a href="javascript:viod(0);"><img class="wh43" src="'+$("#hwpicupload").attr("src")+'" alt="'+result.data.w_title+'" data-id="'+result.data.w_image_id+'"></a>';
                listhtml += '<div class="upcon"><p class="zg_t_cz"><font class="bianji" onclick="edithandwork(this)" data-id="'+result.data.rdid+'" data-type="upload">编辑</font>';
                listhtml += '<font class="F_comfirmdialog" data-uri="'+SLPGER.root+'resume/delresumehandwork/rdid/'+result.data.rdid+'" data-msg="确定要删除此作品吗？" data-id="'+result.data.rdid+'" data-item="handwork" data-child="upload" style="color: #ff712d;">删除</font></p>';
                listhtml += '<div class="contitle ">'+result.data.w_title+'</div><div class="condesc">';    
                listhtml += '<pre>'+result.data.w_intro+'</pre></div></div></div>';
                if(isexist){
                    $("#uploadlist_"+result.data.rdid).replaceWith(listhtml);
                }else{
                    $(listhtml).appendTo("#uploadcontent");
                }
                $("#handworkpic").val(result.data.w_image_id);
                $("#divupload").insertAfter($("#onlineForm"));
                $("#hwpicupload").attr("src","");
                $("#pictitle").val("");
                $("#u_picdes").val("");
                regain("handwork");
            });
        }
    });
}

//作品展示--在线作品
function handworkonlinevalidate() {
    $("#onlineForm").validate({
        rules: {
            handworkurl: {
                required: true,
                url: true
            }
        }, messages: {
            handworkurl: {
                required: "请输入在线地址",
                url: "请输入正确的链接地址,如：www.example.com"
            }
        }, submitHandler: function(form) {
            var handworkurl = $("#handworkurl").val(), picdes = $("#o_picdes").val();
            var data = {handworkurl: handworkurl};
            if (picdes != "") {
                data.picdes = picdes;
            }
            rid = $("#rid").val();
            if (rid == "") {
                $.qglobal.tip({content: "请先填写简历基本信息", icon: 'error', time: 2000});
                return false;
            }
            data.rid = rid;
            rdid = $("#hidrdid").val();
            if(rdid!=""){
                data.rdid = rdid;
            }
            $.ajax({
                type: "POST",
                data: data,
                url: "/resume/savehandwork/type/2",
                datatype: "json"
            }).done(function(result) {
                if(result.status != 1){
                    $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
                    return false;
                }
                //保存在线作品成功
                var listhtml = "";
                var previd = "";
                var isexist = false;
                if($("#onlinelist_"+result.data.rdid).html()!="" && typeof($("#onlinelist_"+result.data.rdid).html())!="undefined"){
                    isexist = true;
                }
                listhtml += '<div class="presite" id="onlinelist_'+result.data.rdid+'" data-id="'+result.data.rdid+'">';
                listhtml += '<p class="zg_t_cz" style="margin-top:5px;width:100%; text-align:right;">';
                listhtml += '<font class="bianji" style="color:#fff" onclick="edithandwork(this)" data-id="'+result.data.rdid+'" data-type="online">编辑</font>';
                listhtml += '<font class="F_comfirmdialog" data-uri="'+SLPGER.root+'resume/delresumehandwork/rdid/'+result.data.rdid+'" data-msg="确定要删除此作品吗？" data-id="'+result.data.rdid+'" data-item="handwork" data-child="online" style="color: #ff712d;">删除</font>';
                listhtml += '</p><div class="onlinepre"><a class="presitelink" href="'+result.data.w_url+'">'+result.data.w_url+'</a>';    
                listhtml += '<pre>'+result.data.w_intro+'</pre></div></div>';
                if(isexist){
                    $("#onlinelist_"+result.data.rdid).replaceWith(listhtml);
                }else{
                    $(listhtml).prependTo("#onlinecontent");
                }
                $("#onlineForm").insertAfter($("#divupload"));
                $("#onlineForm .presitelink").text("www.example.com");
                $("#onlineForm .predes p").text("这里是你的作品描述");
                $("#handworkurl").val("");
                $("#o_picdes").val("");
                regain("handwork");
            });
        }
    });
}

//修改教育经历
function editedu(id){
    $("#zg_edu_null").hide();
    if(expEdit === false){
        $.qglobal.tip({content:"您还没有填写求职意向信息", icon:'error' ,time:2000});
        return false;
    }
    if(isEdit === false){
        $.qglobal.tip({content:"您还有未保存信息，请先保存后再操作", icon:'error' ,time:2000});
        return false;
    }
    isEdit = false;
    if(typeof(id)=="object"){
        var schoolname = $(id).parent().siblings("span:eq(0)").text();
        var startdate = $(id).parent().siblings("span:eq(1)").text();
        var enddate = $(id).parent().siblings("span:eq(2)").text();
        var profname = $(id).parent().siblings("span:eq(4)").text();
        var mjcourse = $(id).parent().parent().next("dl").children("dd:eq(0)").text();
        var micourse = $(id).parent().parent().next("dl").children("dd:eq(1)").text();
        $("#zg_edu_empty input[name='school']").val(schoolname);
        $("#zg_edu_empty input[name='professional']").val(profname);
        $("#zg_edu_empty input[name='startdate']").val(startdate);
        $("#zg_edu_empty input[name='enddate']").val(enddate);
        $("#zg_edu_empty input[name='majorcourse']").val(mjcourse);
        $("#zg_edu_empty input[name='minorcourse']").val(micourse);
        $(id).parent().parent().parent().hide();
        $("#eduForm").insertAfter($(id).parent().parent().parent()).children().show();
        $("#f_edu").text("取消");
    }else{
        eid = id;
        isNewEdu = false;
        var school = $("#e_school_"+id).html(),prof = $("#e_prof_"+id).html(),education = $("#e_edu_"+id).html(),eduid=$("#e_edu_"+id).attr("data-id"),entrance = $("#e_entrance_"+id).html(),
                startdate = $("#e_startdate_"+id).html(),enddate = $("#e_enddate_"+id).html(),major = $("#e_major_"+id).html(),minor = $("#e_minor_"+id).html();
        $("#zg_edu_empty input[name='school']").val(school);
        $("#zg_edu_empty input[name='professional']").val(prof);
        $("#hid_edu_eduname").text(education);
        if(entrance == "1"){
            $("#zg_edu_empty input[name='entrance']").attr("checked",'true');
        }
        $("#zg_edu_empty input[name='startdate']").val(startdate);
        $("#zg_edu_empty input[name='enddate']").val(enddate);
        $("#zg_edu_empty input[name='majorcourse']").val(major);
        $("#zg_edu_empty input[name='minorcourse']").val(minor);
        $("#hid_edu_edu").val(eduid);
        $("#edulist_"+id).hide();
        $("#eduForm").insertAfter($("#edulist_"+id)).children().show();
        $("#f_edu").text("取消");
    }
}

//修改在校经历
function editactive(id){
    $("#zg_active_null").hide();
    if(expEdit === false){
        $.qglobal.tip({content:"您还没有填写求职意向信息", icon:'error' ,time:2000});
        return false;
    }
    if(isEdit === false){
        $.qglobal.tip({content:"您还有未保存信息，请先保存后再操作", icon:'error' ,time:2000});
        return false;
    }
    isEdit = false;
    if(typeof(id)=="object"){
        var jobdepartment = $(id).parent().siblings("span:eq(0)").text();
        var activestart = $(id).parent().siblings("span:eq(1)").text();
        var activeend = $(id).parent().siblings("span:eq(2)").text();
        var jobname = $(id).parent().siblings("span:eq(3)").text();
        var jobduty = $(id).parent().parent().next("p").text();
        $("#zg_active_empty input[name='active_jobdepartment']").val(jobdepartment);
        $("#zg_active_empty input[name='active_jobname']").val(jobname);
        $("#zg_active_empty input[name='active_workstart']").val(activestart);
        $("#zg_active_empty input[name='active_workend']").val(activeend);
        $("#zg_active_empty #active_workperformance").val(jobduty);
        $(id).parent().parent().parent().hide();
        $("#activeForm").insertAfter($(id).parent().parent().parent()).children().show();
        $("#f_active").text("取消");
    }else{
        raid = id;
        isNewActive = false;
        var jobname = $("#zg_active_exist #e_jobname_"+id).html(),jobdepartment = $("#zg_active_exist #e_department_"+id).html(),
                startdate = $("#zg_active_exist #e_startdate_"+id).html(),enddate = $("#zg_active_exist #e_enddate_"+id).html(),workperformance = $("#zg_active_exist #e_workperformance_"+id).html();
        $("#zg_active_empty input[name='active_jobname']").val(jobname);
        $("#zg_active_empty input[name='active_jobdepartment']").val(jobdepartment);
        $("#zg_active_empty input[name='active_workstart']").val(startdate);
        $("#zg_active_empty input[name='active_workend']").val(enddate);
        $("#active_workperformance").val(workperformance);
        $("#activelist_"+id).hide();
        $("#activeForm").insertAfter($("#activelist_"+id)).children().show();
        $("#hid_raid").val(id);
        $("#f_active").text("取消");
    }
}

//修改工作经历
function editwork(id){
    $("#zg_work_null").hide();
    if(expEdit === false){
        $.qglobal.tip({content:"您还没有填写求职意向信息", icon:'error' ,time:2000});
        return false;
    }
    if(isEdit === false){
        $.qglobal.tip({content:"您还有未保存信息，请先保存后再操作", icon:'error' ,time:2000});
        return false;
    }
    isEdit = false;
    if(typeof(id)=="object"){
        var companyname = $(id).parent().siblings("span:eq(0)").text();
        var jobname = $(id).parent().siblings("span:eq(1)").text();
        var wstartdate = $(id).parent().siblings("span:eq(2)").text();
        var wenddate = $(id).parent().siblings("span:eq(3)").text();
        var jobduty = $(id).parent().parent().next("p").text();
        $("#zg_work_empty input[name='work_companyname']").val(companyname);
        $("#zg_work_empty input[name='work_companyjob']").val(jobname);
        $("#zg_work_empty input[name='work_start']").val(wstartdate);
        $("#zg_work_empty input[name='work_end']").val(wenddate);
        $("#zg_work_empty #work_jobduties").val(jobduty);
        $(id).parent().parent().parent().hide();
        $("#workForm").insertAfter($(id).parent().parent().parent()).children().show();
        $("#f_work").text("取消");
    }else{
        wid = id;
        isNewWork = false;
        var company = $("#zg_work_exist #e_company_"+id).html(),companyjob = $("#zg_work_exist #e_companyjob_"+id).html(),
                startdate = $("#zg_work_exist #e_startdate_"+id).html(),enddate = $("#zg_work_exist #e_enddate_"+id).html(),
                addr = $("#zg_work_exist #e_addr_"+id).html(),industry = $("#zg_work_exist #e_industry_"+id).html(),industryid = $("#zg_work_exist #e_industry_"+id).attr("data-id"),
                subordinate = $("#zg_work_exist #e_subordinate_"+id).html(),jobduties = $("#zg_work_exist #e_jobduties_"+id).html();
        $("#zg_work_empty input[name='work_companyname']").val(company);
        $("#zg_work_empty input[name='work_companyjob']").val(companyjob);
        $("#zg_work_empty #work_industry").text(industry);
        $("#zg_work_empty #hid_workindustry").val(industryid);
        $("#zg_work_empty input[name='work_start']").val(startdate);
        $("#zg_work_empty input[name='work_end']").val(enddate);
        $("#zg_work_empty input[name='work_companyaddr']").val(addr);
        $("#zg_work_empty input[name='work_subordinate']").val(subordinate);
        $("#zg_work_empty #work_jobduties").val(jobduties);
        $("#worklist_"+id).hide();
        $("#workForm").insertAfter($("#worklist_"+id)).children().show();
        $("#f_work").text("取消");
    }
}

function edithandwork(obj){
    $("#zg_handwork_null").hide();
    if(expEdit === false){
        $.qglobal.tip({content:"您还没有填写求职意向信息", icon:'error' ,time:2000});
        return false;
    }
    if(isEdit === false){
        $.qglobal.tip({content:"您还有未保存信息，请先保存后再操作", icon:'error' ,time:2000});
        return false;
    }
    isEdit = false;
    rdid = $(obj).attr("data-id");
    var fromtype = $(obj).attr("data-type");
    $("#hidrdid").val(rdid);
    if(fromtype=="online"){
        var hwurl = $(obj).parent().next("div").children("a").text(),hwdes = $(obj).parent().next("div").children("pre").text();
        $("#onlineForm .presitelink").text(hwurl);
        $("#handworkurl").val(hwurl);
        $("#onlineForm .predes p").text(hwdes);
        $("#onlineForm #o_picdes").val(hwdes);
        $("#onlinelist_"+rdid).hide();
        $("#onlineForm").insertAfter($("#onlinelist_"+rdid)).show();
        $(".handwork_tab").children("span:eq(0)").removeClass("upimage");
    }else{
        var hwitem = $("#uploadlist_"+rdid);
        var hwimg = hwitem.children("a").children("img").attr("src"),hwtitle = hwitem.children(".upcon").children(".contitle").text(),
                hwdes = hwitem.children(".upcon").children(".condesc").children("pre").text(),hwimgid = hwitem.children("a").children("img").attr("data-id");
        hwtitle = hwtitle.replace("[","").replace("]","");
        $("#hwpicupload").attr("src",hwimg);
        $("#btnuploadstyle").css("background","url("+hwimg+")").css("background-size","cover");
        $("#uploadForm #pictitle").val(hwtitle);
        $("#uploadForm #u_picdes").val(hwdes);
        $("#uploadlist_"+rdid).hide();
        $("#handworkpic").val(hwimgid);
        $("#divupload").insertAfter($("#uploadlist_"+rdid)).next().show();
        $(".handwork_tab").children("span:eq(1)").removeClass("uponline");
    }
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

function initjobtemplate(){
    rid = $("#rid").val();
    //选择模板出现弹窗
    $('#select_template').bind('click',function(e){
        // e.stopPropagation();
        // $("#zgPhxg .zg_dq_xz").show();
        // $("#genghuan").show();
        e.stopPropagation();
        jtid =  $(this).attr("data-id");
        if(rid!=""){
            window.location.href = SLPGER.root + "resume/online/rid/"+rid+"/jtid/"+jtid;
        }else{
            window.location.href = SLPGER.root + "resume/online/jtid/"+jtid;
        }
    });
    $('.cityhot').on('mouseenter', '.cityhot li', function() {
        if ($(this).children('div').attr("data-id") < 100000) {
            $(this).children('div').show();
        }
    });
    $('.cityhot').on('mouseleave', '.cityhot li', function() {
        if ($(this).children('div').attr("data-id") < 100000) {
            $(this).children('div').hide();
        }
    });
    $('#zgPhxg .cityhot').on('click','.hotcZs a',function(e){
        e.stopPropagation();
        jtid =  $(this).attr("data-id");
        if(rid!=""){
            window.location.href = SLPGER.root + "resume/online/rid/"+rid+"/jtid/"+jtid;
        }else{
            window.location.href = SLPGER.root + "resume/online/jtid/"+jtid;
        }
    });
    $('.hotzhus a').on('click',function(e){
        e.stopPropagation();
        jtid =  $(this).attr("data-id");
        if(rid!=""){
            window.location.href = SLPGER.root + "resume/online/rid/"+rid+"/jtid/"+jtid;
        }else{
            window.location.href = SLPGER.root + "resume/online/jtid/"+jtid;
        }
    });
}

//图片上传
function headimgupload(){
    var uploadimgpath = "";
    rid = $("#rid").val();
    var data = { };
    if(rid != ""){
        data.rid = rid;
    }
    $("#myupload").ajaxSubmit({
        dataType: 'json',
        data:data,
        success: function(result) {
            if (1 === result.status) {
                //上传成功，显示裁剪页面
                $("#hid_imgid").val(result.data.imgid);
                uploadimgpath = SLPGER.root + result.data.path;
                selfcropzoom(uploadimgpath,"face");
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
function selfcropzoom(uploadimgpath,fromtype){
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
        var cutult = SLPGER.root + "resume/cutImg/fromtype/"+fromtype;
        cropzoom.send(cutult, 'POST', {}, function(imgRet) {
            if(fromtype == "face"){
                $("#logoimg").attr("src",SLPGER.root + imgRet.data.path);
            }else{
                var imgpath = SLPGER.root+imgRet.data.path;
                $("#btnuploadstyle").css("background","url("+imgpath+")").css("background-size","cover");
                $("#hwpicupload").attr("src",imgpath);
                $("#handworkpic").val(imgRet.data.imgid);
            }
            closebox();
        });
    });
}

//关闭图片裁剪窗口
function closebox(){
    $("#cboxOverlay").hide();
    $("#colorbox").hide();
    getresumeintegrity();
}

//保存基本信息提示
function submit_sure(){
    var gnl = confirm("基本信息将会应用于所有的简历，您确定要提交吗?");
    if (gnl == true) {
        return true;
    } else {
        return false;
    }
}

//模板编辑按钮
$(".bianji").live("click",function() {
    itemid = $(this).attr("data-item");
    switch(itemid){
        case "edu":
            editedu(this);
            break;
        case "work":
            editwork(this);
            break;
        case "active":
            editactive(this);
            break;
    }
});

//模板编辑按钮
$(".shanchu").live("click",function() {
    delitem(this);
});

//显示模板内容
function showtemplatecontent(){
    var data = {};
    data.tid = jtid;
    $.ajax({
        type: "POST",
        data: data,
        url: "/resume/gettemplate",
        datatype: "json"
    }).done(function(result) {
        if (0 === result.status) {
            $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            return false;
        }
        $(".zg_jl_xx span").text("已选择模板：");
        $(".zg_jl_xx #select_template").hide();
        $(".zg_jl_xx #modcancel").show();
        //特长兴趣
        usetemplates("interest", result.data.special_interest);
        //获奖经历
        usetemplates("win", result.data.honor_award);
        //证书技能
        usetemplates("skill", result.data.job_skill);
        //自我评价
        usetemplates("eval", result.data.evaluation);
        //教育及培训经历
        if($("#hid_edu").val()==0){//如果用户没有填写则使用模板内容，优先使用用户填写的内容
            var eduhtml = "";
            if (result.data.school_name != "") {
                eduhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="edu">编辑</font>'
                eduhtml += '<font class="shanchu" data-item="edu">删除</font></p><span>' + result.data.school_name + '</span>（<span style="margin-right:0px;">' + result.data.edu_start_year + '/' + result.data.edu_start_month + '</span>-';
                eduhtml += '<span style="margin-right:0px;">' + result.data.edu_end_year + '/' + result.data.edu_end_month + '</span>）';
                eduhtml += '<span>专业名称：</span><span>' + result.data.prof_name + '</span></div><dl class="zg_t_kc">';
                eduhtml += '<dt>主修课程：</dt><dd>' + result.data.major_courses + '</dd><dt>辅修课程：</dt><dd>' + result.data.minor_courses + '</dd></dl></div>';
            }
            if (result.data.school_name2 != "") {
                eduhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="edu">编辑</font>'
                eduhtml += '<font class="shanchu" data-item="edu">删除</font></p><span>' + result.data.school_name2 + '</span>（<span style="margin-right:0px;">' + result.data.edu_start_year2 + '/' + result.data.edu_start_month2 + '</span>-';
                eduhtml += '<span style="margin-right:0px;">' + result.data.edu_end_year2 + '/' + result.data.edu_end_month2 + '</span>）';
                eduhtml += '<span>专业名称：</span><span>' + result.data.prof_name2 + '</span></div><dl class="zg_t_kc">';
                eduhtml += '<dt>主修课程：</dt><dd>' + result.data.major_courses2 + '</dd><dt>辅修课程：</dt><dd>' + result.data.minor_courses2 + '</dd></dl></div>';
            }
            if (result.data.school_name3 != "") {
                eduhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="edu">编辑</font>'
                eduhtml += '<font class="shanchu" data-item="edu">删除</font></p><span>' + result.data.school_name3 + '</span>（<span style="margin-right:0px;">' + result.data.edu_start_year3 + '/' + result.data.edu_start_month3 + '</span>-';
                eduhtml += '<span style="margin-right:0px;">' + result.data.edu_end_year3 + '/' + result.data.edu_end_month3 + '</span>）';
                eduhtml += '<span>专业名称：</span><span>' + result.data.prof_name3 + '</span></div><dl class="zg_t_kc">';
                eduhtml += '<dt>主修课程：</dt><dd>' + result.data.major_courses3 + '</dd><dt>辅修课程：</dt><dd>' + result.data.minor_courses3 + '</dd></dl></div>';
            }
            $("#zg_edu_exist").html(eduhtml);
            $("#font_edu").show();
            showexist("edu");
        }else{
            $("#zg_edu_exist .zg_t_ms").css("color","#333");
            $("#zg_edu_exist .zg_t_kc").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dt").css("color","#333");
            $("#zg_edu_exist .zg_t_kc dd").css("color","#333");
            $("#font_edu").hide();
        }
        
        //社团活动
        if($("#hid_active").val()==0){
            var activehtml = "";
            if (result.data.job_name != "") {
                activehtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="active">编辑</font>';
                activehtml += '<font class="shanchu" data-item="active">删除</font></p><span>' + result.data.job_department + '</span>';
                activehtml += '（<span style="margin-right:0px;">' + result.data.work_start_year + '/' + result.data.work_start_month + '</span>';
                activehtml += '-<span style="margin-right:0px;">' + result.data.work_end_year + '/' + result.data.work_end_month + '</span>）';
                activehtml += '<span class="zg_zhiwu">' + result.data.job_name + '</span></div><p class="zg_t_ms" style="color:#999;">' + result.data.work_performance + '</p></div>';
            }
            if (result.data.job_name2 != "") {
                activehtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="active">编辑</font>';
                activehtml += '<font class="shanchu" data-item="active">删除</font></p><span>' + result.data.job_department2 + '</span>';
                activehtml += '（<span style="margin-right:0px;">' + result.data.work_start_year2 + '/' + result.data.work_start_month2 + '</span>';
                activehtml += '-<span style="margin-right:0px;">' + result.data.work_end_year2 + '/' + result.data.work_end_month2 + '</span>）';
                activehtml += '<span class="zg_zhiwu">' + result.data.job_name2 + '</span></div><p class="zg_t_ms" style="color:#999;">' + result.data.work_performance2 + '</p></div>';
            }
            if (result.data.job_name3 != "") {
                activehtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="active">编辑</font>';
                activehtml += '<font class="shanchu" data-item="active">删除</font></p><span>' + result.data.job_department3 + '</span>';
                activehtml += '（<span style="margin-right:0px;">' + result.data.work_start_year3 + '/' + result.data.work_start_month3 + '</span>';
                activehtml += '-<span style="margin-right:0px;">' + result.data.work_end_year3 + '/' + result.data.work_end_month3 + '</span>）';
                activehtml += '<span class="zg_zhiwu">' + result.data.job_name3 + '</span></div><p class="zg_t_ms" style="color:#999;">' + result.data.work_performance3 + '</p></div>';
            }
            $("#zg_active_exist").html(activehtml);
            showexist("active");
            $("#font_active").show();
        }else{
            $("#zg_active_exist .zg_t_ms").css("color","#333");
            $("#font_active").hide();
        }
        
        //工作经历
        if($("#hid_work").val()==0){
            var workhtml = "";
            if(result.data.company_name != ""){
                workhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="work">编辑</font>';
                workhtml += '<font class="shanchu" data-item="work">删除</font></p>';
                workhtml +='<span>'+result.data.company_name+'</span>&nbsp;—&nbsp;<span>'+result.data.company_job+'</span>';
                workhtml +='（<span style="margin-right:0px;">'+result.data.w_start_year+'/'+result.data.w_start_month+'</span>';
                workhtml +='-<span style="margin-right:0px;">'+result.data.w_end_year+'/'+result.data.w_end_month+'</span>）</div>';
                workhtml +='<p class="zg_t_ms" style="color:#999;">'+result.data.job_duties+'</p></div>';
            }
            if(result.data.company_name2 != ""){
                workhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="work">编辑</font>';
                workhtml += '<font class="shanchu" data-item="work">删除</font></p>';
                workhtml +='<span>'+result.data.company_name2+'</span>&nbsp;—&nbsp;<span>'+result.data.company_job2+'</span>';
                workhtml +='（<span style="margin-right:0px;">'+result.data.w_start_year2+'/'+result.data.w_start_month2+'</span>';
                workhtml +='-<span style="margin-right:0px;">'+result.data.w_end_year2+'/'+result.data.w_end_month2+'</span>）</div>';
                workhtml +='<p class="zg_t_ms" style="color:#999;">'+result.data.job_duties2+'</p></div>';
            }
            if(result.data.company_name3 != ""){
                workhtml += '<div class="zg_t_a"><div class="zg_t_s"><p class="zg_t_cz"><font class="bianji" data-item="work">编辑</font>';
                workhtml += '<font class="shanchu" data-item="work">删除</font></p>';
                workhtml +='<span>'+result.data.company_name3+'</span>&nbsp;—&nbsp;<span>'+result.data.company_job3+'</span>';
                workhtml +='（<span style="margin-right:0px;">'+result.data.w_start_year3+'/'+result.data.w_start_month3+'</span>';
                workhtml +='-<span style="margin-right:0px;">'+result.data.w_end_year3+'/'+result.data.w_end_month3+'</span>）</div>';
                workhtml +='<p class="zg_t_ms" style="color:#999;">'+result.data.job_duties3+'</p></div>';
            }
            $("#zg_work_exist").html(workhtml);
            showexist("work");
            $("#font_work").show();
        }else{
            $("#zg_work_exist .zg_t_ms").css("color","#333");
            $("#font_work").hide();
        }
    });
    $("#typejob_" + jtid).addClass("zg_tx_click").parent().parent().show();
}

//删除模板
function delitem(obj){
    itemid = $(obj).attr("data-item");
    $(obj).parent().parent().parent().remove();
    if($("#zg_"+itemid+"_exist").children("div").length == 0){
        $("#zg_"+itemid+"_exist").hide();
        $("#zg_"+itemid+"_null").show();
        $("#hid_"+itemid).val(0);
        getresumeintegrity();
    }
}

//底部保存按钮
$(".zg_t_bcz").live("click",function(){
    if(isEdit == false){
        $.qglobal.tip({content:"您还有未保存信息，请先保存后再进行编辑", icon:'error' ,time:2000});
        return false;
    }
    rid = $("#rid").val();
    if($("#e_jobname").text()!=""){
        $.ajax({
            type : 'POST',
            cache : false,
            dataType : 'json',
            url  : SLPGER.root + 'resume/editresume',
            data : {rid:rid},
            success:function(result){
                if(result.status!=1){
                    $.qglobal.tip({content:result.msg, icon:'error' ,time:3000});
                    return false;
                }else{
                    $.qglobal.tip({content:result.msg, icon:'success' ,time:3000});
                    setTimeout(function(){
                        window.location.href = SLPGER.root + "resume/index";
                    },3000);
                }
            }
        }); 
    }else{
        $.qglobal.tip({content:"您还没有保存简历求职意向信息！", icon:'error' ,time:3000});
    }
});

//获取简历完整度
function getresumeintegrity(){
    var allnum = 0;
    if($("#rid").val()!=""){
        allnum += 10;
    }
    if($("#hid_exp").val()!=0){
        allnum += 10;
    }
    if($("#hid_edu").val()!=0){
        allnum += 10;
    }
    if($("#hid_win").val()!=0){ 
        allnum += 10;
    }
    if($("#hid_active").val()!=0){
        allnum += 10;
    }
    if($("#hid_work").val()!=0){
        allnum += 10;
    }
    if($("#hid_interest").val()!=0){
        allnum += 10;
    }
    if($("#hid_skill").val()!=0){
        allnum += 10;
    }
    if($("#hid_eval").val()!=0){
        allnum += 10;
    }
    if($(".select_txt1").text()!="请选择到岗时间"){
        allnum += 5;
    }
    if($("#logoimg").attr("src").indexOf("public/images/tx.jpg") < 0){
        allnum += 5;
    }
    $("#integrity").html(allnum+"%");
}
//添加、修改手机号或者邮箱
$(".J_genghuan").live("click",function(){
    itemid = $(this).attr("data-item");
    var tipname = (itemid=="phone") ? "手机号" : "邮箱";
    if($(this).html()=="更换"){
        istype=1;
    }
    $("#save"+itemid).show();
    $("#genghuan").show();
    $('#save'+itemid+ ' .zg_tanchuang_bt').html($(this).html()+tipname+'<span class="quxiao">×</span>');
});
//添加、修改手机号或者邮箱弹窗取消按钮
$('.quxiao').live('click',function(){
    clearcontent(this);
});
function clearcontent(obj){
    $('input[name="phone"]').val('');
    $('input[name="email"]').val('');
    $('input[name="verify"]').val('');
    $("#J_phoneTip").attr("class","");
    $("#J_phoneTip").html("");
    $(obj).parent().parent().hide();
    $(".popIframe").hide();
}
//手机号验证
$('input[name="phone"]').focus(function(){
    $('#J_phoneTip').html('请输入手机号').attr('class','login_tip2');
}).blur(function(){
    var content = $(this).val();
    if(content!=''){
        $.ajax({
            type : 'POST',
            cache : false,
            dataType : 'json',
            url  : SLPGER.root + 'foreuser/isphone',
            data : {user:content}, 
            success:function(result){
                if(result.recode!=2){
                    $('#J_phoneTip').html(result.mag).attr('class','login_tip1');
                }else{
                    $('#J_phoneTip').html('').attr('class','login_tip3');
                }
            }
        });                  
    }else{
        $('#J_phoneTip').html('请输入手机号').attr('class','login_tip2');
    }
});
//获取验证码
$("#J_phone").click(function(){
    var content = $('input[name="phone"]').val();
    if(content==""){
        $('#J_phoneTip').html('请输入手机号').attr('class','login_tip2');
        return false;
    }
    $.ajax({
        type : 'POST',
        cache : false,
        dataType : 'json',
        url  : SLPGER.root  + 'foreuser/setphone',
        data : {user:content}, 
        success:function(result){
            if(result.recode!=2){
                 $('#J_phoneTip').html(result.mag).attr('class','login_tip1');                 
            }else{
                phoneTimes(60);
                $('#J_phoneTip').html('').attr('class','login_tip3'); 
                $('#J_phone').hide();
                $('#J_yzm3').show();                        
            }
        }
    });          
}); 
function phoneTimes(miao){
    miao--;
    $('#J_yzm3').html(miao+'秒后重新获得');
    if(miao>0){
        setTimeout("phoneTimes("+miao+")", 1000);
    }else{
        $('#J_phone').show();
        $('#J_yzm3').hide();
    }
}
//填写邮箱
$('input[name="email"]').focus(function(){
    $('#J_emailTip').html('请输入邮箱地址').attr('class','login_tip2');
}).blur(function(){
    var content = $(this).val();
    if(content!=''){
        $.ajax({
            type : 'POST',
            cache : false,
            dataType : 'json',
            url  : SLPGER.root + 'foreuser/isemail',
            data : {user:content}, 
            success:function(result){
                if(result.recode!=2){
                    $('#J_emailTip').html(result.mag).attr('class','login_tip1');
                }else{
                    $('#J_emailTip').html('').attr('class','login_tip3');
                    emailok = true;
                }
            }
        });                  
    }else{
        $('#J_emailTip').html('请输入邮箱地址').attr('class','login_tip2');
    }
});
$('.zg_down_tip').on('mouseenter','li',function(){
    $(".zg_down_tip li").removeClass("active");
    $(this).addClass("active");
});
$(".zg_down span").bind("click",function(e){
    e.stopPropagation();
    if($(".zg_down_tip").css("display")=="none"){
        $(".zg_down_tip").show();
    }else{
        $(".zg_down_tip").hide();
    }
});
 $(document).click(function() {
    $(".zg_down_tip").hide();
});

//切换作品展示
$(".handwork_tab span").each(function() {
    $(this).click(function() {
        if($(this).attr("class")=="upimage"){
            $("#divupload").show();
            $("#onlineForm").hide();
        }else if($(this).attr("class")=="uponline"){
            $("#onlineForm").show();
            $("#divupload").hide();
        }
    });
});

$("#onlineForm #handworkurl").on("keyup",function(){
    if($(this).val()==""){
        $("#onlineForm .presitelink").text("www.example.com");
    }else{
        $("#onlineForm .presitelink").text($(this).val());
    }
});
$("#onlineForm #o_picdes").on("keyup",function(){
    if($(this).val()==""){
        $(".onlinepre .predes").text("这里是你的作品描述");
    }else{
        $(".onlinepre .predes").text($(this).val());
    }
});

//作品展示图片上传
function hwimgupload(){
    var uploadimgpath = "";
    rid = $("#rid").val();
    var data = { };
    if(rid != ""){
        data.rid = rid;
    }
    var percentbar = $(".percentbar"); //进度条
    $("#hwupload").ajaxSubmit({
        dataType: 'json',
        data:data,
        beforeSend: function() {
            percentbar.html("0%");
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = "上传中..." + percentComplete + '%';
            percentbar.css({
                "display":"block",
            });
            percentbar.html(percentVal);
        },
        success: function(result) {
            percentbar.hide();
            if (1 === result.status) {
                //上传成功，显示裁剪页面
                uploadimgpath = SLPGER.root + result.data.path;
                selfcropzoom(uploadimgpath,"display");
                $("#cboxOverlay").show();
                $("#colorbox").show();
                $("#r").next("div").hide();
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }
    });
}