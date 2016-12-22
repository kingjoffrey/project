var WebSocketMessageHelp = new function () {
    this.switch = function (r) {
        //console.log(r)
        switch (r.type) {
            case 'open':
                Help.set(r)
                Help.fillText('game')
                break
            default:
                console.log(r)
        }
    }
}
