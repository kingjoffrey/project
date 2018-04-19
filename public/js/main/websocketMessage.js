var WebSocketMessageMain = new function () {
    this.switch = function (r) {
        if (r.type == 'open') {
            Main.setEnv(r.env)
            Main.createMenu(r.menu)
        } else {
            Main.createContent(r)
        }
    }
}
