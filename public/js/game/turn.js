var Turn = new function () {
    var color = null,
        number = 0

    this.getColor = function () {
        return color
    }
    this.change = function (c) {
        color = c
        number++
        if (Turn.isMy()) {
            CommonMe.turnOn()
            WebSocketSendGame.startMyTurn();
        } else {
            CommonMe.turnOff()
        }
    }
    this.isMy = function () {
        if (CommonMe.colorEquals(color)) {
            return true
        }
    }
    this.getNumber = function () {
        return number
    }
    this.next = function () {
        var id = Message.simple(translations.nextTurn, translations.areYouSure)
        Message.ok(id, WebSocketSendGame.nextTurn)
    }
    this.init = function (c, n) {
        color = c
        number = n
    }
}
