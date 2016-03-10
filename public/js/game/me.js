var Me = new function () {
    this.turnOn = function () {
        if (Turn.isMy() && Turn.getNumber() == 1 && !this.getCastle(this.getFirsCastleId()).getProductionId()) {
            CastleWindow.show(this.getCastle(this.getFirsCastleId()))
        } else {
            //Players.showFirst(color)
            var id = Message.show(translations.yourTurn, translations.thisIsYourTurnNow)
            Message.ok(id, CommonMe.findFirst)
        }
    }
}