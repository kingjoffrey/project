var timer = {
    height: 32,
    timestamp: 0,
    difference1: 0,
    difference2: 0,
    elHour1: null,
    elMinute1: null,
    elSecond1: null,
    hour1: 0,
    minute1: 0,
    second1: 0,
    elHour2: null,
    elMinute2: null,
    elSecond2: null,
    hour2: 0,
    minute2: 0,
    second2: 0,
    start: function () {
        $('#timerScroll').css('height', Players.length * this.height + 'px');
        this.elHour1 = $('#turnTimeLimit #hour')
        this.elMinute1 = $('#turnTimeLimit #minute')
        this.elSecond1 = $('#turnTimeLimit #second')
        this.elHour2 = $('#timeLimit #hour')
        this.elMinute2 = $('#timeLimit #minute')
        this.elSecond2 = $('#timeLimit #second')
        timer.countdown();
    },
    countdown: function () {
        var time = (new Date()).getTime() - 3600000
        var difference1 = time - Date.parse(Turn.beginDate.substr(0, 19)).getTime()
        var difference2 = time - Date.parse(gameBegin.substr(0, 19)).getTime()

        if (this.difference1 != difference1) {
            this.difference1 = difference1

            time = new Date(difference1)
            var hours = time.getHours(),
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

            if (this.hour1 != hours) {
                this.hour1 = hours
                this.elHour1.html(hours)
            }
            if (this.minute1 != minutes) {
                this.minute1 = minutes
                this.elMinute1.html(minutes)
            }
            if (this.second1 != seconds) {
                this.second1 = seconds
                this.elSecond1.html(seconds)
            }
        }

        if (this.difference2 != difference2) {
            this.difference2 = difference2

            time = new Date(difference2)
            var hours = time.getHours(),
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

            if (this.hour2 != hours) {
                this.hour2 = hours
                this.elHour2.html(hours)
            }
            if (this.minute2 != minutes) {
                this.minute2 = minutes
                this.elMinute2.html(minutes)
            }
            if (this.second2 != seconds) {
                this.second2 = seconds
                this.elSecond2.html(seconds)
            }
        }

        setTimeout(function () {
            timer.countdown()
        }, 1000);
    },
    update: function () {
        $('#timerBox #' + Turn.color + Turn.number + ' #hour').html(this.elHour1.html())
        $('#timerBox #' + Turn.color + Turn.number + ' #minute').html(this.elMinute1.html())
        $('#timerBox #' + Turn.color + Turn.number + ' #second').html(this.elSecond1.html())
        this.scroll()
    },
    append: function (color, number, start, end) {
        var difference = 0,
            hours = 0,
            minutes = 0,
            seconds = 0

        if (isSet(start) && isSet(end)) {
            var end = Date.parse(end).getTime();
            difference = end - Date.parse(start).getTime() - 3600000
            this.timestamp = end

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
            .append($('<div class="row">')
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
    },
    scroll: function () {
        $('#timerScroll').animate({ scrollTop: $('#timerRows .row').length * this.height }, 1000)
    }
}
