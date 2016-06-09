/**
 * 添加公司信息
 */
var itemid = "",isEdit = false,nameEdit = false,basicEdit = false;
$(function(){
    init();
    $(".oBtnSEdit").live("click", function() {//编辑OR取消按钮
        itemid = $(this).attr("data-item");
        var name = $(this).children("a").text();
        if (name === "编辑") {
            if(itemid !== "name" && itemid !== "basic"){
                if(basicEdit === true){
                    if (isEdit === true) {
                        $(this).children("a").text("取消");
                        showempty(itemid);
                    }else{
                        $.qglobal.tip({content: "您还有未保存信息，请先保存后再进行编辑", icon: 'error', time: 2000});
                    }
                }else{
                    $.qglobal.tip({content: "您还没有填写公司基本信息，请先填写公司基本信息", icon: 'error', time: 2000});
                }
            }else{
                if(itemid!=="name"){
                    if(nameEdit === true && isEdit === true){
                        $(this).children("a").text("取消");
                        showempty(itemid);
                    }else{
                        $.qglobal.tip({content: "您还没有保存公司简称信息，请先填写公司简称信息", icon: 'error', time: 2000});
                    }
                }else{
                    if(nameEdit === true){
                        $("#shortname").attr("disabled",true);
                    }
                    showempty(itemid);
                }
            }
        } else {
            if(itemid !== "name"){
                $(this).children("a").text("编辑");
            }
            hideempty(itemid);
        }
    });

    $(".oResetCom").live("click", function() {
        isEdit = true;
        itemid = $(this).attr("data-item");
        $("#f_"+itemid).text("编辑");
        hideempty(itemid);
    });
    
    //点击菜单赋值给文本框
    $(".o_option a").click(function(){
        var value=$(this).text();
        var dataid = $(this).attr("data-id");
        $(this).parent().siblings(".select_txt").text(value);
        $(this).parent().parent().parent().parent().parent().children("input").val(dataid);
    });
    
    //标签选中
    $(".cptags").live("click",function(){
        var taghtml = $(this).parent().html();
        var selectedtags = $("#selectedtags");
        var selectednum = selectedtags.children("a").length;
        if(selectednum >=9){
            $.qglobal.tip({content: "您选择的标签数量已达到上限", icon: 'error', time: 2000});
            return false;
        }else{
            var newtag = taghtml.replace("class=\"cptags\"","");
            var emtag = newtag.replace("</a>","<em class=\"deltags\"></em></a>");
            selectedtags.append(emtag);
            selectednum = selectedtags.children("a").length;
            $("#selecttagnum").html(selectednum);
        }
    });
    
    //删除已选择的标签
    $(".deltags").live("click",function(){
        var tagid = $(this).attr("data-id");
        $(this).parent().remove();
        var selectedtags = $("#selectedtags");
        var selectednum = selectedtags.children("a").length;
        $("#selecttagnum").html(selectednum);
    });
    
    //风采编辑图删除按钮
    $(".showdel").live("click",function(){
        $(this).parent().prev().show();
    });
    
    //风采编辑图删除框取消按钮
    $(".stylecancel").live("click",function(){
        $(this).parent().parent().hide();
    });
    
    //设为封面按钮
    $(".setcover").live("click",function(){
        var imgid = $(this).attr("data-id");
        if(imgid == ""){
            $.qglobal.tip({content: "请选择一个风采图", icon: 'error', time: 2000});
        }
        var data = {tid:imgid};
        $.ajax({
            url: SLPGER.root + 'company/dosetcover',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(result) {
            if (result.status == 1) {
                $("#style_"+imgid).children("p.oSetCover").children("a:eq(0)").removeClass("setcover").text("已设为封面");
                $("#style_"+imgid).siblings().children("p.oSetCover").children("a:eq(0)").addClass("setcover").text("设置为封面");
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
    
    //自定义标签按钮
    $("#btndiytag").live("click",function(){
        var tagname = $("#diytagname").val();
        if(isNaN(tagname) === false){
            $.qglobal.tip({content: "自定义标签不能为纯数字，请重新填写", icon: 'error', time: 2000});
            return false;
        }
        var selectedtags = $("#selectedtags");
        if(tagname == ""){
            $.qglobal.tip({content: "自定义标签名称为空，请填写标签名称", icon: 'error', time: 2000});
            return false;
        }
        if(tagname.length > 6){
            $.qglobal.tip({content: "自定义标签字数长度超过上限", icon: 'error', time: 2000});
            return false;
        }
        var taghtml = '<a href="javascript:(0);">'+tagname+'<em class="deltags"></em></a>';
        var selectednum = selectedtags.children("a").length;
        if(selectednum >=9){
            $.qglobal.tip({content: "您选择的标签数量已达到上限", icon: 'error', time: 2000});
            return false;
        }else{
            selectedtags.append(taghtml);
            selectednum = selectedtags.children("a").length;
            $("#selecttagnum").html(selectednum);
            $("#diytagname").val("");
        }
    });
    
    $("#btnuploadstyle").live("click",function(){
        var cid = $("#hid_cpid").val();
        if(cid == ""){
            $.qglobal.tip({content: "请先填写公司基本信息", icon: 'error', time: 2000});
            return false;
        }
    });
    
    $("#btnuplogo").live("mouseover",function(){
        if ($(this).prev("img").attr("src") !== "") {
            $(this).css({"background": "url('" + SLPGER.root + "public/images/oIcoUpPic.png')"});
        }
    });
    $("#btnuplogo").live("mouseout",function(){
        if ($(this).prev("img").attr("src") !== "") {
            $(this).css({"background": "transparent none repeat scroll 0% 0%"});
        }
    });
    namevalidate(),basicvalidate(),briefvalidate(),tagvalidate(),stylevalidate(),addrvalidate();
});

//初始化
function init(){
    $("#cp_name_empty").hide();
    $("#cp_name_exist").show();
    $("#cp_basic_empty").hide();
    $("#cp_basic_exist").show();
    $("#cp_style_empty").hide();
    $("#cp_style_exist").show();
    $("#cp_brief_empty").hide();
    $("#cp_brief_exist").show();
    $("#cp_tag_empty").hide();
    $("#cp_tag_exist").show();
    $("#cp_addr_empty").hide();
    $("#cp_addr_exist").show();
}

//简称信息验证
function namevalidate(){
    //保存公司简称信息
    $("#btnnamesub").live("click",function(){
        var shortname = $("#shortname").val();
        if (shortname == "") {
            $.qglobal.tip({content: "公司简称不能为空", icon: 'error', time: 2000});
            return false;
        }
        $("#cpname").text(shortname);
        var website = $("#website").val();
        if (website == "") {
            $.qglobal.tip({content: "公司主页不能为空", icon: 'error', time: 2000});
            return false;
        }
        if(CheckUrl(website) === false){
            $.qglobal.tip({content: "公司主页格式不正确,请重新输入", icon: 'error', time: 2000});
            return false;
        }
        var cominfo = $("#cominfo").val();
        if(cominfo == ""){
            $.qglobal.tip({content: "公司一句话介绍不能为空", icon: 'error', time: 2000});
            return false;
        }
        Checklength(cominfo,50);
        var cid = $("#hid_cpid").val();
        if(cid != ""){
            //修改公司简称信息
            $('#btnnamesub').attr("disabled",true);//防重复提交
            var data = {cid:cid,cpname:shortname,shortname:shortname,website:website,cominfo:cominfo,type:"short"};
            var logoid = $("#hid_logo_id").val();
            if (logoid != "") {
                data.logoid = logoid;
            }
            $.ajax({
                url: SLPGER.root + '/company/doaddcompany',
                type: 'POST',
                data: data,
                dataType: 'json'
            }).done(function(result) {
                //防重复提交
                $('#btnnamesub').removeAttr("disabled");
                if (result.status == 1) {
                    $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
                } else {
                    $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
                }
            }); 
        }
        $("#e_shortname").text(shortname);
        $("#e_website").text(website);
        $("#e_cominfo").text(cominfo);
        regain("name");
    });
}

//基本信息验证
function basicvalidate(){
    $("#btnbasicsub").live("click",function(){
        var industry = $("#hid_industry").val();
        var industryname = $("#cp_industry").text();
        if(industry==""){
            $.qglobal.tip({content: "公司行业为空，至少选择一项", icon: 'error', time: 2000});
            return false;
        }
        var size = $("#hid_size").val();
        var sizename = $("#cp_size").text();
        if (size == "") {
            $.qglobal.tip({content: "公司规模为空，至少选择一项", icon: 'error', time: 2000});
            return false;
        }
        var nature = $("#hid_nature").val();
        var naturename = $("#cp_nature").text();
        if (nature == "") {
            $.qglobal.tip({content: "公司性质为空，至少选择一项", icon: 'error', time: 2000});
            return false;
        }
        var cpname = $("#cpname").text();
        if (cpname == "") {
            $.qglobal.tip({content: "公司名称不能为空", icon: 'error', time: 2000});
            return false;
        }
        var shortname = $("#shortname").val();
        if (shortname == "") {
            $.qglobal.tip({content: "公司简称不能为空", icon: 'error', time: 2000});
            return false;
        }
        var website = $("#website").val();
        if (website == "") {
            $.qglobal.tip({content: "公司主页不能为空", icon: 'error', time: 2000});
            return false;
        }
        var cominfo = $("#cominfo").val();
        if (cominfo == "") {
            $.qglobal.tip({content: "公司一句话简介不能为空", icon: 'error', time: 2000});
            return false;
        }
        var data = {cpname: cpname, shortname: shortname, website: website, cominfo: cominfo, industry: industry, industryname: industryname, size: size, sizename: sizename, nature: nature, naturename: naturename};
        var cid = $("#hid_cpid").val();
        if (cid != "") {
            data.cid = cid;
        }
        var logoid = $("#hid_logo_id").val();
        if(logoid!=""){
            data.logoid = logoid;
        }
        //防重复提交
        $('#btnbasicsub').attr("disabled","disabled");
        $.ajax({
            url: SLPGER.root + '/company/doaddcompany',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(result) {
            //防重复提交
            $('#btnbasicsub').removeAttr("disabled");
            if (result.status == 1) {
                $("#hid_cpid").val(result.data.cid);
                $("#e_shortname").text(result.data.shortname);
                $("#e_cominfo").text(result.data.cominfo);
                $("#e_industry").text(result.data.industry);
                $("#e_size").text(result.data.size);
                $("#e_nature").text(result.data.nature);
                $("#e_website").text(result.data.website);
                regain("basic");
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
}

//简介信息验证
function briefvalidate(){
    $("#btnbriefsub").live("click",function(){
        var cid = $("#hid_cpid").val();
        if (cid == "") {
            $.qglobal.tip({content: "请先填写公司基本信息", icon: 'error', time: 2000});
            return false;
        }
        var content = $("#cpintro").val();
        if(content == ""){
            $.qglobal.tip({content: "公司简介不能为空", icon: 'error', time: 2000});
            return false;
        }
        var bid = $("#hid_briefid").val();
        var data = {cid: cid,content:content};;
        if (bid != "") {
            data.bid = bid;
        } 
        //防重复提交
        $('#btnbriefsub').attr("disabled","disabled");
        $.ajax({
            url: SLPGER.root + '/company/doaddbrief',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(result) {
            //防重复提交
            $('#btnbriefsub').removeAttr("disabled");
            if (result.status == 1) {
                $("#hid_briefid").val(result.data.bid);
                $("#ScrollBarbox").html(result.data.brief);
                regain("brief");
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
}

//标签信息验证
function tagvalidate(){
    $("#btntagsub").live("click",function(){
        var cid = $("#hid_cpid").val();
        if(cid == ""){
            $.qglobal.tip({content: "请先填写公司基本信息", icon: 'error', time: 2000});
            return false;
        }
        var selectedtags = $("#selectedtags");
        var tagnum = selectedtags.children("a").length;
        if(tagnum == 0){
            $.qglobal.tip({content: "您还没有选择公司标签,至少选择一个", icon: 'error', time: 2000});
            return false;
        }
        var tagids = "";
        for(var i=0;i<tagnum;i++){
            if(typeof(selectedtags.children("a:eq("+i+")").attr("data-id"))=="undefined"){
                tagids += selectedtags.children("a:eq("+i+")").text() + ",";
            }else{
                tagids += selectedtags.children("a:eq("+i+")").attr("data-id") + ",";
            }
        }
        tagids = tagids.substring(0,tagids.length-1);
        //防重复提交
        $('#btntagsub').attr("disabled","disabled");
        $.ajax({
            url: SLPGER.root + '/company/doeditcompany',
            type: 'POST',
            data: {cid:cid,tags:tagids,type:"tag"},
            dataType: 'json'
        }).done(function(result) {
            //防重复提交
            $('#btntagsub').removeAttr("disabled");
            if (result.status == 1) {
                $("#cp_tag_exist").html(selectedtags.html().replace(/<em class="deltags"><\/em>/g,""));
                regain("tag");
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
}

//公司风采图验证
function stylevalidate(){
    //删除风采图
    $(".delstyle").live("click",function(){
        var cid = $("#hid_cpid").val();
        if(cid == ""){
            $.qglobal.tip({content: "请先填写公司基本信息", icon: 'error', time: 2000});
            return false;
        }
        var imgid = $(this).attr("data-id");
        if(imgid == ""){
            $.qglobal.tip({content: "请选择一个风采图", icon: 'error', time: 2000});
        }
        $(this).attr("disabled", true);//防重复提交
        var data = {imgid:imgid};
        $.ajax({
            url: SLPGER.root + 'company/dodelstyleimg',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(result) {
            //防重复提交
            $(this).removeAttr("disabled");
            if (result.status == 1) {
                $("#style_"+imgid).remove();
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
}

//公司地址验证
function addrvalidate(){
    $("#btnaddrsub").live("click",function(){
        var cid = $("#hid_cpid").val();
        if (cid == "") {
            $.qglobal.tip({content: "请先填写公司基本信息", icon: 'error', time: 2000});
            return false;
        }
        var content = $("#cpaddr").val();
        if(content == ""){
            $.qglobal.tip({content: "公司地址不能为空", icon: 'error', time: 2000});
            return false;
        }
        var data = {cid: cid,addr:content,type:"addr"};
        //防重复提交
        $('#btnaddrsub').attr("disabled","disabled");
        $.ajax({
            url: SLPGER.root + '/company/doeditcompany',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function(result) {
            //防重复提交
            $('#btnaddrsub').removeAttr("disabled");
            if (result.status == 1) {
                $("#e_addr").text(content);
                regain("addr");
                $.qglobal.tip({content: result.msg, icon: 'success', time: 2000});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }); 
    });
}

//检查输入内容长度
function Checklength(content,maxlength){
    var contentlength = content.length;
    if(contentlength > maxlength){
        $.qglobal.tip({content: "您输入的内容超过了最大限制", icon: 'error', time: 2000});
        return false;
    }
}

function checkupkey(obj){
    var id = $(obj).attr("id");
    var text = $(obj).val();
    var max = $(obj).attr("maxlength");
    var counter = text.length;
    if(id=="cpintro"){
        $("#"+id+"num").text(parseInt(max-counter));
    }else{
        $("#"+id+"num").text(counter);
    }
}

//验证网址
function CheckUrl(str) {
    var RegUrl = new RegExp();
    RegUrl.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$");
    if (!RegUrl.test(str)) {
        return false;
    }
    return true;
}

function showempty(itemid) {
    $("#cp_" + itemid + "_exist").hide();
    $("#cp_" + itemid + "_empty").show();
}

function hideempty(itemid) {
    $("#cp_" + itemid + "_exist").show();
    $("#cp_" + itemid + "_empty").hide();
}

//logo图上传
function logochange(){
    var cid = $("#hid_cpid").val();
    var data = { };
    if (cid != "") {
        data.cid = cid;
    }
    $("#logoupload").ajaxSubmit({
        dataType: 'json',
        data : data,
        success: function(result) {
            if (1 === result.status) {
                $("#logoimg").attr("src",result.data.path);
                $("#hid_logo_id").val(result.data.logoid);
                $("#btnuplogo").css({"background": "transparent none repeat scroll 0% 0%"});
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }
    });
}

//成功保存基本信息后
function regain(itemid) {
    isEdit = true;//更新编辑状态
    switch (itemid) {
        case "name":
            nameEdit = true;
            break;
        case "basic":
            basicEdit = true;
            break;
    }
    $("#f_"+itemid).text("编辑");
    $("#cp_" + itemid + "_empty").hide();
    $("#cp_" + itemid + "_exist").show();
}

//公司风采图上传
function styleupchange(){
    var slen = $("#styleuploaded").children("div").length;
    if(slen >= 10){
        $.qglobal.tip({content: "已到达上传公司风采图上限,不能再上传。", icon: 'error', time: 2000});
        return false;
    }
    var cid = $("#hid_cpid").val();
    if(cid == ""){
        $.qglobal.tip({content: "请填写公司基本信息", icon: 'error', time: 2000});
        return false;
    }
    var data = { cid:cid };
    $("#styleuploadFrom").ajaxSubmit({
        dataType: 'json',
        data : data,
        success: function(result) {
            if (1 === result.status) {
                var imghtml = "";
                imghtml += '<div id="style_'+result.data.imgid+'" class="oComPicItem fl" onmouseover="styleover(this)" onmouseout="styleout(this)"><img src="'+SLPGER.root+result.data.path+'" width="330" height="234" alt="" class="oComBpicP">';
                imghtml += '<div class="oPicRemoveC"><h2>确定删除这张图片？</h2><p class="mt10"><input type="button" class="oBtnSaveCom delstyle" value="删除" data-id="'+result.data.imgid+'"><input type="reset" value="取消" class="oResetCom stylecancel"></p>';
                imghtml += '</div><p class="oSetCover"><a href="javascript:(0);" class="setcover" data-id="'+result.data.imgid+'">设置为封面</a> | <a href="javascript:(0);" class="showdel">删除</a></p></div>';
                $(imghtml).appendTo($("#styleuploaded"));
                var slen = $("#styleuploaded").children("div").length;
                $("#stylenum").text(slen);
            } else {
                $.qglobal.tip({content: result.msg, icon: 'error', time: 2000});
            }
        }
    });
}

//鼠标滑过风采编辑图
function styleover(obj) {
    $(obj).children("p.oSetCover").show();
}

//鼠标离开风采编辑图
function styleout(obj) {
    $(obj).children("p.oSetCover").hide();
}
