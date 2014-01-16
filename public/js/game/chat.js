var Chat = {
    renderChatCloud: function (chatContent, date, message, color) {
        var div = $('<div>')
            .append($('<span>').html(players[color].longName + ' (' + date + ')'))
            .append('<br>')
            .append(message)
            .addClass('chatCloud')
        if (color == my.color) {
            var align = 'right'
            div
                .css({
                    'text-align': align
                })
            div = div.add($('<img>').attr('src', Hero.getImage(color)))

        } else {
            var align = 'left'
            div
                .css({
                    'text-align': align
                })
            div =
                $('<img>').attr('src', Hero.getImage(color)).add(div)
        }
        chatContent
            .append(
                $('<div>')
                    .css({
                        'text-align': align
                    })
                    .append(div)
                    .addClass('chatCloudContainer')
            )
    }
}

function chat(color, msg, time) {
    if (color != my.color) {
        titleBlink('Incoming chat!');
    }

    Chat.renderChatCloud($('#chatWindow #chatContent'), time, msg, color)

    $('#chatWindow').animate({ scrollTop: $('#chatWindow #chatContent')[0].scrollHeight }, 'fast');

    $('#msg').focus();
}

function renderChatHistory() {
    var chatContent = $('#chatWindow #chatContent')
    for (i in chatHistory) {
        Chat.renderChatCloud(chatContent, getISODateTime(chatHistory[i]['date']), chatHistory[i]['message'], chatHistory[i]['color'])
    }
    $('#chatWindow').animate({ scrollTop: $('#chatWindow div')[0].scrollHeight }, 1000);
}

