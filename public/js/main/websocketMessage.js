var WebSocketMainMessage = new function () {
    this.switch = function (r) {
        //console.log(r)
        switch (r.type) {
            case 'controller':
                Main.controller(r)
                break
            case 'open':
                delete r.type
                Main.createMenu(r)
                break
            default:
                console.log(r)
        }
    }
}
