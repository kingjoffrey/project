var Picker = new function () {
    this.onContainerMouseDown = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            switch (event.button) {
                case 0:
                    if (CommonMe.getSelectedArmyId()) {
                        WebSocketSend.move()
                    } else {
                        var field = PickerCommon.getField()
                        if (field.hasArmies()) {
                            var armies = field.getArmies()
                            for (var armyId in armies) {
                                if (CommonMe.colorEquals(armies[armyId])) {
                                    CommonMe.armyClick(armyId)
                                }
                            }
                        } else if (CommonMe.colorEquals(field.getCastleColor())) {
                            var castleId = field.getCastleId()
                            if (CommonMe.getSelectedCastleId()) {
                                if (CommonMe.getSelectedCastleId() != castleId) {
                                    WebSocketSend.production(CommonMe.getSelectedCastleId(), CommonMe.getSelectedUnitId(), castleId)
                                }
                                CommonMe.setSelectedCastleId(null)
                                CommonMe.setSelectedUnitId(null)
                            } else {
                                CastleWindow.show(CommonMe.getCastle(castleId))
                            }
                        }
                    }
                    break

                case 1:
                    // middle button
                    break

                case 2:
                    CommonMe.deselectArmy()
                    break
            }
        }
    }
    this.onContainerMouseMove = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && CommonMe.getSelectedArmyId()) {
                AStar.showPath()
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault();
    }
}
