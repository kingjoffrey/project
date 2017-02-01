"use strict"
var EditorController = new function () {
    var mapId
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        $('#create').click(function () {
            WebSocketSendMain.controller('editor', 'create')
        })
        $('.trlink').click(function () {
            mapId = $(this).attr('id')
            WebSocketSendMain.controller('editor', 'edit')
        })
        $('.trash').click(function (e) {
            e.preventDefault()
            e.stopPropagation()
            WebSocketSendMain.controller('editor', 'delete', {'id': $(this).parent().attr('id')})
        })
    }
    this.delete = function (r) {
        $('#' + r.id + '.trlink').remove()
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
                'maxPlayers': $('#maxPlayers').val()
            })
        })
    }
    this.generate = function (r) {
        var main = $('#main')
        Main.setMain(main.html())
        main.html(r.data)
        mapId = r.mapId
        MapGenerator.init()
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