var Picker = new function () {
    this.onContainerMouseDown = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            switch (event.button) {
                case 0:
                    if (Me.getSelectedArmyId()) {
                        WebSocketSend.move()
                    } else {
                        var field = PickerCommon.getField()
                        if (field.hasArmies()) {
                            var armies = field.getArmies()
                            for (var armyId in armies) {
                                if (Me.colorEquals(armies[armyId])) {
                                    Me.armyClick(armyId)
                                }
                            }
                        } else if (Me.colorEquals(field.getCastleColor())) {
                            var castleId = field.getCastleId()
                            if (Me.getSelectedCastleId()) {
                                if (Me.getSelectedCastleId() != castleId) {
                                    WebSocketSend.production(Me.getSelectedCastleId(), Me.getSelectedUnitId(), castleId)
                                }
                                Me.setSelectedCastleId(null)
                                Me.setSelectedUnitId(null)
                            } else {
                                CastleWindow.show(Me.getCastle(castleId))
                            }
                        }
                    }
                    break

                case 1:
                    // middle button
                    break

                case 2:
                    Me.deselectArmy()
                    break
            }
        }
    }
    this.onContainerMouseMove = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && Me.getSelectedArmyId()) {
                AStar.showPath()
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault();
    }
}
