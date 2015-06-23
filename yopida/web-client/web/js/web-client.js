/**
 * 読み込み完了
 */
$(document).ready(function () {
    reloadMessages();
});
/**
 * 投稿
 */
$(".post-message").bind("click", function() {
    $("#myModal").modal("hide");
    var body = {'message':'ms','name':'name'};
    body['message'] = $(".message-body").val();
    body['name'] = $(".message-name").val();
    sendMessage(body);
});

/**
*削除
*/
$(".message-table").on("click",".post-delete", function(){
    console.log("re");
    var id = $(this).attr('id');
    console.log(id);
});

/**
 * 画像選択
 */
$("#image-form").change(function () {
    if (!this.files.length) {
        return;
    }

    var file = this.files[0];
    var fileReader = new FileReader();
    fileReader.readAsDataURL(file);

    fileReader.onload = function() {
        var image = new Image();
        image.src = this.result;
        insertImage(image);
    }
});

/**
 * 画像挿入
 */
function insertImage(image) {
    $(".image-result").html('<img src="' + image.src + '" width="60">');
}
