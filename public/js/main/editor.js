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
        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('editor', 'create', {
                'name': $('#name').val(),
                'mapSize': $('#mapSize').val(),
                'maxPlayers': $('#maxPlayers').val()
            })
        })
    }
    this.generate = function (r) {
        var main = $('#main')
        Main.setMain(main.html())
        main.html(r.data)
        mapId = r.mapId
        MapGenerator.init(r.mapSize)
    }
    this.edit = function (r) {
        var main = $('#main')
        Main.setMain(main.html())
        main.html(r.data)
        WebSocketEditor.init()
    }
    this.getMapId = function () {
        return mapId
    }
}