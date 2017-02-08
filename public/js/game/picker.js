var PickerGame = new function () {
    var dragStart = 0,
        clickStart = 0,
        handleDownStart = function (event) {
            PickerCommon.intersect(event)
            if (PickerCommon.intersects()) {
                if (CommonMe.getSelectedArmyId()) {
                    var x = PickerCommon.convertX(),
                        y = PickerCommon.convertZ()
                    if (CommonMe.getSelectedArmy().getX() == x && CommonMe.getSelectedArmy().getY() == y) {
                        SplitWindow.show()
                    } else if (clickStart && clickStart.x == x && clickStart.y == y) {
                        clickStart = 0
                        AStar.cursorPosition(x, y)
                        WebSocketSendGame.move()
                    } else {
                        clickStart = {x: x, y: y}
                        AStar.cursorPosition(x, y)
                        AStar.showPath()
                    }
                    dragStart = PickerGame.getPoint(event)
                } else {
                    clickStart = 0
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
                                WebSocketSendGame.production(CommonMe.getSelectedCastleId(), CommonMe.getSelectedUnitId(), castleId)
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
        },
        handleMove = function (event) {
            PickerCommon.intersect(event)
            if (PickerCommon.intersects()) {
                // if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && CommonMe.getSelectedArmyId()) {
                //     AStar.showPath()
                // }

                var field = PickerCommon.getField(),
                    castleId = field.getCastleId(),
                    armies = field.getArmies()

                if (CommonMe.getSelectedArmyId()) {
                    if (castleId && CommonMe.hasCastle(castleId)) {
                        $('canvas').css('cursor', 'pointer')
                    } else {
                    }
                } else if (castleId) {
                    if (CommonMe.hasCastle(castleId)) {

                    }else{

                    }
                    $('canvas').css('cursor', 'pointer')

                }


                if (dragStart) {
                    var dragEnd = PickerGame.getPoint(event)
                    GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                    dragStart = dragEnd
                }
            }
        }

    this.onContainerMouseDown = function (event) {
        if (Page.hasTouch()) {
            return
        }
        //console.log('down')
        switch (event.button) {
            case 0:
                handleDownStart(event)
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

        handleDownStart(event)
    }
    this.onContainerMouseMove = function (event) {
        if (Page.hasTouch()) {
            return
        }
        handleMove(event)
    }
    this.onContainerTouchMove = function (event) {
        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY

        handleMove(event)
    }
    this.onContainerMouseUp = function (event) {
        if (Page.hasTouch()) {
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
    /**
     *
     * @returns {{x: Number, y: Number}}
     */
    this.getPoint = function (event) {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX,
            y = event.offsetY == undefined ? event.layerY : event.offsetY
        return {x: x, y: y}
    }
}
