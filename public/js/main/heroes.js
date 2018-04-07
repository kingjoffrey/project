"use strict"
var HeroesController = new function () {
    var createList = function (list) {
            for (var id in list) {
                addHero(list[id])
            }
            if (notSet(id)) {
                addNoList()
            }
        },
        addNoList = function () {
            $('#heroesList').append($('<tr>').append($('<td colspan="2">').html(translations.Nosearchresults).addClass('after')))
        },
        addHero = function (hero) {

            $('#heroesList').append($('<tr>').addClass('trlink').attr('id', hero.heroId)
                .append($('<td>').html(hero.name))
                .append($('<td>').html(hero.experience))
                .click(function () {
                    WebSocketSendMain.controller('heroes', 'show', {'id': $(this).attr('id')})
                })
            )
        }
    this.index = function (r) {
        $('#content').html(r.data)
        createList(r.list)
    }
    this.show = function (r) {
        var bonusSize = countProperties(r.bonus)
        if (isSet(r.data)) {
            $('#content').html(r.data)

            var hero1 = $('<table id="hero1">')
                    .append($('<tr>').append($('<td colspan="3">').html(r.name)))
                    .append($('<br/>')),
                hero2 = $('<table id="hero2">')
                    .append(
                        $('<tr>')
                            .append($('<td>').html(translations.Attack))
                            .append($('<td>').html(translations.Defense))
                            .append($('<td>').html(translations.Moves))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td id="sAttack">').html($('<span>').html(r.attack)))
                            .append($('<td id="sDefense">').html($('<span>').html(r.defense)))
                            .append($('<td id="sMoves">').html($('<span>').html(r.moves)))
                    )
                    .append($('<br/>')),
                hero3 = $('<table id="hero3">')
                    .append(
                        $('<tr>')
                            .append($('<td colspan="2">').html(translations.Experience))
                            .append($('<td>').html(r.experience))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td colspan="2">').html(translations.Level))
                            .append($('<td>').html(r.level))
                    )
            $('.table h2').after(hero3).after(hero2).after(hero1)

            for (var i in r.bonus) {
                var bonus = r.bonus[i].bId

                switch (bonus) {
                    case 1:
                        var val = $('#sAttack span').html() * 1 + 1
                        $('#sAttack span').html(val)
                        break
                    case 2:
                        var val = $('#sDefense span').html() * 1 + 1
                        $('#sDefense span').html(val)
                        break
                    case 3:
                        var val = $('#sMoves span').html() * 1 + 1
                        $('#sMoves span').html(val)
                        break
                }
            }

            if (bonusSize > r.level) {
                $('#sAttack').append(
                    $('<div>').addClass('button buttonColors').html('+').click(function () {
                        WebSocketSendMain.controller('heroes', 'up', {'hId': r.id, 'lbId': 1})
                    })
                )
                $('#sDefense').append(
                    $('<div>').addClass('button buttonColors').html('+').click(function () {
                        WebSocketSendMain.controller('heroes', 'up', {'hId': r.id, 'lbId': 2})
                    })
                )
                $('#sMoves').append(
                    $('<div>').addClass('button buttonColors').html('+').click(function () {
                        WebSocketSendMain.controller('heroes', 'up', {'hId': r.id, 'lbId': 3})
                    })
                )
            }
        } else if (bonusSize <= r.level) {
            $('#sAttack div').hide()
            $('#sDefense div').hide()
            $('#sMoves div').hide()
        }
    }
    this.ok = function (r) {
        $('#content').html(r.data)
    }
}