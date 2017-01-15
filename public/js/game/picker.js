var PickerGame = new function () {
    var dragStart = 0

    this.onContainerMouseDown = function (event) {
        if (Game.hasTouch()) {
            return
        }
        //console.log('down')
        switch (event.button) {
            case 0:
                PickerCommon.intersect(event)
                if (PickerCommon.intersects()) {
                    if (CommonMe.getSelectedArmyId()) {
                        WebSocketSendCommon.move()
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
                                    WebSocketSendCommon.production(CommonMe.getSelectedCastleId(), CommonMe.getSelectedUnitId(), castleId)
                                }
                                CommonMe.setSelectedCastleId(null)
                                CommonMe.setSelectedUnitId(null)
                            } else {
                                CastleWindow.show(CommonMe.getCastle(castleId))
                            }
                        } else {
                            dragStart = PickerGame.getPoint(event)
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
    this.onContainerTouchStart = function (event) {
        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY
        //console.log('touchStart')
        PickerCommon.intersect(event)
        if (CommonMe.getSelectedArmyId()) {
            AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ())
            WebSocketSendCommon.move()
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
                        WebSocketSendCommon.production(CommonMe.getSelectedCastleId(), CommonMe.getSelectedUnitId(), castleId)
                    }
                    CommonMe.setSelectedCastleId(null)
                    CommonMe.setSelectedUnitId(null)
                } else {
                    CastleWindow.show(CommonMe.getCastle(castleId))
                }
            } else {
                dragStart = PickerGame.getPoint(event)
            }
        }
    }
    this.onContainerMouseMove = function (event) {
        if (Game.hasTouch()) {
            return
        }
        //console.log('mm')
        PickerCommon.intersect(event)
        if (PickerCommon.intersects()) {
            if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && CommonMe.getSelectedArmyId()) {
                AStar.showPath()
            }
            if (dragStart) {
                var dragEnd = PickerGame.getPoint()
                GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                dragStart = dragEnd
            }
        }
    }
    this.onContainerTouchMove = function (event) {
        //console.log('touchMove')
        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY

        PickerCommon.intersect(event)
        if (dragStart) {
            var dragEnd = PickerGame.getPoint()
            GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
            dragStart = dragEnd
        }
    }
    this.onContainerMouseUp = function (event) {
        if (Game.hasTouch()) {
            return
        }
        //console.log('up')
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        dragStart = 0
    }
    this.onContainerTouchEnd = function (event) {
        //console.log('touchEnd')
        dragStart = 0
    }
    this.getPoint = function () {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY
        return {x: x, y: y}
    }
}
