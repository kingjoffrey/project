var GamePlayers = new function () {
    var layer,
        center = {x: 90, y: 90},
        length = 0,
        wedges = {},
        kineticTurnNumber = null,
        circle = null,
        drawImage = function (shortName, skull) {
            var imageObj = new Image()
            imageObj.onload = function () {
                wedges[shortName].img = new Kinetic.Image({
                    x: wedges[shortName].x,
                    y: wedges[shortName].y,
                    image: imageObj,
                    width: 22,
                    height: 28
                });
                layer.add(wedges[shortName].img);
                layer.draw()
                if (isTruthful(skull)) {
                    GamePlayers.drawSkull(shortName)
                }
            }
            imageObj.src = Hero.getImage(shortName)
        }

    this.rotate = function (color, players) {
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
        }, layer)
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
            layer.add(wedges[shortName].skull);
            layer.draw()
        };
        imageObj.src = '/img/game/skull_and_crossbones.png'
    }
    this.drawTurn = function () {
        if (Game.getTurnsLimit()) {
            var turnNumber = Turn.getNumber() + '/' + Game.getTurnsLimit()
        }else{
            var turnNumber = Turn.getNumber()
        }
        if (kineticTurnNumber) {
            kineticTurnNumber.setText(turnNumber)
            layer.draw()
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
            })

            layer.add(kineticTurnNumber)
            layer.draw()
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
            layer.add(wedges[shortName].online);
        } else {
            wedges[shortName].online = new Kinetic.Circle({
                x: wedges[shortName].x,
                y: wedges[shortName].y,
                radius: 5,
                stroke: '#555',
                fill: 'grey',
                strokeWidth: 1
            });
            layer.add(wedges[shortName].online);
        }
        layer.draw()
    }
    this.initOnline = function (online) {
        for (var shortName in online) {
            this.setOnline(shortName, online[shortName])
        }
        layer.draw()
    }
    this.init = function (players) {
        length = Object.size(players) - 1

        var angle = 360 / length,
            r_angle = Math.PI * 2 / length,
            i = 0,
            stage = new Kinetic.Stage({
                container: 'playersCanvas',
                width: center.x * 2,
                height: center.y * 2
            })

        layer = new Kinetic.Layer()
        stage.add(layer)


        layer.add(new Kinetic.Circle({
            x: center.x,
            y: center.y,
            radius: 51,
            fill: 'grey',
            strokeWidth: 0
        }))
        layer.add(new Kinetic.Circle({
            x: center.x,
            y: center.y,
            radius: 90,
            fill: 'none',
            stroke: 'grey',
            strokeWidth: 1
        }))

        for (var shortName in players) {
            if (shortName == 'neutral') {
                continue
            }
            var player = Players.get(shortName),
                rotation = i * angle,
                r_rotation = i * r_angle + r_angle / 2

            wedges[shortName] = {
                x: center.x + Math.cos(r_rotation) * 70 - 11,
                y: center.y + Math.sin(r_rotation) * 70 - 14,
                rotation: r_rotation,
                kinetic: new Kinetic.Wedge({
                    x: center.x,
                    y: center.y,
                    radius: 50,
                    angleDeg: angle,
                    fill: Players.get(player.getTeam()).getBackgroundColor(),
                    strokeWidth: 0,
                    rotationDeg: rotation
                })
            }
            layer.add(wedges[shortName].kinetic)
            drawImage(shortName, Players.get(shortName).getLost())
            i++
        }
        layer.add(new Kinetic.Circle({
            x: center.x,
            y: center.y,
            radius: 40,
            fill: 'lightgrey',
            stroke: 'grey',
            strokeWidth: 1
        }))
        circle = new Kinetic.Circle({
            x: center.x,
            y: center.y,
            radius: 17,
            fill: 'grey',
            offset: [center.x - wedges[Turn.getColor()].x - 11, center.y - wedges[Turn.getColor()].y - 14],
            strokeWidth: 0
        })
        layer.add(circle)
        this.drawTurn()
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

