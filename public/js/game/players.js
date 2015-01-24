var Players = new function () {
    var center = {x: 0, y: 0},
        kineticStage = null,
        kineticLayer = null,
        length = 0,
        rotate = 0,
        wedges = {},
        kineticTurnNumber = null,
        circle = null,
        players = {}

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
            //if (!players[color].computer && color != 'neutral') {
            //    game.online[color] = 0
            //}

            //$('.' + color + ' .color').addClass(color + 'bg');

            this.add(color, players[color])
        }

        draw(players)

        return 1
    }
    this.add = function (color, player) {
        players[color] = new Player(player)
    }
    /**
     *
     * @param color
     * @returns Player
     */
    this.get = function (color) {
        return players[color]
    }
    var draw = function () {
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
            drawImage(shortName)
            if (players[shortName].lost) {
                drawSkull(shortName)
            }
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
        drawTurn()
        drawPlayerCircle()
    }

    var rotate = function (color) {
        var i = 0,
            start = 0,
            end = 0

        for (shortName in players) {
            i++
            if (color == shortName) {
                end = i
            }
            if (Turn.color == shortName) {
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

    var drawImage = function (shortName) {
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
        };
        imageObj.src = Hero.getImage(shortName)
    }

    var drawSkull = function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            wedges[shortName].skull = new Kinetic.Image({
                x: wedges[shortName].x + 3,
                y: wedges[shortName].y + 7,
                image: imageObj,
                width: 16,
                height: 16
            })
            wedges[shortName].img.remove()
            kineticLayer.add(wedges[shortName].skull);
            kineticLayer.draw()
        };
        imageObj.src = '/img/game/skull_and_crossbones.png'
    }

    var drawPlayerCircle = function () {
        circle = new Kinetic.Circle({
            x: center.x,
            y: center.y,
            radius: 17,
            fill: 'grey',
            offset: [center.x - wedges[Turn.color].x - 11, center.y - wedges[Turn.color].y - 14],
            strokeWidth: 0
        });

        kineticLayer.add(circle)
    }

    var drawTurn = function () {
        var turnNumber = Turn.number
        if (game.turnsLimit) {
            turnNumber = Turn.number + '/' + game.turnsLimit
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

    var updateOnline = function () {
        for (shortName in game.online) {
            if (isSet(wedges[shortName].online)) {
                wedges[shortName].online.remove()
            }

            wedges[shortName].online = new Kinetic.Circle({
                x: center.x,
                y: center.y,
                radius: 18,
                offset: [center.x - wedges[shortName].x - 11, center.y - wedges[shortName].y - 14],
                stroke: '#0f0',
                strokeWidth: 1
            });
            kineticLayer.add(wedges[shortName].online);
        }
        kineticLayer.draw()
    }

    var isMy = function () {
        for (color in game.online) {
            if (game.online[color]) {
                if (game.me.color == color) {
                    return 1
                } else {
                    return 0
                }
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

