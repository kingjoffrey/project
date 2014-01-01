// *** PLAYERS ***

var Players = {
    circle_center_x: 90,
    circle_center_y: 90,
    canvas: null,
    ctx: null,
    length: 0,
    rotate: 0,
    init: function () {
        this.canvas = $('#playersCanvas');
        this.ctx = this.canvas[0].getContext('2d');
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
        this.ctx.clearRect(0, 0, 180, 180)

        var r_length = 60;
        var r_angle = Math.PI * 2 / this.length;

        var i = 0;
        for (shortName in players) {
            this.ctx.beginPath();
            var r_start_angle = i * r_angle + this.rotate + Math.PI;
            console.log(r_start_angle)
            var r_end_angle = r_start_angle + r_angle + this.rotate + Math.PI;
            console.log(r_end_angle)
            var x = this.circle_center_x + Math.cos(r_start_angle) * r_length;
            var y = this.circle_center_y + Math.sin(r_start_angle) * r_length;

            this.ctx.moveTo(this.circle_center_x, this.circle_center_y);
            this.ctx.lineTo(x, y);
            this.ctx.arc(this.circle_center_x, this.circle_center_y, r_length, r_start_angle, r_end_angle, false);
            this.ctx.lineTo(this.circle_center_x, this.circle_center_y);
            this.ctx.fillStyle = players[shortName].backgroundColor;
            this.ctx.fill();

            var x = this.circle_center_x + Math.cos(r_start_angle + r_angle / 2) * 70;
            var y = this.circle_center_y + Math.sin(r_start_angle + r_angle / 2) * 70;
            this.drawImage(shortName, x, y)
//            this.drawSkull(x, y)
            i++;
        }
        this.rotate += r_angle / this.length
        this.drawTurn()
    },
    animate: function (time) {
        if (this.rotate > 1) {
            return
        }
        Players.draw(time);
        requestAnimationFrame(Players.animate)
    },
    drawImage: function (shortName, x, y) {
//        if (players[shortName].computer) {
//            img.src = '/img/game/computer.png';
//        } else {
//        }
        var img = new Image;
        img.src = Hero.getImage(shortName)
        img.onload = function () {
            Players.ctx.drawImage(img, x - 22, y - 14)
        }
    },
    drawSkull: function (x, y) {
        var img = new Image;
        img.src = '/img/game/skull_and_crossbones.png'
        img.onload = function () {
            Players.ctx.drawImage(img, x, y-16)
        }
    },
    drawTurn: function () {
        this.ctx.beginPath();
        this.ctx.arc(this.circle_center_x, this.circle_center_y, 50, 0, Math.PI * 2, true);
        this.ctx.fillStyle = players[Turn.color].backgroundColor;
        this.ctx.fill();
    },
    turn: function () {
        this.drawTurn()
        $('#turnNumber').css('color', players[Turn.color].textColor).html(Turn.number);
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};