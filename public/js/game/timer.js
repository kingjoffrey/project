var Timer = new function () {
    var height = 32,
        timestamp = 0,
        difference1 = 0,
        difference2 = 0,
        elHour1 = null,
        elMinute1 = null,
        elSecond1 = null,
        hour1 = 0,
        minute1 = 0,
        second1 = 0,
        elHour2 = null,
        elMinute2 = null,
        elSecond2 = null,
        hour2 = 0,
        minute2 = 0,
        second2 = 0,
        gameBegin = 0,
        turnTimeL,
        timeL

    this.init = function (begin,turnTimeL, timeL) {
        gameBegin = Date.parse(begin.substr(0, 19)).getTime()
        $('#turnTimeLimit2').html(turnTimeLimit[turnTimeL])
        $('#timeLimit2').html(timeLimits[timeL])
        $('#timerScroll').css('height', Players.count() * height + 'px');
        elHour1 = $('#turnTimeLimit #hour')
        elMinute1 = $('#turnTimeLimit #minute')
        elSecond1 = $('#turnTimeLimit #second')
        elHour2 = $('#timeLimit #hour')
        elMinute2 = $('#timeLimit #minute')
        elSecond2 = $('#timeLimit #second')
        countdown()
    }
    var countdown = function () {
        var time = (new Date()).getTime(),
            diff1 = time - Turn.getBeginDate(),
            diff2 = time - gameBegin

        if (difference1 != diff1) {
            difference1 = diff1

            var date = new Date(diff1),
                hours = date.getHours() + Math.floor(diff1 / 3600000) - 1,
                minutes = date.getMinutes(),
                seconds = date.getSeconds()

            if (turnTimeL && hours * 60 + minutes >= turnTimeL) {
                WebSocketGame.nextTurn()
            }

            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (hours < 10) {
                hours = '0' + hours;
            }

            if (hour1 != hours) {
                hour1 = hours
                elHour1.html(hours)
            }
            if (minute1 != minutes) {
                minute1 = minutes
                elMinute1.html(minutes)
            }
            if (second1 != seconds) {
                second1 = seconds
                elSecond1.html(seconds)
            }
        }

        if (difference2 != diff2) {
            difference2 = diff2

            var date = new Date(diff2),
                hours = date.getHours() + Math.floor(diff2 / 3600000) - 1,
                minutes = date.getMinutes(),
                seconds = date.getSeconds();


            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (hours < 10) {
                hours = '0' + hours;
            }

            if (hour2 != hours) {
                hour2 = hours
                elHour2.html(hours)
            }
            if (minute2 != minutes) {
                minute2 = minutes
                elMinute2.html(minutes)
            }
            if (second2 != seconds) {
                second2 = seconds
                elSecond2.html(seconds)
            }
        }

        setTimeout(function () {
            countdown()
        }, 1000);
    }
    this.update = function () {
        $('#limitBox #' + Turn.getColor() + Turn.getNumber() + ' #hour').html(elHour1.html())
        $('#limitBox #' + Turn.getColor() + Turn.getNumber() + ' #minute').html(elMinute1.html())
        $('#limitBox #' + Turn.getColor() + Turn.getNumber() + ' #second').html(elSecond1.html())
        //this.scroll()
    }
    this.append = function (color, number, start, end) {
        var difference = 0,
            hours = 0,
            minutes = 0,
            seconds = 0

        if (isSet(start) && isSet(end)) {
            var end = Date.parse(end).getTime();
            difference = end - Date.parse(start).getTime() - 3600000
            timestamp = end

            var time = new Date(difference),
                hours = time.getHours(),
                minutes = time.getMinutes(),
                seconds = time.getSeconds();

            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (hours < 10) {
                hours = '0' + hours;
            }
        } else {
            seconds = '--'
            minutes = '--'
            hours = '--'
        }

        $('#timerRows')
            .prepend($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(color))))
                .append($('<div class="left nr">').html(number))
                .append(
                $('<div class="left time" id="' + color + number + '">')
                    .append($('<div>').attr('id', 'second').html(seconds))
                    .append($('<div>').html(':'))
                    .append($('<div>').attr('id', 'minute').html(minutes))
                    .append($('<div>').html(':'))
                    .append($('<div>').attr('id', 'hour').html(hours))
            )
        );
    }
    //this.scroll = function () {
    //    $('#timerScroll').animate({scrollTop: $('#timerRows .row').length * this.height}, 1000)
    //}
}
