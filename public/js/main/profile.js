"use strict"
var ProfileController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        $('form#player').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('profile', 'index', {
                'firstName': $('input#firstName').val(),
                'lastName': $('input#lastName').val()
            })
        })
        $('form#email').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('profile', 'index', {
                'login': $('input#login').val()
            })
        })
        $('form#password').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('profile', 'index', {
                'password': $('input#password').val(),
                'repeatPassword': $('input#repeatPassword').val()
            })
        })
    }
    this.show = function (r) {
        $('#content').html(r.data)
    }
    this.ok = function (r) {
        $('#content').html(r.data)

    }
}