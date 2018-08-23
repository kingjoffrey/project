"use strict"
var Tutorial = new function () {
    var number = 0,
        step = 0,
        steps = [],
        timeoutId = null,
        blink = 0

    this.initSteps = function (s) {
        steps = s
    }
    this.showDescription = function () {
        var stepPlus = step + 1,
            numberPlus = number + 1

        Message.tutorial(
            'Tutorial ' + numberPlus + '/' + steps.length,
            translations.Goal + ' ' + stepPlus + '/' + steps[number].length + ': ' + steps[number][step].goal,
            steps[number][step]
        )

        clearInterval(timeoutId)
        $('#game #tutorial').removeClass('blink')
    }
    this.changeStep = function (s) {
        step = s * 1

        var goal = steps[number][step].goal

        $('#game #tutorial').html(goal)
        this.blink()
    }
    this.blink = function () {
        if (timeoutId) {
            clearInterval(timeoutId);
        }

        timeoutId = setInterval(function () {
            if (blink) {
                blink = 0
                $('#game #tutorial').addClass('blink')
            } else {
                $('#game #tutorial').removeClass('blink')
                blink = 1
            }
        }, 1000)
    }
    this.init = function (t) {
        number = t.number * 1

        $('#game #tutorial').click(function () {
            Tutorial.showDescription()
        })

        this.changeStep(t.step)
    }
}