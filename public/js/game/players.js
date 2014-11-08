// *** PLAYERS ***

var Players = {
    centerX: 0,
    centerY: 0,
    stage: null,
    layer: null,
    length: 0,
    rotate: 0,
    wedges: {},
    anim: null,
    turnCircle: null,
    turnNumber: null,
    init: function () {
        this.stage = new Kinetic.Stage({
            container: 'playersCanvas',
            width: 180,
            height: 180
        });
        this.centerX = this.stage.getWidth() / 2
        this.centerY = this.stage.getHeight() / 2
        this.layer = new Kinetic.Layer();

        this.length = Object.size(game.players);

        for (color in game.players) {
            //game.players[color].active = 0;
            if (!game.players[color].computer) {
                game.online[color] = 0
            }

            $('.' + color + ' .color').addClass(color + 'bg');

            for (i in game.players[color].armies) {
                Army.init(game.players[color].armies[i], color);
                if (color == game.me.color) {
                    for (s in game.players[color].armies[i].soldiers) {
                        my.costs += units[game.players[color].armies[i].soldiers[s].unitId].cost;
                    }
                    myArmies = true;
                } else {
                    enemyArmies = true;
                }
            }

            for (i in game.players[color].castles) {
                //Castle.updateDefense(i, game.players[color].castles[i].defenseMod);
                //Castle.owner(i, color);

                if (color == game.me.color) {
                    game.me.income += game.players[color].castles[i].income;
                    if (firstCastleId > i) {
                        firstCastleId = i;
                    }
                    myCastles = true;
                    //Castle.initMyProduction(i);
                } else {
                    enemyCastles = true;
                }
            }
        }

        timer.start()
        this.draw()

        return 1
    },
    draw: function () {
        this.angle = 360 / this.length;
        var r_angle = Math.PI * 2 / this.length

        var circle = new Kinetic.Circle({
            x: this.centerX,
            y: this.centerY,
            radius: 51,
            fill: 'grey',
            strokeWidth: 0
        })
        this.layer.add(circle)
        var circle = new Kinetic.Circle({
            x: this.centerX,
            y: this.centerY,
            radius: 90,
            fill: 'none',
            stroke: 'grey',
            strokeWidth: 1
        })
        this.layer.add(circle)

        var i = 0;
        for (shortName in game.players) {
            var rotation = i * this.angle
            var r_rotation = i * r_angle + r_angle / 2

            this.wedges[shortName] = {}
            this.wedges[shortName].x = this.centerX + Math.cos(r_rotation) * 70 - 11;
            this.wedges[shortName].y = this.centerY + Math.sin(r_rotation) * 70 - 14;
            this.wedges[shortName].rotation = r_rotation
            this.wedges[shortName].kinetic = new Kinetic.Wedge({
                x: this.centerX,
                y: this.centerY,
                radius: 50,
                angleDeg: this.angle,
                fill: game.players[game.players[shortName].team].backgroundColor,
                strokeWidth: 0,
                rotationDeg: rotation
            })
            this.layer.add(this.wedges[shortName].kinetic)
            this.drawImage(shortName)
            if (game.players[shortName].lost) {
                this.drawSkull(shortName)
            }
            i++;
        }

        var turnCircle = new Kinetic.Circle({
            x: this.centerX,
            y: this.centerY,
            radius: 40,
            fill: 'lightgrey',
            stroke: 'grey',
            strokeWidth: 1
        })
        this.layer.add(turnCircle);

        this.stage.add(this.layer);
        this.drawTurn()
        this.drawPlayerCircle()
    },
    rotate: function (color) {
        var i = 0,
            start = 0,
            end = 0

        for (shortName in game.players) {
            i++
            if (color == shortName) {
                end = i
            }
            if (Turn.color == shortName) {
                start = i
            }
        }

        var angle = (end - start) * (Math.PI * 2 / this.length)

        if (angle < 0) {
            angle = 2 * Math.PI + angle
        }

        var angularSpeed = Math.PI / 2
        var currentAngle = 0;
        var anim = new Kinetic.Animation(function (frame) {
            var angleDiff = frame.timeDiff * angularSpeed / 1000;
            currentAngle += angleDiff
            if (currentAngle >= angle) {
                Players.circle.rotate(angle - (currentAngle - angleDiff))
                anim.stop()
            } else {
                Players.circle.rotate(angleDiff)
            }
        }, Players.layer)
        anim.start()
    },
    drawImage: function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            Players.wedges[shortName].img = new Kinetic.Image({
                x: Players.wedges[shortName].x,
                y: Players.wedges[shortName].y,
                image: imageObj,
                width: 22,
                height: 28
            });
            Players.layer.add(Players.wedges[shortName].img);
            Players.layer.draw()
        };
        imageObj.src = Hero.getImage(shortName)
    },
    drawSkull: function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            Players.wedges[shortName].skull = new Kinetic.Image({
                x: Players.wedges[shortName].x + 3,
                y: Players.wedges[shortName].y + 7,
                image: imageObj,
                width: 16,
                height: 16
            })
            Players.wedges[shortName].img.remove()
            Players.layer.add(Players.wedges[shortName].skull);
            Players.layer.draw()
        };
        imageObj.src = '/img/game/skull_and_crossbones.png'
    },
    drawPlayerCircle: function () {
        this.circle = new Kinetic.Circle({
            x: this.centerX,
            y: this.centerY,
            radius: 17,
            fill: 'grey',
            offset: [this.centerX - Players.wedges[Turn.color].x - 11, this.centerY - Players.wedges[Turn.color].y - 14],
            strokeWidth: 0
        });

        this.layer.add(this.circle)
    },
    drawTurn: function () {
        var turnNumber = Turn.number
        if (game.turnsLimit) {
            turnNumber = Turn.number + '/' + game.turnsLimit
        }
        if (this.turnNumber) {
            this.turnNumber.setText(turnNumber)
            this.layer.draw()
        } else {
            this.turnNumber = new Kinetic.Text({
                x: this.centerX - 70,
                y: this.centerY - 15,
                text: turnNumber,
                fontSize: 30,
                fontFamily: 'fantasy',
                fill: 'graphite',
                width: 140,
                align: 'center'
            });

            this.layer.add(this.turnNumber);
            this.layer.draw()
        }
    },
    updateOnline: function () {
        for (shortName in game.online) {
            if (!game.online[shortName]) {
                if (isSet(Players.wedges[shortName].online)) {
                    Players.wedges[shortName].online.remove()
                }
                continue
            }

            if (isSet(Players.wedges[shortName].online)) {
                continue
            }

            Players.wedges[shortName].online = new Kinetic.Circle({
                x: this.centerX,
                y: this.centerY,
                radius: 18,
                offset: [this.centerX - Players.wedges[shortName].x - 11, this.centerY - Players.wedges[shortName].y - 14],
                stroke: '#0f0',
                strokeWidth: 1
            });
            Players.layer.add(Players.wedges[shortName].online);
        }
        this.layer.draw()
    },
    isMy: function () {
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

