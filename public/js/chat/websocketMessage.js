var WebSocketMessageChat = new function () {
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
                $('#chat input').val('')
                Chat.addMessage()
                break
            case 'threads':
                for (var id in r.threads) {
                    Chat.addThread(id, r.threads[id])
                }
                break
            case 'conversation':

                for (var i in r.messages) {
                    Chat.addMessage(r.messages[i])
                }

                $('.table').append(
                    $('<div>').attr('id', 'chat')
                        .append($('<input>'))
                        .append($('<div>')
                            .addClass('button buttonColors')
                            .html(translations.send)
                            .click(function () {
                                var message = $('#chat input').val()

                                if (message) {
                                    WebSocketSendChat.send(message)
                                }
                            })
                        )
                )
                break
            default:
                console.log(r);
        }
    }
}
