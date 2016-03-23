var Picker = new function () {
    var dragStart = 0,
        touch = 0,
        click = 0

    this.onContainerMouseDown = function (event) {
        console.log('md1')
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
    this.onContainerTouchStart = function (event) {
        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY
        console.log('ts1')
        PickerCommon.intersect(event)
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
    }
    this.onContainerMouseMove = function (event) {
        console.log('mm1')
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
    this.onContainerTouchMove = function (event) {
        //console.log(event.changedTouches[0])
        console.log('tm1')

        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY

        PickerCommon.intersect(event)
        if (dragStart) {
            var dragEnd = Picker.getPoint()
            Scene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
            dragStart = dragEnd
        }
    }
    this.onContainerMouseUp = function (event) {
        console.log('up1')
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerTouchEnd = function (event) {
        dragStart = 0
    }
    this.getPoint = function () {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY
        return {x: x, y: y}
    }
}
