"use strict"
var Help = new function () {
    var help,
        mesh = 0,
        currentUnitId = 0,
        currentCastleId = 0,
        currentTerrainId = 'g',
        castles = [1, 2, 3, 4, 5],
        terrainMatrix = {
            'g': [
                ['g', 'g', 'g', 'g'],
                ['g', 'g', 'g', 'g'],
                ['g', 'g', 'g', 'g'],
                ['g', 'g', 'g', 'g']
            ],
            'r': [
                ['g', 'r', 'g', 'g'],
                ['g', 'r', 'g', 'g'],
                ['r', 'r', 'r', 'r'],
                ['g', 'r', 'g', 'r']
            ],
            'f': [
                ['f', 'f', 'f', 'f'],
                ['f', 'f', 'f', 'f'],
                ['f', 'f', 'f', 'f'],
                ['f', 'f', 'f', 'f']
            ],
            's': [
                ['s', 's', 's', 's'],
                ['s', 's', 's', 's'],
                ['s', 's', 's', 's'],
                ['s', 's', 's', 's']
            ],
            'h': [
                ['h', 'h', 'h', 'h'],
                ['h', 'h', 'h', 'h'],
                ['h', 'h', 'h', 'h'],
                ['h', 'h', 'h', 'h']
            ],
            'm': [
                ['m', 'm', 'm', 'm'],
                ['m', 'm', 'm', 'm'],
                ['m', 'm', 'm', 'm'],
                ['m', 'm', 'm', 'm']
            ],
            'w': [
                ['w', 'w', 'w', 'w'],
                ['w', 'w', 'w', 'w'],
                ['w', 'w', 'w', 'w'],
                ['w', 'w', 'w', 'w']
            ],
            'b': [
                ['g', 'r', 'g', 'g'],
                ['g', 'r', 'g', 'g'],
                ['w', 'b', 'w', 'w'],
                ['g', 'r', 'g', 'g']
            ],
        },
        unitProperties = function (unit) {
            $('#unit').remove()
            if (unit.canFly) {
                var table = $('<table>')
                    .append(
                        $('<tr>')
                            .append($('<th>'))
                            .append($('<th>').html(translations.Road))
                            .append($('<th>').html(translations.Grass))
                            .append($('<th>').html(translations.Forest))
                            .append($('<th>').html(translations.Swamp))
                            .append($('<th>').html(translations.Hill))
                            .append($('<th>').html(translations.Mountain))
                            .append($('<th>').html(translations.Water))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html(translations.Walk))
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
                            .append($('<td>').html(translations.Fly))
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
                            .append($('<td>').html(translations.Swim))
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
                            .append($('<th>').html(translations.Road))
                            .append($('<th>').html(translations.Grass))
                            .append($('<th>').html(translations.Forest))
                            .append($('<th>').html(translations.Swamp))
                            .append($('<th>').html(translations.Hill))
                            .append($('<th>').html(translations.Mountain))
                            .append($('<th>').html(translations.Water))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html(translations.Walk))
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
                            .append($('<td>').html(translations.Fly))
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
                            .append($('<td>').html(translations.Swim))
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
                            .append($('<th>').html(translations.Road))
                            .append($('<th>').html(translations.Grass))
                            .append($('<th>').html(translations.Forest))
                            .append($('<th>').html(translations.Swamp))
                            .append($('<th>').html(translations.Hill))
                            .append($('<th>').html(translations.Mountain))
                            .append($('<th>').html(translations.Water))
                    )
                    .append(
                        $('<tr>')
                            .append($('<td>').html(translations.Walk))
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
                            .append($('<td>').html(translations.Fly))
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
                            .append($('<td>').html(translations.Swim))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                            .append($('<td>').html('-'))
                    )
            }
            var unitDiv = $('<div>')
                .attr('id', 'unit')
                .append($('<h3>')
                    .append($('<span>').html('<<').addClass('button buttonColors').click(function () {
                        changeUnit('-')
                    }))
                    .append($('<span>').html(unit.name_lang).addClass('unitName'))
                    .append($('<span>').html('>>').addClass('button buttonColors').click(function () {
                        changeUnit('+')
                    }))
                )
                .append($('<div>')
                    .append(
                        $('<table>').addClass('unitTable')
                            .append($('<tr>')
                                .append($('<td>').html(translations.Attack))
                                .append($('<td>').html(unit.attackPoints))
                            )
                            .append($('<tr>')
                                .append($('<td>').html(translations.Defense))
                                .append($('<td>').html(unit.defensePoints))
                            )
                            .append($('<tr>')
                                .append($('<td>').html(translations.Moves))
                                .append($('<td>').html(unit.numberOfMoves))
                            )
                            .append($('<tr>')
                                .append($('<td>').html(translations.Cost))
                                .append($('<td>').html(unit.cost))
                            )
                    )
                )

            if (unit.special) {
                unitDiv.append($('<div>').html(translations.Specialunit).addClass('right'))
            }

            unitDiv.append(table)

            return unitDiv
        },
        changeUnit = function (direction) {
            if (mesh) {
                HelpScene.remove(mesh)
            }
            var stop = 0,
                lastId = 0

            for (var unitId in help.list) {
                if (stop) {
                    lastId = unitId
                    break
                }
                if (unitId == currentUnitId) {
                    stop = 1
                    if (direction == '-') {
                        break
                    }
                }
                lastId = unitId
            }
            if (!lastId) {
                lastId = unitId
            }

            mesh = HelpModels.addUnit(help.list[lastId].name.replace(' ', '_').toLowerCase())
            $('#text').prepend(unitProperties(help.list[lastId]))
            currentUnitId = lastId
        },
        changeCastle = function (direction) {
            if (mesh) {
                HelpScene.remove(mesh)
            }
            var stop = 0,
                lastId = 0

            for (var id in castles) {
                if (stop) {
                    lastId = id
                    break
                }
                if (id == currentCastleId) {
                    stop = 1
                    if (direction == '-') {
                        break
                    }
                }
                lastId = id
            }
            if (!lastId) {
                lastId = id
            }

            currentCastleId = lastId

            handleCastle()
        },
        changeTerrain = function (direction) {
            if (mesh) {
                HelpScene.remove(mesh)
            }
            var stop = 0,
                lastId = 0

            for (var id in terrainMatrix) {
                if (stop) {
                    lastId = id
                    break
                }
                if (id == currentTerrainId) {
                    stop = 1
                    if (direction == '-') {
                        break
                    }
                }
                lastId = id
            }
            if (!lastId) {
                lastId = id
            }

            currentTerrainId = lastId

            handleTerrain()
        },
        handleCastle = function () {
            mesh = HelpModels.addCastle(castles[currentCastleId])
            $('#Castle').remove()
            $('#text').prepend($('<div>')
                .attr('id', 'Castle')
                .append($('<h3>')
                    .append($('<span>').html('<<').addClass('button buttonColors').click(function () {
                        changeCastle('-')
                    }))
                    .append($('<span>').html(translations.Defense + ' ' + castles[currentCastleId]).addClass('unitName'))
                    .append($('<span>').html('>>').addClass('button buttonColors').click(function () {
                        changeCastle('+')
                    }))
                ))
        },
        handleTerrain = function () {
            mesh = HelpModels.addTerrain(terrainMatrix[currentTerrainId])
            $('#Terrain').remove()
            $('#text').prepend($('<div>')
                .attr('id', 'Terrain')
                .append($('<h3>')
                    .append($('<span>').html('<<').addClass('button buttonColors').click(function () {
                        changeTerrain('-')
                    }))
                    .append($('<span>').html(terrain[currentTerrainId].name).addClass('unitName'))
                    .append($('<span>').html('>>').addClass('button buttonColors').click(function () {
                        changeTerrain('+')
                    }))
                ))
        }

    this.fillText = function (id) {
        var element = help[id]
        $('#helpMenu div').removeClass('off')
        $('#helpMenu #' + id).addClass('off')
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
                handleCastle()
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
            case 'terrain':
                handleTerrain()
                break;
        }

        if (mesh) {
            HelpRenderer.setMesh(mesh)
            if (!HelpRenderer.isRunning()) {
                HelpRenderer.start()
            }
            $('#graphics').css('display', 'block')
        } else {
            $('#graphics').css('display', 'none')
        }
    }

    this.getMesh = function () {
        return mesh
    }
    // this.nl2br = function (str, is_xhtml) {
    //     var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    //     return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    // }
    this.init = function (r) {
        help = r
    }
}
