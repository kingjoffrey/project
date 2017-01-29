var WebSocketMessageChat = new function () {
    this.switch = function (r) {
        switch (r.type) {
            case 'notification':
                if (!parseInt(r.count)) {
                    return
                }
                $('#envelope').html($('<span>').html(r.count))
                break
            default:
                console.log(r);
        }
    }
}
