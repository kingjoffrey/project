"use strict"
var SingleController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('single', 'index', {'mapId': $('#mapId').val()})
        })
        $('#mapId').change(function () {
            WebSocketSendMain.controller('create', 'map', {'mapId': $('#mapId').val()})
        })

        WebSocketSendMain.controller('create', 'map', {'mapId': $('#mapId').val()})

        $('#wait').hide()
    }
}