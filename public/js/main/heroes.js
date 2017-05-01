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
        $('#content').html(r.data)
    }
    this.ok = function (r) {
        $('#content').html(r.data)
    }
}