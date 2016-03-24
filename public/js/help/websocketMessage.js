var WebSocketMessage = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                break
            default:
                console.log(r)
        }
    }
}
