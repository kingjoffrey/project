var Chat = new function () {

    this.addMessage=function (message) {
        $('#conversation').append(
            $('<tr>').addClass('trlink')
                .append($('<td>').addClass('date').html(message.date + '<br>' + message.name))
                .append($('<td>').addClass('msg').html(message.message))
        )
    }

    this.addThread = function (id, thread) {
        $('table').append(
            $('<tr>').attr('id', id).addClass('trlink')
                .append($('<td>').html(thread.name))
                .append($('<td>').html(thread.unread))
                .click(function () {
                    WebSocketSendMain.controller('messages', 'thread', {'id': $(this).attr('id')})
                })
        )
    }
}
