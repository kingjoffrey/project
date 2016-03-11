"use strict"
var Tutorial = new function () {
    var description = '',
        steps = {},
        step = 0
    this.showDescription = function () {
        Message.simple('Tutorial', description)
    }
    this.changeStep = function (s) {
        step = s
        var html = $('<div>'),
            add
        for (var i in steps) {
            if (i < step) {
                add = '+'
            } else if (i == step) {
                add = '>'
                description = steps[i].description
            } else {
                add = '-'
            }
            html.append($('<div>').append(add + ' ' + steps[i].goal))
        }
        $('#limitBox').html(html)
    }
    this.init = function (tutorial) {
        steps = tutorial.steps
        step = tutorial.step

        var html = $('<div>'),
            add
        for (var i in steps) {
            if (i < step) {
                add = '+'
            } else if (i == step) {
                add = '>'
                description = steps[i].description
            } else {
                add = '-'
            }
            html.append($('<div>').append(add + ' ' + steps[i].goal))
        }
        $('#limitBox').html(html)
    }
}