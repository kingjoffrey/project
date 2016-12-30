"use strict"
var New = new function () {
    var table,
        empty,
        info = 'There are no open games',
        playersOutElement,
        gameMasterId,
        gameId,
        numberOfSelectedPlayers = 0,
        form,
        mapPlayers,
        initButton = function (mapPlayerId) {
            $('#' + mapPlayerId + ' .td2').html($('<a>')
                .addClass('button')
                .html(translations.select)
                .attr('id', mapPlayerId)
                .click(function () {
                    WebSocketSendNew.change(this.id)
                }))
        },
        initButtons = function () {
            for (var mapPlayerId in mapPlayers) {
                initButton(mapPlayerId)
            }
        },
        initTeams = function () {
            var click = function (i) {
                return function () {
                    WebSocketSendNew.team(i)
                }
            }

            for (var mapPlayerId in mapPlayers) {
                $('#' + mapPlayerId + ' .td4')
                    .html($(form).children('dl').children('dd').children('select'))
                    .append($('<img>').attr('src', '/img/game/heroes/' + mapPlayers[mapPlayerId].shortName + '.png'))
                $('#' + mapPlayerId + ' .td4 select')
                    .val(mapPlayerId)
                    .attr('id', mapPlayerId)
                    .change(click(mapPlayerId))
            }
        }

    this.updateStartButton = function () {
        $('.td3').each(function () {
            if ($(this).html()) {
                numberOfSelectedPlayers++
            }
        })
        if (gameMasterId == id) {
            if (numberOfSelectedPlayers > 0) {
                $('#start')
                    .html(translations.startGame)
                    .removeClass('buttonOff')
                    .css('display', 'inline-block')
                    .unbind()
                    .click(function () {
                        if (gameMasterId != id) {
                            return
                        }
                        var team = {}
                        $('#playersingame tr').each(function () {
                            var id = $(this).attr('id')
                            if (isSet(id)) {
                                team[id] = $(this).find('select').val()
                            }
                        })
                        WebSocketSendNew.start(team)
                    })
            } else {
                $('#start')
                    .html(translations.startGame)
                    .addClass('buttonOff')
                    .css('display', 'inline-block')
                    .unbind()
            }
        } else {
            $('#start').css('display', 'none')
        }
        numberOfSelectedPlayers = 0
    }
    this.removePlayer = function (playerId) {
        var tr = $('#' + playerId + '.td1').parent()
        if (tr.length) {
            tr.removeClass('selected')
            initButton(tr.attr('id'))
            tr.find('.td3').html('')
            $('#' + playerId + '.td1').attr('id', '')
        }
        playersOutElement.find('#' + playerId).remove()
    }
    this.getPlayersOutElement = function () {
        return playersOutElement
    }
    this.getGameMasterId = function () {
        return gameMasterId
    }
    this.setGameMasterId = function (i) {
        gameMasterId = i
    }
    this.getGameId = function () {
        return gameId
    }
    this.getMapPlayers = function () {
        return mapPlayers
    }



    this.removeGame = function (gameId) {
        $('tr#' + gameId).remove()
        if (!$('.trlink').length) {
            table.append(empty)
        }
    }
    this.addGames = function (games) {
        for (var i in games) {
            this.addGame(games[i])
        }
        if (!$('.trlink').length && !$('tr#0').length) {
            table.append(empty)
        }
    }
    this.addGame = function (game) {
        if ($('tr#' + game.id).length) {
            return
        }
        var numberOfPlayersInGame = countProperties(game.players)
        $('tr#0').remove()
        table.append(
            $('<tr>')
                .addClass('trlink')
                .attr('id', game.id)
                .append($('<td>').html(game.name))
                .append($('<td>').html(game.gameMasterName))
                .append($('<td>').append($('<span>').html(numberOfPlayersInGame)).append('/' + game.numberOfPlayers))
                .append($('<td>').html(game.begin.split('.')[0]))
                .click(function () {
                    WebSocketSendMain.controller('new', 'setup', {'gameId': $(this).attr('id')})
                })
        )
    }
    this.setup = function (mapP, f, id) {
        mapPlayers = mapP
        form = f
        gameId = id

        playersOutElement = $('#playersout')
        // PrivateChat.setType('setup')
        // PrivateChat.enable()
        initButtons()
        initTeams()
        WebSocketSetup.init()
    }
    this.init = function () {
        // PrivateChat.setType('new')
        table = $('#join.table table')
        empty = $('<tr id="0">').append($('<td colspan="4">').html(info).css('padding', '15px'))

        $('#mapId').change(function () {
            WebSocketSendMain.controller('new','map',{'mapId':$('#mapId').val()})
        })

        WebSocketNew.init()
        PrivateChat.prepare()
    }
}
