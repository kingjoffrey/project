"use strict"
var HeroesController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

    }
    this.show = function (r) {
        $('#content').html(r.data)
    }
    this.ok = function (r) {
        $('#content').html(r.data)
    }
}