var Picker = new function () {
    var dragStart = 0

    this.onContainerMouseDown = function (event) {
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            switch (event.button) {
                case 0:
                    console.log('a')
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
                            dragStart = Picker.getPoint(event)
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
                var dragEnd = Picker.getPoint()
                Scene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
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
    this.getPoint = function () {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY
        return {x: x, y: y}
    }
}
