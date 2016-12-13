var WebSocketMainMessage = new function () {
    this.switch = function (r) {
        //console.log(r)
        switch (r.type) {
            case 'open':
                console.log(r)
                break
            default:
                console.log(r)
        }
    }
}
