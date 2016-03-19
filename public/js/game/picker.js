var Picker = new function () {
    var dragStart = 0

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
                        } else {
                            dragStart = PickerCommon.getPoint()
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
            if (dragStart) {
                var dragEnd = PickerCommon.getPoint()

                if (dragStart.x > dragEnd.x) {
                    var x = 1
                } else {
                    var x = -1
                }

                if (dragStart.z > dragEnd.z) {
                    var y = 1
                } else {
                    var y = -1
                }
                //x = dragStart.x - dragEnd.x,
                //z = dragStart.z - dragEnd.z

                MiniMap.moveGround(x, y)
                dragStart = dragEnd
            }
        }
    }
    this.onContainerMouseUp = function (event) {
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        dragStart = 0
    }
}
