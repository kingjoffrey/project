var PickerGame = new function () {
    var dragStart = 0,
        clickStart = 0,
        move = 0,
        cursorMesh = 0,
        leftClick = 0,
        scaling = false,
        dist = 0,
        inCastle = false,
        handleDownStart = function (event) {
            leftClick = 1
            move = 0
            dragStart = PickerCommon.getPoint(event)
            PickerCommon.intersect(event)
        },
        handleUp = function (event) {
            dragStart = 0
            if (!leftClick) {
                return
            }
            if (!inCastle && !move && PickerCommon.intersects()) {
// Armia jest zaznaczona
                if (Me.getSelectedArmyId()) {
                    var x = PickerCommon.convertX(),
                        y = PickerCommon.convertZ()
// Klik na armię
                    if (Me.getSelectedArmy().getX() == x && Me.getSelectedArmy().getY() == y) {
                        StatusWindow.show()
// Move
                    } else if (clickStart && clickStart.x == x && clickStart.y == y) {
                        clickStart = 0
                        AStar.cursorPosition(x, y)
                        WebSocketSendGame.move()
// click & drag START
                    } else {
                        var x = PickerCommon.convertX(),
                            y = PickerCommon.convertZ()

                        clickStart = {x: x, y: y}
                        AStar.cursorPosition(x, y)
                        AStar.showPath()
                    }
// Brak zaznaczonej armii
                } else {
                    clickStart = 0
                    var field = PickerCommon.getField()
// Są armie na polu
                    if (field.hasArmies()) {
                        var armies = field.getArmies()
                        for (var armyId in armies) {
                            if (Me.colorEquals(armies[armyId])) {
// Zaznacz moją armię
                                Me.armyClick(armyId)
                            }
                        }
// Wejdź do zamku
                    } else if (Me.colorEquals(field.getCastleColor())) {
                        var castleId = field.getCastleId()
                        inCastle = true
                        CastleWindow.show(Me.getCastle(castleId))
                    }
                }
            } else if (inCastle) {
                PickerCommon.cursor('open')
                console.log('iun castle')
            }
        },
        handleMove = function (event) {
            PickerCommon.intersect(event)
            // if (AStar.cursorPosition(PickerCommon.convertX(), PickerCommon.convertZ()) && Me.getSelectedArmyId()) {
            //     AStar.showPath()
            // }

// nie w zamku
            if (!inCastle) {
                if (dragStart) {
                    var dragEnd = PickerCommon.getPoint(event)
                    if (!PickerCommon.checkOffset(dragStart, dragEnd)) {
// drag
                        move = 1
                        GameScene.moveCamera(dragStart.x - dragEnd.x, dragStart.y - dragEnd.y)
                        dragStart = dragEnd
                        PickerCommon.cursor('move')
                    }
                } else {
// Zmiana kursora
                    PickerGame.cursorChange()
                }
            }
        },
        changeCursorArmyMove = function (field) {
            GameModels.changeCursorColor('white')
            if (Me.getSelectedArmy().canFly()) {
// fly
                PickerCommon.cursor('fly')
            } else if (Me.getSelectedArmy().canSwim()) {
                if (field.getType() == 'w' || field.getType() == 'b') {
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
        }

    this.cursorChange = function () {
        if (PickerCommon.intersects()) {
            if (GameGui.getLock()) {
                return
            }

            var x = PickerCommon.convertX(), y = PickerCommon.convertZ()
            AStar.cursorPosition(x, y)
// turn
            if (Turn.isMy()) {
                var field = PickerCommon.getField(),
                    castleColor = field.getCastleColor(),
                    armies = field.getArmies()

                GameModels.cursorPosition(x, y, field.getType())

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
                                GameModels.changeCursorColor('white')
                                return
                            }
                            if (Me.colorEquals(armies[armyId])) {
// join my army
                                PickerCommon.cursor('join')
                                GameModels.changeCursorColor('white')
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
                        GameModels.changeCursorColor('red')
                    } else if (castleColor) {
// castle
                        if (Me.colorEquals(castleColor)) {
// enter my castle
                            PickerCommon.cursor('enter')
                            GameModels.changeCursorColor('white')
                        } else if (Me.sameTeam(castleColor)) {
// castle same team
                            changeCursorArmyMove(field)
                        } else {
// attack enemy castle
                            PickerCommon.cursor('attack')
                            GameModels.changeCursorColor('red')
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
                                GameModels.changeCursorColor('white')
                                return
                            }
                        }
// grab map
                        PickerCommon.cursor('grab')
                        GameModels.changeCursorColor('white')
                    } else if (castleColor && Me.colorEquals(castleColor)) {
// open my castle
                        PickerCommon.cursor('open')
                        GameModels.changeCursorColor('white')
                    } else {
// grab map
                        PickerCommon.cursor('grab')
                        GameModels.changeCursorColor('white')
                    }
                }
            }
        } else {
            PickerCommon.cursor(0)
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
                leftClick = 0
                Me.deselectArmy()
                break
        }
    }
    this.onContainerTouchStart = function (event) {
        if (event.touches.length === 2) {
            scaling = true
            dist = 0
            dragStart = 0
        } else {
            event.offsetX = event.changedTouches[0].clientX
            event.offsetY = event.changedTouches[0].clientY
            handleDownStart(event)
        }
    }
    this.onContainerMouseMove = function (event) {
        if (isTouchDevice()) {
            return
        }
        handleMove(event)
    }
    this.onContainerTouchMove = function (event) {
        if (scaling) {
            var tmp = Math.hypot(
                event.touches[0].pageX - event.touches[1].pageX,
                event.touches[0].pageY - event.touches[1].pageY
            )

            if (tmp < dist) {
                GameScene.moveCameraAway()
            } else {
                GameScene.moveCameraClose()
            }

            dist = tmp
        } else {
            event.offsetX = event.changedTouches[0].clientX
            event.offsetY = event.changedTouches[0].clientY

            handleMove(event)
        }
    }
    this.onContainerMouseUp = function (event) {
        if (isTouchDevice()) {
            return
        }
        //console.log('up')
        event.preventDefault()
        handleUp(event)
    }
    this.onContainerMouseOut = function (event) {
        event.preventDefault()
        // handleUp(event)
    }
    this.onContainerTouchEnd = function (event) {
        //console.log('touchEnd')
        if (scaling) {
            scaling = false
        } else {
            handleUp(event)
        }
    }
}
