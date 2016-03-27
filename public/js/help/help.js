"use strict"
var Help = new function () {
    var help,
        text,
        graphics,
        mesh = 0,
        currentUnitId = 0,
        unitProperties = function (unit) {
            $('#unit').remove()
            if (unit.special) {
                var special = 'yes'
            } else {
                var special = 'no'
            }
            if (unit.canFly) {
                var table = $('<table>')
                    .append(
                        $('<tr>')
                            .append($('<th>'))
                            .append($('<th>').html('Road'))
                            .append($('<th>').html('Grass'))
                            .append($('<th>').html('Forest'))
                            .append($('<th>').html('Swamp'))
                            .append($('<th>').html('Hill'))
                            .append($('<th>').html('Mountain'))
                            .append($('<th>').html('Water'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Walk'))
                            .append($('<td>').html('1'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html(unit.modMovesForest))
                            .append($('<td>').html(unit.modMovesSwamp))
                            .append($('<td>').html(unit.modMovesHills))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Fly'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html('2'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Swim'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
            } else if (unit.canSwim) {
                var table = $('<table>')
                    .append(
                        $('<tr>')
                            .append($('<th>'))
                            .append($('<th>').html('Road'))
                            .append($('<th>').html('Grass'))
                            .append($('<th>').html('Forest'))
                            .append($('<th>').html('Swamp'))
                            .append($('<th>').html('Hill'))
                            .append($('<th>').html('Mountain'))
                            .append($('<th>').html('Water'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Walk'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Fly'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Swim'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('1'))
                    )
            } else {
                var table = $('<table>')
                    .append(
                        $('<tr>')
                            .append($('<th>'))
                            .append($('<th>').html('Road'))
                            .append($('<th>').html('Grass'))
                            .append($('<th>').html('Forest'))
                            .append($('<th>').html('Swamp'))
                            .append($('<th>').html('Hill'))
                            .append($('<th>').html('Mountain'))
                            .append($('<th>').html('Water'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Walk'))
                            .append($('<td>').html('1'))
                            .append($('<td>').html('2'))
                            .append($('<td>').html(unit.modMovesForest))
                            .append($('<td>').html(unit.modMovesSwamp))
                            .append($('<td>').html(unit.modMovesHills))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Fly'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html('Swim'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
            }
            return $('<div>')
                .attr('id', 'unit')
                .append($('<h3>')
                    .append($('<span>').html('<< ').click(function () {
                        changeUnit('-')
                    }))
                    .append(unit.name_lang)
                    .append($('<span>').html(' >>').click(function () {
                        console.log('xxx')
                        changeUnit('+')
                    }))
                )
                .append($('<div>').html('attack/defense - ' + unit.attackPoints + '/' + unit.defensePoints))
                .append($('<div>').html('cost - ' + unit.cost + ', moves - ' + unit.numberOfMoves))
                .append($('<div>').html('special unit - ' + special))
                .append(table)
        },
        changeUnit = function (direction) {
            if (mesh) {
                Scene.remove(mesh)
            }
            var stop = 0,
                lastUnitId = 0

            for (var unitId in help.list) {
                if (stop) {
                    lastUnitId = unitId
                    break
                }
                if (unitId == currentUnitId) {
                    stop = 1
                    if (direction == '-') {
                        break
                    }
                }
                lastUnitId = unitId
            }
            if (!lastUnitId) {
                lastUnitId = unitId
            }
            mesh = Models.addUnit('orange', help.list[lastUnitId].name.replace(' ', '_').toLowerCase())
            text.prepend(unitProperties(help.list[lastUnitId]))
            currentUnitId = lastUnitId
        }

    this.click = function (id) {
        this.fillText(id)
    }
    this.fillText = function (id) {
        var menu = help[id]
        $('#helpMenu div').removeClass('off')
        $('#' + id).addClass('off')
        if (mesh) {
            Scene.remove(mesh)
            mesh = 0
        }
        text.html('')
        for (var i in menu) {
            text
                .append($('<h5>').html(menu[i].title))
                .append($('<p>').html(Help.nl2br(menu[i].content)))
        }
        switch (id) {
            case 'army':
                mesh = Models.addArmySimple('orange', 'light_infantry')
                break
            case 'castle':
                mesh = Models.addCastle({x: 0, y: -2, defense: 4, name: 'Castle'}, 'orange')
                break
            case 'hero':
                mesh = Models.addHero('orange')
                break
            case 'tower':
                mesh = Models.addTower(-1, 1, 'orange')
                mesh.scale.x = 1.7
                mesh.scale.y = 1.7
                mesh.scale.z = 1.7
                break
            case 'ruin':
                mesh = Models.addRuin(0, 0, 'gold')
                mesh.scale.x = 3
                mesh.scale.y = 3
                mesh.scale.z = 3
                break
            case 'units':
                for (var unitId in help.list) {
                    mesh = Models.addUnit('orange', help.list[unitId].name.replace(' ', '_').toLowerCase())
                    text.prepend(unitProperties(help.list[unitId]))
                    currentUnitId = unitId
                    break
                }
                break
        }
        if (mesh) {
            graphics.css('display', 'block')
        } else {
            graphics.css('display', 'none')
        }
    }
    this.set = function (r) {
        help = r
    }
    this.nl2br = function (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
    this.init = function () {
        $('#helpMenu div').click(function () {
            Help.click($(this).attr('id'))
        })

        graphics = $('#graphics')
        text = $('#text')

        WebSocketHelp.init()
    }
}
