/**
 * 简单日历插件
 * @author fmj
 */
//日历
function initcalendar(){
    var box = $(".mr_calendar_ym");
    $(".mrStartTimeLabel").children("input").attr("readonly",true); 
    box.hide();
    var now = new Date();
    var year = now.getFullYear();//当前年
    var month = now.getMonth() + 1;//当前月
    var yearhtml = "";
    var monthhtml = "";
    var inputname = "";
    $(".mrStartTimeLabel").live("click",function(){
        if($(this).children("input").attr("id") == "u_birth"){
            year = now.getFullYear() - 18 ;
        }
        yearhtml = createyear(year);
        inputname = $(this).children("input").attr("name");
        if(inputname == "enddate" || inputname == "active_workend" || inputname == "work_end"){
            yearhtml = "<li class='still'>至今</li>" + yearhtml;
        }
        monthhtml = createmonth(this,year);
        $(this).next("div").children("ul:eq(0)").html("");
        $(this).next("div").children("ul:eq(1)").html("");
        $(this).next("div").children("ul:eq(0)").append(yearhtml);
        $(this).next("div").children("ul:eq(1)").append(monthhtml);
        $(".mr_calendar_ym ").hide();
        $(this).next("div").show();
        var existym = $(this).children("input").val();
        if(existym != ""){
            $(this).next("div").children("ul:eq(1)").html("");
            var existArr = existym.split('/');
            if(existArr.length > 0){
                selectyear(this,existArr[0],0);
                monthhtml = createmonth(this,existArr[0]);
                $(this).next("div").children("ul:eq(1)").html(monthhtml);
                var monthli = $(this).next("div").children("ul:eq(1)").children("li");
                var m = "";
                for(var i=0;i<monthli.length;i++){
                    m = $(this).next("div").children("ul:eq(1)").children("li:eq("+i+")").children("span").text().replace(/[^0-9,]*/ig,""); 
                    if(m == parseInt(existArr[1])){
                        $(".mr_month span").removeClass("active");
                        $(this).next("div").children("ul:eq(1)").children("li:eq("+i+")").children("span").addClass("active");
                        return false;
                    }
                }
            }
        }
    });
    $(document).click(function(event) {
        var eo = $(event.target);
        if(eo.context.className!="active" && eo.context.className.indexOf("zg_txt") < 0 && eo.context.className!="disable"){
            if ($(".mr_calendar_ym").is(":visible"))
                $('.mr_calendar_ym').hide();
        }
    });
}

//生成年份
function createyear(year){
    var html = "";
    for (var i = 0; i <= 45; i++) {
        if(i==0){
            html += '<li onclick="selectyear(this,'+(year - i)+')" class="active">'+(year - i)+'</li>';
        }else{
            html += '<li onclick="selectyear(this,'+(year - i)+')">'+(year - i)+'</li>';
        }
    }
    return html;
}

//生成月份
function createmonth(obj,year){
    $(obj).next("div").children("ul:eq(1)").html("");
    var now = new Date();
    var nowmonth = now.getMonth() + 1;
    var monthhtml = "";
    if(year == now.getFullYear()){
        for(var m=1;m<=12;m++){
            if(m>nowmonth){
                 monthhtml += '<li class="mb0"><span class="disable">'+m+'月</span></li>';
            }else if(m === nowmonth){
                 monthhtml += '<li class="mr0"><span onclick="selectmonth(this,'+m+')" class="active">'+m+'月</span></li>';
            }else{
                monthhtml += '<li><span onclick="selectmonth(this,'+m+')">'+m+'月</span></li>';
            }
        }
    }else{
        for(var m=1;m<=12;m++){
            monthhtml += '<li><span onclick="selectmonth(this,'+m+')">'+m+'月</span></li>';
        }
    }
    return monthhtml;
}

//点击切换年份
function selectyear(obj,year,hadyear){
    if(hadyear!=0){
        $(obj).addClass('active').siblings().removeClass('active');
        //根据年份生成月份
        $(obj).parent().next().html("");
        var monthhtml = createmonth(obj,year);
        $(obj).parent().next().append(monthhtml);
    }else{
        //根据年份，让该年份处于选中状态
        $(obj).next("div").children("ul:eq(0)").children().removeClass("active");
        var yearli = $(obj).next("div").children("ul:eq(0)").children("li");
        for(var i=0;i<yearli.length;i++){
            if($(obj).next("div").children("ul:eq(0)").children("li:eq("+i+")").text() == year){
                $(obj).next("div").children("ul:eq(0)").children("li:eq("+i+")").addClass("active");
                return false;
            }
        }
    }
}

//点击切换月份
function selectmonth(obj,month){
    $("span.error").hide();
    $(obj).parent().siblings().children("span").removeClass("active");
    $(obj).addClass("active");
    if(month < 10){
        month = "0" + month;
    }
    var year = $(obj).parent().parent().prev().children("li.active").html();
    if(year=="至今"){
        return false;
    }
    if(year == "" || typeof(year) === "undefined"){
        $(obj).parent().parent().parent().hide();
        $.qglobal.tip({content:"选择年份异常,请重新选择", icon:'error' ,time:2000});
        return false;
    }
    if(month == "" || typeof(month) == "undefined"){
        $(obj).parent().parent().parent().hide();
        $.qglobal.tip({content:"选择月份异常,请重新选择", icon:'error' ,time:2000});
        return false;
    }
    $(obj).parent().parent().parent().prev().children("input").val(year+'/'+month);
    $(obj).parent().parent().parent().hide();
}

$(".still").live("click",function(){
    $("span.error").hide();
    $(this).addClass("active");
    $(this).parent().parent().prev().children("input").val($(this).html());
    $(this).parent().parent().hide();
});


