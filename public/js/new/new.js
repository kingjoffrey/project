var New = new function () {
    var table,
        empty,
        changeMap = function () {
            $('#map').attr('src', '/img/maps/' + $('#mapId').children(':selected').attr('value') + '.png');
        },
        getNumberOfPlayersForm = function () {
            var mapId = $('#mapId').val()
            $.getJSON('/' + lang + '/newajax/nop/mapId/' + mapId, function (result) {
                var html = $.parseHTML(result);
                console.log($($(html)[0][0]).val())
                $('#x').val($($(html)[0][0]).val())
                $('#numberOfPlayers').val($($(html)[0][1]).val())
            })
        }

    this.removeGame=function(gameId){
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
                    top.location.replace('/' + lang + '/setup/index/gameId/' + $(this).attr('id'))
                })
        )
    }
    this.init = function () {
        PrivateChat.setType('new')
        table = $('#join.table table')
        empty = $('<tr id="0">').append($('<td colspan="4">').html(info).css('padding', '15px'))
        changeMap()
        $('#mapId').change(function () {
            changeMap()
            getNumberOfPlayersForm()
        })
        WebSocketNew.init()
        PrivateChat.prepare()
    }
}
