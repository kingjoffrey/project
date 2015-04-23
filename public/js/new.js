$().ready(function () {
    New.init()
})

var New = new function () {
    var myGames,
        ws,
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

    this.init = function () {
        myGames = $('#join.table table');

        changeMap()

        $('#mapId').change(function () {
            changeMap()
            getNumberOfPlayersForm()
        })
        ws = new WebSocket(wsURL + '/new')
    }

}

function refresh() {
    $.getJSON('/' + lang + '/newajax/refresh', function (result) {

        myGames.html('');
        myGames.append(th);

        var j = 0;

        for (i in result) {
            j++;
            myGames.append(
                $('<tr>')
                    .addClass('gid' + result[i].gameId)
                    .append($('<td>').append($('<a>').html(result[i].name)).css('cursor', 'pointer'))
//                    .append($('<td>').append($('<a>').html(result[i].gameMaster)).css('cursor', 'pointer'))
//                    .append($('<td>').append($('<a>').html(result[i].playersingame)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].playersingame + '/' + result[i].numberOfPlayers)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].begin.split('.')[0])).css('cursor', 'pointer'))
                    .bind('click', {gameId: result[i].gameId}, makeUrl)
                    .mouseover(function () {
                        $(this).css('background', 'transparent url(/img/nav_bg.png) repeat')
                    })
                    .mouseleave(function () {
                        $(this).css('background', 'transparent')
                    })
            );
            $('#mygames td').mouseover(function () {
                $('#mygames td').css('cursor', 'pointer')
            });
        }
        if (j == 0) {
            myGames.append(
                $('<tr>')
                    .append($('<td colspan="3">').html(info).css('padding', '15px'))
            )
        }
    });
}
function makeUrl(event) {
    top.location.replace('/' + lang + '/setup/index/gameId/' + event.data.gameId);
}

