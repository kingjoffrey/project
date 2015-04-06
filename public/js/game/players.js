var Players = new function () {
    var center = {x: 0, y: 0},
        kineticStage = null,
        kineticLayer = null,
        length = 0,
        wedges = {},
        kineticTurnNumber = null,
        circle = null,
        players = {},
        draw = function () {
            var angle = 360 / length;
            var r_angle = Math.PI * 2 / length

            var circle = new Kinetic.Circle({
                x: center.x,
                y: center.y,
                radius: 51,
                fill: 'grey',
                strokeWidth: 0
            })
            kineticLayer.add(circle)
            var circle = new Kinetic.Circle({
                x: center.x,
                y: center.y,
                radius: 90,
                fill: 'none',
                stroke: 'grey',
                strokeWidth: 1
            })
            kineticLayer.add(circle)

            var i = 0;
            for (var shortName in players) {
                if (shortName == 'neutral') {
                    continue
                }
                var player = Players.get(shortName)
                var rotation = i * angle
                var r_rotation = i * r_angle + r_angle / 2

                wedges[shortName] = {}
                wedges[shortName].x = center.x + Math.cos(r_rotation) * 70 - 11;
                wedges[shortName].y = center.y + Math.sin(r_rotation) * 70 - 14;
                wedges[shortName].rotation = r_rotation
                wedges[shortName].kinetic = new Kinetic.Wedge({
                    x: center.x,
                    y: center.y,
                    radius: 50,
                    angleDeg: angle,
                    fill: Players.get(player.getTeam()).getBackgroundColor(),
                    strokeWidth: 0,
                    rotationDeg: rotation
                })
                kineticLayer.add(wedges[shortName].kinetic)
                drawImage(shortName, Players.get(shortName).getLost())
                i++;
            }

            var kineticTurnCircle = new Kinetic.Circle({
                x: center.x,
                y: center.y,
                radius: 40,
                fill: 'lightgrey',
                stroke: 'grey',
                strokeWidth: 1
            })
            kineticLayer.add(kineticTurnCircle);

            kineticStage.add(kineticLayer);
            Players.drawTurn()
            drawPlayerCircle()
        },
        drawImage = function (shortName, skull) {
            var imageObj = new Image();
            imageObj.onload = function () {
                wedges[shortName].img = new Kinetic.Image({
                    x: wedges[shortName].x,
                    y: wedges[shortName].y,
                    image: imageObj,
                    width: 22,
                    height: 28
                });
                kineticLayer.add(wedges[shortName].img);
                kineticLayer.draw()
                if (isTruthful(skull)) {
                    Players.drawSkull(shortName)
                }
            }
            imageObj.src = Hero.getImage(shortName)
        },
        drawPlayerCircle = function () {
            circle = new Kinetic.Circle({
                x: center.x,
                y: center.y,
                radius: 17,
                fill: 'grey',
                offset: [center.x - wedges[Turn.getColor()].x - 11, center.y - wedges[Turn.getColor()].y - 14],
                strokeWidth: 0
            });
            kineticLayer.add(circle)
        }

    this.rotate = function (color) {
        var i = 0,
            start = 0,
            end = 0

        for (var shortName in players) {
            i++
            if (color == shortName) {
                end = i
            }
            if (Turn.getColor() == shortName) {
                start = i
            }
        }

        var angle = (end - start) * (Math.PI * 2 / length)

        if (angle < 0) {
            angle = 2 * Math.PI + angle
        }

        var angularSpeed = Math.PI / 2
        var currentAngle = 0;
        var animation = new Kinetic.Animation(function (frame) {
            var angleDiff = frame.timeDiff * angularSpeed / 1000;
            currentAngle += angleDiff
            if (currentAngle >= angle) {
                circle.rotate(angle - (currentAngle - angleDiff))
                animation.stop()
            } else {
                circle.rotate(angleDiff)
            }
        }, kineticLayer)
        animation.start()
    }

    this.hasSkull = function (shortName) {
        return isSet(wedges[shortName].skull)
    }
    this.drawSkull = function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            wedges[shortName].skull = new Kinetic.Image({
                x: wedges[shortName].x + 3,
                y: wedges[shortName].y + 7,
                image: imageObj,
                width: 16,
                height: 16
            })
            kineticLayer.add(wedges[shortName].skull);
            kineticLayer.draw()
        };
        imageObj.src = '/img/game/skull_and_crossbones.png'
    }

    this.drawTurn = function () {
        var turnNumber = Turn.getNumber()
        if (Game.getTurnsLimit()) {
            turnNumber = Turn.getNumber() + '/' + Game.getTurnsLimit()
        }
        if (kineticTurnNumber) {
            kineticTurnNumber.setText(turnNumber)
            kineticLayer.draw()
        } else {
            kineticTurnNumber = new Kinetic.Text({
                x: center.x - 70,
                y: center.y - 15,
                text: turnNumber,
                fontSize: 30,
                fontFamily: 'fantasy',
                fill: 'graphite',
                width: 140,
                align: 'center'
            });

            kineticLayer.add(kineticTurnNumber);
            kineticLayer.draw()
        }
    }
    this.setOnline = function (shortName, value) {
        if (isSet(wedges[shortName].online)) {
            wedges[shortName].online.remove()
        }
        if (value) {
            wedges[shortName].online = new Kinetic.Circle({
                x: wedges[shortName].x,
                y: wedges[shortName].y,
                radius: 5,
                stroke: '#0f0',
                fill: 'green',
                strokeWidth: 1
            });
            kineticLayer.add(wedges[shortName].online);
        } else {
            wedges[shortName].online = new Kinetic.Circle({
                x: wedges[shortName].x,
                y: wedges[shortName].y,
                radius: 5,
                stroke: '#555',
                fill: 'grey',
                strokeWidth: 1
            });
            kineticLayer.add(wedges[shortName].online);
        }
        kineticLayer.draw()
    }
    this.initOnline = function (online) {
        for (var shortName in online) {
            this.setOnline(shortName, online[shortName])
        }
        kineticLayer.draw()
    }
    this.init = function (players) {
        kineticStage = new Kinetic.Stage({
            container: 'playersCanvas',
            width: 180,
            height: 180
        })
        kineticLayer = new Kinetic.Layer()
        center.x = kineticStage.getWidth() / 2
        center.y = kineticStage.getHeight() / 2

        length = Object.size(players) - 1

        for (var color in players) {
            this.add(color, players[color])
        }

        draw(players)
    }
    this.add = function (color, player) {
        players[color] = new Player(player, color)
    }
    /**
     *
     * @param color
     * @returns Player
     */
    this.get = function (color) {
        return players[color]
    }
    this.count = function () {
        return Object.size(players) - 1
    }
    this.countHumans = function () {
        var numberOfHumans = 0
        for (var color in players) {
            if (color == 'neutral') {
                continue
            }
            var player = this.get(color)
            if (!player.isComputer()) {
                numberOfHumans++
            }
        }
        return numberOfHumans;
    }
    this.toArray = function () {
        return players
    }
    this.showFirst = function (color, func) {
        var castleId = Game.getCapitalId(color),
            firstCastleId,
            player = this.get(color),
            castles = player.getCastles()

        if (castles.has(castleId)) {
            var castle = castles.get(castleId)
            Zoom.lens.setcenter(castle.getX(), castle.getY(), func)
        } else if (firstCastleId = castles.getFirsCastleId()) {
            var castle = castles.get(firstCastleId)
            Zoom.lens.setcenter(castle.getX(), castle.getY(), func)
        } else {
            var armies = player.getArmies()
            for (var armyId in armies.toArray()) {
                var army = armies.get(armyId)
                Zoom.lens.setcenter(army.getX(), army.getY(), func)
                break
            }
        }
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

