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

        this.length = Object.size(players);

        for (color in players) {
            players[color].active = 0;

            $('.' + color + ' .color').addClass(color + 'bg');

            for (i in players[color].armies) {
                Army.init(players[color].armies[i], color);
                if (color == my.color) {
                    for (s in players[color].armies[i].soldiers) {
                        my.costs += units[players[color].armies[i].soldiers[s].unitId].cost;
                    }
                    myArmies = true;
                } else {
                    enemyArmies = true;
                }
            }

            if (players[color].armies == "" && players[color].castles == "") {
                $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');
            }

            for (i in players[color].castles) {
                Castle.updateDefense(i, players[color].castles[i].defenseMod);
                Castle.owner(i, color);

                if (color == my.color) {
                    my.income += castles[i].income;
                    if (firstCastleId > i) {
                        firstCastleId = i;
                    }
                    myCastles = true;
                    Castle.initMyProduction(i);
                } else {
                    enemyCastles = true;
                }
            }
        }
        timer.start()
        this.draw()
    },
    draw: function () {
        this.angle = 360 / this.length;
        var r_angle = Math.PI * 2 / this.length

        var i = 0;
        for (shortName in players) {
            var rotation = i * this.angle
            var r_rotation = i * r_angle + r_angle / 2

            var x = this.centerX
            var y = this.centerY

            this.wedges[shortName] = {}
            this.wedges[shortName].x = this.centerX + Math.cos(r_rotation) * 77 - 11;
            this.wedges[shortName].y = this.centerY + Math.sin(r_rotation) * 77 - 14;
            this.wedges[shortName].rotation = r_rotation
            this.wedges[shortName].kinetic = new Kinetic.Wedge({
                x: x,
                y: y,
                radius: 55,
                angleDeg: this.angle,
                fill: players[shortName].backgroundColor,
                stroke: 'grey',
                strokeWidth: 2,
                rotationDeg: rotation
            });

            this.layer.add(this.wedges[shortName].kinetic);
            this.drawImage(shortName)
            if (players[shortName].lost) {
                this.drawSkull(shortName)
            }
            i++;
        }

        this.stage.add(this.layer);
        this.drawTurn()
        this.drawPlayerCircle()
    },
    animate: function (slide) {
        Players.anim = new Kinetic.Animation(function (frame) {
            var x = Math.cos(Players.wedges[Turn.color].rotation) * 0.1
            var y = Math.sin(Players.wedges[Turn.color].rotation) * 0.1
            console.log(x)
            console.log(y)
            Players.wedges[Turn.color].kinetic.move(-x, -y)
            if (Players.wedges[Turn.color].kinetic.getPosition().x == Players.centerX || Players.wedges[Turn.color].kinetic.getPosition().y == Players.centerY) {
                Players.anim.stop()
            }
        }, this.layer);
        Players.anim.start()
    },
    dc: function (angle) {
        var c = new Kinetic.Wedge({
            x: this.centerX,
            y: this.centerY,
            radius: 40,
            angle: angle,
            fill: 'orange',
            strokeWidth: 0
        });

        this.layer.add(c)
        this.layer.draw()
    },
    countAngle: function (p12, p13, p23) {
        return Math.acos((Math.pow(p12, 2) + Math.pow(p13, 2) - Math.pow(p23, 2)) / (2 * p12 * p13))
    },
    countVectorLength: function (x1, y1, x2, y2) {
        return Math.sqrt(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2))
    },
    rotate: function () {

        var angle = Math.atan2(Players.wedges['yellow'].x - Players.wedges['white'].x, Players.wedges['yellow'].y - Players.wedges['white'].y)
        this.dc(angle)
//        return

//        var p12 = this.countVectorLength(this.centerX, this.centerY, Players.wedges['yellow'].x, Players.wedges['yellow'].y)
//        var p13 = this.countVectorLength(this.centerX, this.centerY, Players.wedges['white'].x, Players.wedges['white'].y)
//        var p23 = this.countVectorLength(Players.wedges['white'].x, Players.wedges['white'].y, Players.wedges['yellow'].x, Players.wedges['yellow'].y)

//        var angle = this.countAngle(p12, p13, p23)
        if (angle < 0) {
            angle = 2 * Math.PI + angle
        }
        console.log(angle)
//        this.dc(angle)
//        return

        var angularSpeed = Math.PI / 2
        var currentAngle = 0;
        var anim = new Kinetic.Animation(function (frame) {
            var angleDiff = frame.timeDiff * angularSpeed / 1000;
            currentAngle += angleDiff
            if (currentAngle >= angle) {
                Players.circle.rotate(angle - (currentAngle - angleDiff))
                anim.stop()

                console.log(currentAngle)
                console.log(angle - (currentAngle - angleDiff))
            } else {
                Players.circle.rotate(angleDiff)
            }
        }, Players.layer)
        anim.start()
    },
    drawImage: function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            var img = new Kinetic.Image({
                x: Players.wedges[shortName].x,
                y: Players.wedges[shortName].y,
                image: imageObj,
                width: 22,
                height: 28
            });
            Players.layer.add(img);
            Players.layer.draw()
        };
        imageObj.src = Hero.getImage(shortName)
    },
    drawSkull: function (shortName) {
        var imageObj = new Image();
        imageObj.onload = function () {
            var img = new Kinetic.Image({
                x: Players.wedges[shortName].x + 3,
                y: Players.wedges[shortName].y + 7,
                image: imageObj,
                width: 16,
                height: 16
            });
            Players.layer.add(img);
            Players.layer.draw()
        };
        imageObj.src = '/img/game/skull_and_crossbones.png'
    },
    drawPlayerCircle: function () {
        this.circle = new Kinetic.Circle({
            x: this.centerX,
            y: this.centerY,
            radius: 18,
            fill: 'grey',
            offset: [this.centerX - Players.wedges[Turn.color].x - 11, this.centerY - Players.wedges[Turn.color].y - 14],
            strokeWidth: 0
        });

        this.layer.add(this.circle)
    },
    drawTurn: function () {
        if (this.turnCircle) {
            this.turnCircle.setFill(players[Turn.color].backgroundColor)
            this.turnNumber.setFill(players[Turn.color].textColor)
            this.turnNumber.setText(Turn.number)
            this.layer.draw()
        } else {
            this.turnCircle = new Kinetic.Circle({
                x: this.centerX,
                y: this.centerY,
                radius: 50,
                fill: players[Turn.color].backgroundColor,
                strokeWidth: 0
            });

            this.layer.add(this.turnCircle);

            this.turnNumber = new Kinetic.Text({
                x: this.centerX - 70,
                y: this.centerY - 15,
                text: Turn.number,
                fontSize: 30,
                fontFamily: 'fantasy',
                fill: players[Turn.color].textColor,
                width: 140,
                align: 'center'
            });

            this.layer.add(this.turnNumber);
            this.layer.draw()
        }
    },
    degreesFromTwelveOclock: function (x, y) {
        // calculate the angle(theta)
        var theta = Math.atan2(y - this.centerY, x - this.centerX);
        // be sure theta is positive
        if (theta < 0) {
            theta += 2 * Math.PI
        }
        ;
        // convert to degrees and rotate so 0 degrees = 12 o'clock
        var degrees = (theta * 180 / Math.PI + 90) % 360;
        return (degrees);
    },
    radians: function (x, y) {
        // calculate the angle(theta)
        var theta = Math.atan2(y - this.centerY, x - this.centerX);
        // be sure theta is positive
        if (theta < 0) {
            theta += 2 * Math.PI
        }
        return theta
    }
//    turn: function () {
//        this.drawTurn()
//    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

