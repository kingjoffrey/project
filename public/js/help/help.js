"use strict"
var Help = new function () {
    var help,
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
                    .append($('<span>').html('<< ').addClass('left').click(function () {
                        changeUnit('-')
                    }))
                    .append(unit.name_lang)
                    .append($('<span>').html(' >>').addClass('right').click(function () {
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
                HelpScene.remove(mesh)
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
            mesh = HelpModels.addUnit(help.list[lastUnitId].name.replace(' ', '_').toLowerCase())
            $('#text').prepend(unitProperties(help.list[lastUnitId]))
            currentUnitId = lastUnitId
        }

    this.fillText = function (id) {
        var element = help[id]
        $('#helpMenu div').removeClass('off')
        $('#' + id).addClass('off')
        if (mesh) {
            HelpScene.remove(mesh)
            mesh = 0
        }
        $('#text').html('')
        for (var i in element) {
            var content = element[i].content.split("\n")
            for (var j in content) {
                if (content[j].search('#')) { // return 0 if first
                    $('#text').append($('<p>').html(content[j]))
                } else {
                    $('#text').append($('<div>').html(content[j].substring(1) + ':').addClass('title'))
                }
            }
        }
        switch (id) {
            case 'army':
                mesh = HelpModels.addArmy()
                break
            case 'castle':
                mesh = HelpModels.addCastle()
                break
            case 'hero':
                mesh = HelpModels.addHero()
                break
            case 'tower':
                mesh = HelpModels.addTower()
                break
            case 'ruin':
                mesh = HelpModels.addRuin()
                break
            case 'units':
                for (var unitId in help.list) {
                    mesh = HelpModels.addUnit(help.list[unitId].name.replace(' ', '_').toLowerCase())
                    $('#text').prepend(unitProperties(help.list[unitId]))
                    currentUnitId = unitId
                    break
                }
                break
        }
        if (mesh) {
            $('#graphics').css('display', 'block')
        } else {
            $('#graphics').css('display', 'none')
        }
    }
    this.nl2br = function (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
    this.init = function (r) {
        help = r
    }
}
