var WebSocketMessage = new function () {
    this.switch = function (r) {
        //console.log(r)
        switch (r.type) {
            case 'open':
                Help.set(r)
                $('#game').addClass('off')
                Help.fillText(r.game)
                break
            default:
                console.log(r)
        }
    }
}
