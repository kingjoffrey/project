"use strict"
var Tutorial = new function () {
    var description = ''
    this.showDescription = function () {
        Message.simple('Tutorial', description)
    }
    this.init = function (tutorial) {
        console.log(tutorial)

        var html = $('<div>'),
            add
        for (var i in tutorial.steps) {
            if (i < tutorial.step) {
                add = '+'
            } else if (i == tutorial.step) {
                add = '>'
                description = tutorial.steps[i].description
            } else {
                add = '-'
            }
            html.append($('<div>').append(add + ' ' + tutorial.steps[i].goal))
        }
        $('#limitBox').html(html)
    }
}