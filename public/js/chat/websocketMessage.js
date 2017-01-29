var WebSocketMessageChat = new function () {
    this.switch = function (r) {
        switch (r.type) {
            case 'notification':
                if (!parseInt(r.count)) {
                    return
                }
                $('#envelope').html($('<span>').html(r.count))
                break
            case 'chat':
                $('#messages').append(
                    $('<tr>').addClass('trlink')
                        .append($('<td>').addClass('date').html(r.name))
                        .append($('<td>').addClass('msg').html(r.message))
                )
                break
            default:
                console.log(r);
        }
    }
}
