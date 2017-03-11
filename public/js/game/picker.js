var PickerGame = new function () {
    var dragStart = 0,
        clickStart = 0,
        handleDownStart = function (event) {
            PickerCommon.intersect(event)
            if (PickerCommon.intersects()) {
                if (Me.getSelectedArmyId()) {
                    var x = PickerCommon.convertX(),
                        y = PickerCommon.convertZ()
                    if (Me.getSelectedArmy().getX() == x && Me.getSelectedArmy().getY() == y) {
                        StatusWindow.show()
                    } else if (clickStart && clickStart.x == x && clickStart.y == y) {
                        clickStart = 0
                        AStar.cursorPosition(x, y)
                        WebSocketSendGame.move()
                    } else {
                        clickStart = {x: x, y: y}
                        AStar.cursorPosition(x, y)
                        AStar.showPath()
                    }
                    dragStart = PickerCommon.getPoint(event)
                } else {
                    clickStart = 0
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
                                WebSocketSendGame.production(Me.getSelectedCastleId(), Me.getSelectedUnitId(), castleId)
                            }
                            Me.setSelectedCastleId(null)
                            Me.setSelectedUnitId(null)
                        } else {
                            CastleWindow.show(Me.getCastle(castleId))
                        }
                    } else {
                        dragStart = PickerCommon.getPoint(event)
                    }
                }
            } else {
                dragStart = PickerCommon.getPoint(event)
            }
        },
        changeCursorArmyMove = function (field) {
            if (Me.getSelectedArmy().canFly()) {
// fly
                PickerCommon.cursor('fly')
            } else if (Me.getSelectedArmy().canSwim()) {
                if (field.getType() == 'w') {
// swim
                    PickerCommon.cursor('swim')
                } else {
// can't swim
                    PickerCommon.cursor('impenetrable')
                }
            } else {
                if (field.getType() == 'w' || field.getType() == 'm') {
// can't walk
                    PickerCommon.cursor('impenetrable')
                } else {
// walk
                    PickerCommon.cursor('walk')
                }
            }
        },
        handleMove = function (event) {
            PickerCommon.intersect(event)
            // if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && Me.getSelectedArmyId()) {
            //     AStar.showPath()
            // }

            if (dragStart) {
// drag
                GameRenderer.shadowsOff()
                var dragEnd = PickerCommon.getPoint(event)
                GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                dragStart = dragEnd
                PickerCommon.cursor('move')
            } else {
                if (PickerCommon.intersects()) {
                    AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ())
// turn
                    if (Turn.isMy()) {
                        var field = PickerCommon.getField(),
                            castleColor = field.getCastleColor(),
                            armies = field.getArmies()

                        for (var armyId in armies) {
                            var hasArmy = 1
                            break
                        }

                        if (Me.getSelectedArmyId()) {
// selected
                            if (hasArmy) {
// armies
                                for (var armyId in armies) {
                                    if (Me.getSelectedArmyId() == armyId) {
// split my army
                                        PickerCommon.cursor('split')
                                        return
                                    }
                                    if (Me.colorEquals(armies[armyId])) {
// join my army
                                        PickerCommon.cursor('join')
                                        return
                                    }
                                    if (Me.sameTeam(armies[armyId])) {
// army same team
                                        changeCursorArmyMove(field)
                                        return
                                    }
                                }
// attack enemy army
                                PickerCommon.cursor('attack')
                            } else if (castleColor) {
// castle
                                if (Me.colorEquals(castleColor)) {
// enter my castle
                                    PickerCommon.cursor('enter')
                                } else if (Me.sameTeam(castleColor)) {
// castle same team
                                    changeCursorArmyMove(field)
                                } else {
// attack enemy castle
                                    PickerCommon.cursor('attack')
                                }
                            } else {
// map
                                changeCursorArmyMove(field)
                            }
                        } else {
// not selected
                            if (hasArmy) {
                                for (var armyId in armies) {
                                    if (Me.colorEquals(armies[armyId])) {
// select my army
                                        PickerCommon.cursor('select')
                                        return
                                    }
                                }
// grab map
                                PickerCommon.cursor('grab')
                            } else if (castleColor && Me.colorEquals(castleColor)) {
// open my castle
                                PickerCommon.cursor('open')
                            } else {
// grab map
                                PickerCommon.cursor('grab')
                            }
                        }
                    } else {
// wait
                        PickerCommon.cursor('wait')
                    }
                } else {
                    PickerCommon.cursor()
                }
            }
        }

    this.onContainerMouseDown = function (event) {
        if (isTouchDevice()) {
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
                Me.deselectArmy()
                break
        }
    }
    this.onContainerTouchStart = function (event) {
        event.offsetX = event.changedTouches[0].clientX
        event.offsetY = event.changedTouches[0].clientY

        handleDownStart(event)
    }
    this.onContainerMouseMove = function (event) {
        if (isTouchDevice()) {
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
        if (isTouchDevice()) {
            return
        }
        //console.log('up')
        event.preventDefault()
        dragStart = 0
        GameRenderer.shadowsOn()
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        dragStart = 0
        GameRenderer.shadowsOn()
    }
    this.onContainerTouchEnd = function (event) {
        //console.log('touchEnd')
        dragStart = 0
        GameRenderer.shadowsOn()
    }
}
