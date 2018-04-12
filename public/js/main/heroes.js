"use strict"
var HeroesController = new function () {
    var attack,
        defense,
        moves,
        createList = function (list) {
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
        },
        updateBonus = function (bonus) {
            var a = 0, d = 0, m = 0
            for (var i in bonus) {
                switch (bonus[i]) {
                    case 1:
                        a++
                        break
                    case 2:
                        d++
                        break
                    case 3:
                        m++
                        break
                }
            }
            $('#sAttack span').html(a + attack)
            $('#sDefense span').html(d + defense)
            $('#sMoves span').html(m + moves)
        }
    this.index = function (r) {
        $('#content').html(r.data)
        createList(r.list)
    }
    this.show = function (r) {
        var bonusSize = countProperties(r.bonus)

        if (isSet(r.data)) {
            $('#content').html(r.data)

            attack = r.attack
            defense = r.defense
            moves = r.moves

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

            updateBonus(r.bonus)

            if (bonusSize < r.level) {
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
        } else {
            updateBonus(r.bonus)

            if (bonusSize >= r.level) {

                $('#sAttack div').hide()
                $('#sDefense div').hide()
                $('#sMoves div').hide()
            }
        }
    }
    this.ok = function (r) {
        $('#content').html(r.data)
    }
}