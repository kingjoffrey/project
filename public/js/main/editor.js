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
            EditorController.edit()
        })
        $('.trash').parent().click(function (e) {
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

        $('form').submit(function (e) {
            e.preventDefault()
            WebSocketSendMain.controller('editor', 'create', {
                'name': $('#name').val(),
                'maxPlayers': $('#maxPlayers').val()
            })
        })
    }
    this.generate = function (r) {
        mapId = r.mapId
        MapGenerator.init()
    }
    this.edit = function () {
        $('#bg').hide()
        $('.game').hide()
        $('#loading').show()
        WebSocketEditor.init()
    }
    this.getMapId = function () {
        return mapId
    }
}