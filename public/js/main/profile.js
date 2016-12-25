"use strict"
var ProfileController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
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
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('.trlink').click(function () {
            var id = $(this).attr('id')
            WebSocketSendMain.controller('over', 'index', {'id': id})
        })
    }
}