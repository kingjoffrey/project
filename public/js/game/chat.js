var Chat = new function () {
    var chatContent,
        renderChatCloud = function (date, message, color) {
            var div = $('<div>')
                .append($('<span>').html(Players.get(color).getLongName() + ' (' + date + ')'))
                .append('<br>')
                .append(message)
                .addClass('chatCloud')

            if (CommonMe.colorEquals(color)) {
                var align = 'right'
                div.css({
                    'text-align': align
                })
                div = div.add($('<img>').attr('src', Hero.getImage(color)))

            } else {
                var align = 'left'
                div.css({'text-align': align})
                div = $('<img>').attr('src', Hero.getImage(color)).add(div)
            }
            chatContent.append($('<div>').css({'text-align': align}).append(div).addClass('chatCloudContainer'))
        },
        showMsg = function () {
            $('#chatWindow').animate({scrollTop: $('#chatWindow #chatContent')[0].scrollHeight}, 'fast')
            $('#msg').focus()
        }

    this.init = function (chatHistory) {
        chatContent = $('#chatWindow #chatContent')
        for (var i in chatHistory) {
            renderChatCloud(getISODateTime(chatHistory[i]['date']), chatHistory[i]['message'], chatHistory[i]['color'])
        }
        $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
    }
    this.message = function (color, msg, time) {
        renderChatCloud(time, msg, color)
        if ($('#chatBox.mini').length) {
            GameGui.moveChatBox(showMsg)
        }
        //else {
        showMsg()
        //}
        if (!CommonMe.colorEquals(color)) {
            GameGui.titleBlink(translations.incomingChat)
        }
    }
}
