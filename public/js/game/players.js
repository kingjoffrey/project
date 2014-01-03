// *** PLAYERS ***

var Players = {
    center_x: 0,
    center_y: 0,
    stage: null,
    layer: null,
    length: 0,
    rotate: 0,
    wedges: {},
    anim: null,
    init: function () {
        this.stage = new Kinetic.Stage({
            container: 'playersCanvas',
            width: 180,
            height: 180
        });
        this.center_x = this.stage.getWidth() / 2
        this.center_y = this.stage.getHeight() / 2
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
        var angle = 360 / this.length;
        var r_angle = Math.PI * 2 / this.length

        var i = 0;
        for (shortName in players) {
            var start_angle = i * angle
            var r_start_angle = i * r_angle - Math.PI + r_angle

            if (Turn.color == shortName) {
                var x = this.center_x + Math.cos(r_start_angle) * 32
                var y = this.center_y + Math.sin(r_start_angle) * 32
            } else {
                var x = this.center_x
                var y = this.center_y
            }

            this.wedges[shortName] = {}
            this.wedges[shortName].kinetic = new Kinetic.Wedge({
                x: x,
                y: y,
                radius: 60,
                angleDeg: angle,
                fill: players[shortName].backgroundColor,
                stroke: 'none',
                strokeWidth: 0,
                rotationDeg: start_angle - 157.5
            });

            this.layer.add(this.wedges[shortName].kinetic);

            this.wedges[shortName].x = this.center_x + Math.cos(r_start_angle) * 77 - 11;
            this.wedges[shortName].y = this.center_y + Math.sin(r_start_angle) * 77 - 14;

            this.drawImage(shortName)
            i++;
        }
        this.stage.add(this.layer);
        this.drawTurn()
    },
    animate: function () {
        Players.anim = new Kinetic.Animation(function (frame) {
            Players.wedges[Turn.color].kinetic.move(0.1, 0.1)
            if (Players.wedges[Turn.color].kinetic.getPosition().x >= Players.center_x || Players.wedges[Turn.color].kinetic.getPosition().y >= Players.center_y) {
                Players.anim.stop()
            }
        }, this.layer);
        Players.anim.start()
    },
    rotate: function () {

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
            Players.stage.add(Players.layer);
        };
        imageObj.src = Hero.getImage(shortName)
    },
    drawSkull: function (x, y) {
        var img = new Image;
        img.src = '/img/game/skull_and_crossbones.png'
        img.onload = function () {
            Players.ctx.drawImage(img, x, y - 16)
        }
    },
    drawComputer: function (x, y) {
        var img = new Image;
        img.src = '/img/game/computer.png'
        img.onload = function () {
            Players.ctx.drawImage(img, x, y - 16)
        }
    },
    drawTurn: function () {
        var circle = new Kinetic.Circle({
            x: this.center_x,
            y: this.center_y,
            radius: 55,
            fill: players[Turn.color].backgroundColor,
            stroke: players[Turn.color].backgroundColor,
//            fill: 'grey',
//            stroke: 'grey',
            strokeWidth: 0
        });
        this.layer.add(circle);

        var turnNumber = new Kinetic.Text({
            x: this.center_x - 70,
            y: this.center_y - 15,
            text: Turn.number,
            fontSize: 30,
            fontFamily: 'fantasy',
            fill: players[Turn.color].textColor,
            width: 140,
            align: 'center'
        });
        this.layer.add(turnNumber);
        this.stage.add(this.layer);
    },
    turn: function () {
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