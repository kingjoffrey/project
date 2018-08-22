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

    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'notification':
                if (!parseInt(r.count)) {
                    return
                }
                $('#messages').css('background-color', 'red')
                break
            case 'chat':
                $('#conversation').append(
                    $('<tr>').addClass('trlink')
                        .append($('<td>').addClass('date').html(r.name))
                        .append($('<td>').addClass('msg').html(r.message))
                )
                break
            case 'threads':
                for (var id in r.threads) {
                    this.addThread(id, r.threads[id])
                }
                break
        }
    }
}
