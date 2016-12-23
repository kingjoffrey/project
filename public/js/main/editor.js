"use strict"
var EditorController = new function () {
    var mapId
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('#create').click(function () {
            WebSocketSendMain.controller('editor', 'create')
        })
        $('.trlink').click(function () {
            mapId = $(this).attr('id')
            WebSocketSendMain.controller('editor', 'edit')
        })
    }
    this.create = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#back').click(function () {
            WebSocketSendMain.controller('index', 'index')
        })
        $('#submit').submit(function (e) {
            e.preventDefault()
            console.log($('#name').val())
            console.log($('#mapSize').val())
            console.log($('#maxPlayers').val())
            // WebSocketSendMain.controller('index', 'index')
        })
    }
    this.edit = function (r) {
        var body = $('body')
        Main.setBody(body.html())
        body.html(r.data)
        WebSocketEditor.init()
    }
    this.getMapId = function () {
        return mapId
    }
}