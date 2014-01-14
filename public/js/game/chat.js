function chat(color, msg, time) {
    if (color != my.color) {
        titleBlink('Incoming chat!');
    }

    var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + players[color].textColor + ';background:' + players[color].backgroundColor + '">' + players[color].longName + ' (' + time + '):</span> ' + msg);

    $('#chatWindow').animate({ scrollTop: $('#chatWindow div')[0].scrollHeight }, 'fast');

    $('#msg').focus();
}

function renderChatHistory() {
    for (i in chatHistory) {
        var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + players[chatHistory[i]['color']].textColor + ';background:' + players[chatHistory[i]['color']].backgroundColor + '">' + players[chatHistory[i]['color']].longName + ' (' + getISODateTime(chatHistory[i]['date']) + '):</span> ' + chatHistory[i]['message']);
    }
    $('#chatWindow').animate({ scrollTop: $('#chatWindow div')[0].scrollHeight }, 1000);
}