/**
 * @name  资讯搜索 
 * @author wjh 2014-11-11
 */
;(function($){
    $.qglobal.search = {
        settings: {
            search: '.J_search_btn', //搜索条件
            closed: '.J_search_closed_btn' //搜索关闭
        },
        //初始化
        init: function(options){
            options && $.extend($.qglobal.search.settings, options);
            var s = $.qglobal.search.settings;
            $.qglobal.search.ajaxsearch(); //搜索条件响应
            $.qglobal.search.ajaxclose();  //搜索条件关闭
        },
        //生成搜索条件并触发ajax去获取数据
        ajaxsearch:function(){
            var s = $.qglobal.search.settings;
            $(s.search).live('click', function(){
                //添加已选中多次点击判断提示信息
//                if($(this).attr('class') === 'J_search_btn hover'){
//                    $.qglobal.tip({content:'该条件已选中,请换一个条件试试！', icon:'error'});
//                    return false;
//                }
                var ids = new Array();
                var id = $(this).attr('id'); //当前搜索条件的id 
                var name = $(this).html();//搜索条件内容
                ids = id.split('_');
                //追加搜索条件显示
                if(name){
                    if(ids[0] == "typejobid"){
                        var searchs ="<span><a href='javascript:void(0);' class='J_search_closed_btn' id='close_"+ids[0]+"' data='"+ids[1]+"'> 栏目分类：<span>"+name+"</span><em >×</em></a></span>";
                    }else if(ids[0] == "year"){
                        var searchs ="<span><a href='javascript:void(0);' class='J_search_closed_btn' id='close_"+ids[0]+"' data='"+ids[1]+"'> 年份：<span>"+name+"</span><em >×</em></a></span>";
                    }
                    var search_items = $('#search_items').val();//分类内容
                    if(search_items){
                        var nums = search_items.indexOf(ids[0]);
                        //判断有没有重复类型的值已经存在 存在后替换 没有就拼接
                        if(nums >= 0){ //说明已经存在
                            var data =  $('#close_'+ids[0]).attr('data'); //获取当前内容 
                            //替换之前已经存在的相同类型的搜索条件items 然后在赋值
                            if(search_items.indexOf(','+ids[0]+'/'+data+',') >= 0){
                                search_items = search_items.replace(','+ids[0]+'/'+data+',',','+ids[0]+'/'+ids[1]+',');
                            }else if(search_items.indexOf(','+ids[0]+'/'+data) >= 0){
                                search_items = search_items.replace(','+ids[0]+'/'+data,','+ids[0]+'/'+ids[1]);
                            }else if(search_items.indexOf(ids[0]+'/'+data+',') >= 0){
                                search_items = search_items.replace(ids[0]+'/'+data+',',ids[0]+'/'+ids[1]+',');
                            }else{
                                search_items = search_items.replace(ids[0]+'/'+data,ids[0]+'/'+ids[1]);
                            }
                            $('#search_items').val(search_items); //直接替换
                        }else{
                            $("#search_items").val(ids[0]+'/'+ids[1]+','+search_items);
                        }
                    }else{
                        $("#search_items").val(ids[0]+'/'+ids[1]); 
                    }
                    if(ids[0] == 'typejobid'){
                        $("#search_typejobid").html(searchs).show();
                    }else if(ids[0] == 'year'){
                        $("#search_year").html(searchs).show(); 
                    }
                }else{
                    $.qglobal.tip({content:'搜索条件获取失败', icon:'error'});
                }
                //重新获取search条件并ajax获取数据
                search_items = $("#search_items").val();
                $.qglobal.search.ajaxGetData(search_items);
            });
        },
        //点击关闭搜索条件并触发ajax去重新获取数据
        ajaxclose:function(){
            var s = $.qglobal.search.settings;
            $(s.closed).live('click', function(){
                var ids = new Array();
                var id = $(this).attr('id'); //当前搜索条件关闭按钮的id 
                var name = $(this).attr('data');//当前搜索条件内容
                ids = id.split('_');
                var search_items = $('#search_items').val();//分类内容
                if(search_items){
                    //将要关闭的分类去掉
                    if(search_items.indexOf(','+ids[1]+'/'+name+',') >= 0){
                        search_items = search_items.replace(','+ids[1]+'/'+name,'');
                    }else if(search_items.indexOf(','+ids[1]+'/'+name) >= 0){
                        search_items = search_items.replace(','+ids[1]+'/'+name,'');
                    }else if(search_items.indexOf(ids[1]+'/'+name+',') >= 0){
                        search_items = search_items.replace(ids[1]+'/'+name+',','');
                    }else{
                        search_items = search_items.replace(ids[1]+'/'+name,'');
                    }
                    $('#search_items').val(search_items);
                    $('#search_'+ids[1]).hide();//关闭搜索条件
                }
                //重新获取search条件并ajax获取数据
                search_items = $("#search_items").val();
                $.qglobal.search.ajaxGetData(search_items);
            });
        },
        ajaxGetData:function(search_items){
            var search_url = '';
            if(search_items.indexOf(',') >= 0){
                search_url = search_items.replace(',','/');//存在多个条件
                search_url = search_url.replace(',','/');//存在多个条件
            }else{
                search_url = search_items;  //当只有一个条件的话
            }
            //ajax请求数据
            $('#ajax_div').hide();
            $.ajax({
                type : 'GET',
                cache : true,
                dataType : 'json',
                url  : SLPGER.root + 'message/ajaxsearch/'+search_url,
                data : '', 
                success:function(result){ 
                    if(result.status == 1){
                        var messages = "";
                        messages += "<ul>";
                        var j = 1;
                        $(result.data).each(function(){
                            messages += "<li><a href='" + this.href + "' target='_blank'>"+this.title+"</a><span>"+this.add_time+"</span></li>";
                            if(j % 5 == 0){
                                messages += "<li class='line'></li>";
                            }
                            j++;
                        })
                        messages += "</ul><div class='zg_page'>"+result.dialog+"</div>";
                        $('#ajax_div').html(messages);
                    }else{
                        $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                        $('#ajax_div').html(result.msg);
                    }
                    $('#ajax_div').show();
                }
            });
        }
    };
    $.qglobal.search.init();
})(jQuery);