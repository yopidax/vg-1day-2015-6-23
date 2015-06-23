/**
 * メッセージリストの読み込み
 */
function reloadMessages() {
    var success = function(data) { appendMessages(data) };
    var error   = function() { console.log("error") };
    getMessages(success, error);
}

/**
 * メッセージの投稿
 */
function sendMessage(username, body) {
    var success = function() {
        $(".message-username").val("");
        $(".message-body").val("");
        reloadMessages();
    };
    var error   = function() { console.log("error") };
    postMessage(username, body, success, error);
}

/**
 *
 */
function removeMessage(id) {
	var success = function() {
		reloadMesages();
	};
	var error = function() { console.log("error") };
	deleteMessage(id, success, error);
}

/**
 * メッセージリスト挿入
 */
function appendMessages(data) {
    $("#message-table").empty();
    for ( var i = 0; i < data.length; i++ ) {
        var object = data[i];
        appendMessage(object);
    }
}

/**
 * メッセージ挿入
 */
function appendMessage(message) {
	var escapeUsername = $("<div/>").text(message.username).html();
	var escapeBody = $("<div/>").text(message.body).html();
	var escapeCreatedAt = $("<div/>").text(message.created_at).html();
	var escapeIcon = $("<div/>").text(message.icon).html();
	var escapeId = $("<div/>").text(message.id).html();

    var messageHTML = '<tr><td>' +
        '<div class="media message">' +
        '<div class="media-left" id="top-aligned-media">' +
        '<img class="media-object" src="data:image/png;base64,' + escapeIcon + '" data-holder-rendered="true" style="width: 64px; height: 64px;">' +
        '</div>' +
        '<div class="media-body">' +
        '<h4 class="media-heading">' + escapeUsername + '</h4>' +
        escapeBody + '<br>' +
        escapeCreatedAt +
	    '</div>' +
        '<div class="media-right">' +
        '<input type="button" class="btn btn-primary delete-message" value="削除" id="' + escapeId + '"></input>' +
        '</div>' +
        '</div>' +
        '</td></tr>';
	$("#message-table").append(messageHTML);
}

/**
 * APIリクエストコメント取得
 */
function getMessages(success, error) {
    var getMessageUri = "http://localhost:8888/messages";
    return $.ajax({
        type: "get",
        url: getMessageUri,
        })
    .done(function(data) { success(data) })
    .fail(function() { error() });
}

/**
 * APIリクエストコメント投稿
 */
function postMessage(username, body, success, error) {
    var postMessageUri = "http://localhost:8888/messages";
    return $.ajax({
        type: "post",
        url: postMessageUri,
        data: JSON.stringify({"username":username, "body":body}), 
        dataType: "json",
        })
    .done(function(data) { success() })
    .fail(function() { error() });
}

/**
 * APIリクエストコメント削除
 */
function deleteMessage(id, success, error) {
    var deleteMessageUri = "http://localhost:8888/messages/" + id;
    return $.ajax({
        type: "delete",
        url: deleteMessageUri,
        })
    .done(function(data) { success() })
    .fail(function() { error() });
}
