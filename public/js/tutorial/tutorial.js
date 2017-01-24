"use strict"
var Tutorial = new function () {
    var number = 0,
        step = 0,
        steps = []

    this.initSteps = function (s) {
        steps = s
    }
    this.showDescription = function () {
        if (isSet(steps[number]) && isSet(steps[number][step])) {
            var stepPlus = step * 1 + 1,
                numberPlus = number * 1 + 1,
                html = $('<div>')
                    .append($('<div>').html('Goal ' + stepPlus + '/' + steps[number].length + ': ' + steps[number][step].goal).addClass('goal'))
                    .append($('<div>').html(steps[number][step].description))
            Message.tutorial('Tutorial ' + numberPlus + '/' + steps.length, html.html())
        }
    }
    this.changeStep = function (s) {
        step = s

        this.showDescription()

        $('#tutorial').html('Goal: ' + steps[number][step].goal)
    }
    this.init = function (t) {
        console.log(t)
        number = t.number

        var tutorial = $('<div id="tutorial">').click(function () {
            Tutorial.showDescription()
        })

        $('#terrain').after(tutorial)

        this.changeStep(t.step)
    }
}