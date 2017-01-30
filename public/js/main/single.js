"use strict"
var SingleController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('single', 'index', {
                'mapId': $('select#mapId').val()
            })
        })
    }
}