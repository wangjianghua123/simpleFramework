/* 上传头像 */
$(function () {
    $(".zg_pic").click(function(){
        document.all.fileupload.click();
    });
    $("#fileupload").wrap('<form id="myupload" action="<?php echo $this->url("imgupload","","resume");?>" method="post" enctype="multipart/form-data"></form>');
    $("#fileupload").change(function(){
        $("#myupload").ajaxSubmit({
            dataType:  'json',
            success: function(result) {
                if(1 === result.status){
                    //上传成功，显示裁剪页面
                    $("#img_to_crop").attr("xlink:href",SLPGER.root+result.data);
                    $("#cboxOverlay").show();
                    $("#colorbox").show();
                }else{
                    $.qglobal.tip({content:result.msg, icon:'error' ,time:2000});
                }
            }
        });
    });
});
function closebox(){
    $("#img_to_crop").attr("xlink:href","");
    $("#cboxOverlay").hide();
    $("#colorbox").hide();
    history.go(0);
}