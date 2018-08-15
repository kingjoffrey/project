"use strict"
var EditorController = new function () {
    var mapId,
        addRow = function (map) {
            $('table').append(
                $('<tr>').attr('id', map.mapId).addClass('trlink')
                    .append($('<td>').html($('<input>').val(map.name).addClass('name').change(function () {
                        $(this).parent().parent().find('#save').css('display', 'inline-block')
                    })))
                    .append($('<td>').html($('<input>').val(map.maxPlayers).addClass('max').change(function () {
                        $(this).parent().parent().find('#save').css('display', 'inline-block')
                    })))
                    .append($('<td>').html(map.date))
                    .append($('<td>')
                        .append(
                            $('<div>').addClass('button buttonColors').html(translations.Edit).click(function () {
                                mapId = $(this).parent().parent().attr('id')
                                EditorController.edit()
                            })
                        )
                        .append(
                            $('<div>').addClass('button buttonColors').html(translations.Test).click(function () {
                                WebSocketSendMain.controller('single', 'index', {
                                    'mapId': $(this).parent().parent().attr('id'),
                                    'test': 1
                                })
                            })
                        )
                        .append(
                            $('<div>').addClass('button buttonColors').attr('id', 'save').html(translations.Save).click(function () {
                                WebSocketSendMain.controller('editor', 'save', {
                                    'id': $(this).parent().parent().attr('id'),
                                    'name': $(this).parent().parent().find('.name').val(),
                                    'max': $(this).parent().parent().find('.max').val()
                                })
                            })
                        )
                        .append(
                            $('<div>').addClass('button buttonColors').attr('id', 'publish').html(translations.Publish).click(function () {
                                WebSocketSendMapgenerator.publish($(this).parent().parent().attr('id'))
                            })
                        )
                    )
                    .append($('<td>')
                        .append(
                            $('<div>').addClass('iconButton buttonColors').attr('id', 0).html($('<div>').addClass('mirror mirror0')).click(mirrorClick())
                        )
                        .append(
                            $('<div>').addClass('iconButton buttonColors').attr('id', 1).html($('<div>').addClass('mirror mirror1')).click(mirrorClick())
                        )
                        .append(
                            $('<div>').addClass('iconButton buttonColors').attr('id', 2).html($('<div>').addClass('mirror mirror2')).click(mirrorClick())
                        )
                        .append(
                            $('<div>').addClass('iconButton buttonColors').attr('id', 3).html($('<div>').addClass('mirror mirror3')).click(mirrorClick())
                        )
                    )
                    .append($('<td>').append(
                        $('<div>').addClass('iconButton buttonColors').html($('<div>').addClass('trash')).click(function (e) {
                            $('#wait').show()
                            WebSocketSendMain.controller('editor', 'delete', {'id': $(this).parent().parent().attr('id')})
                        })
                    ))
            )
            $('.trlink').css('cursor', 'default')
        },
        mirrorClick = function () {
            return function (e) {
                $('#wait').show()
                WebSocketSendMapgenerator.mirror($(this).parent().parent().attr('id'), $(this).attr('id'))
            }
        }
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.list) {
            addRow(r.list[i])
        }

        $('#create').click(function () {
            WebSocketSendMain.controller('editor', 'create')
        })
    }
    this.delete = function (r) {
        $('tr#' + r.id).remove()
        $('#wait').hide()
    }
    this.create = function (r) {
        $('#content').html(r.data)

        $('form').submit(function (e) {
            $('#wait').show()

            e.preventDefault()
            WebSocketSendEditor.controller('editor', 'create', {
                'name': $('#name').val(),
                'maxPlayers': $('#maxPlayers').val()
            })

            WebSocketSendMapgenerator.create($('#name').val(),$('#maxPlayers').val())
        })
    }
    this.add = function (r) {
        addRow(r.map)
        $('#wait').hide()
    }
    this.save = function (r) {
        $('#' + r.id + ' #save').hide()
    }
    this.edit = function () {
        $('#bg').hide()
        $('#loading').show()
        $('#loading2').hide()
        WebSocketEditor.init()
    }
    this.getMapId = function () {
        return mapId
    }
}