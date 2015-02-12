var Chat = new function () {
    var chatContent = $('#chatWindow #chatContent')
    var renderChatCloud = function (date, message, color) {
        var div = $('<div>')
            .append($('<span>').html(Players.get(color).getLongName() + ' (' + date + ')'))
            .append('<br>')
            .append(message)
            .addClass('chatCloud')
        if (Me.colorEquals(color)) {
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
    this.init = function (chatHistory) {
        for (var i in chatHistory) {
            renderChatCloud(getISODateTime(chatHistory[i]['date']), chatHistory[i]['message'], chatHistory[i]['color'])
        }
        $('#chatWindow').animate({scrollTop: $('#chatWindow div')[0].scrollHeight}, 1000)
    }
    this.message = function (color, msg, time) {
        if (!Me.colorEquals(color)) {
            titleBlink('Incoming chat!')
        }
        renderChatCloud(time, msg, color)
        $('#chatWindow').animate({scrollTop: $('#chatWindow #chatContent')[0].scrollHeight}, 'fast')
        $('#msg').focus()
    }
}
