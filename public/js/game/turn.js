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
            Me.turnOn()
            WebSocketSendGame.startMyTurn();
        } else {
            Me.turnOff()
        }
    }
    this.isMy = function () {
        if (Me.colorEquals(color)) {
            return true
        }
    }
    this.getNumber = function () {
        return number
    }
    this.next = function () {
        var id = Message.simple(translations.nextTurn, translations.areYouSure)
        Message.addButton(id, 'Yes', WebSocketSendGame.nextTurn)
    }
    this.init = function (c, n) {
        color = c
        number = n
    }
}
