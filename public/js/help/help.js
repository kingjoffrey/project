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
            mesh = Models.addUnit(5, 4, 'orange', help.list[lastUnitId].name.replace(' ', '_').toLowerCase())
            text.prepend(unitProperties(help.list[lastUnitId]))
            currentUnitId = lastUnitId
        }

    this.click = function (id) {
        this.fillText(id)
    }
    this.fillText = function (id) {
        console.log(id)
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
                mesh = Models.addArmySimple(5, 5, 'orange', 'light_infantry')
                break
            case 'castle':
                mesh = Models.addCastle({x: 1.5, y: 0.5, defense: 4, name: 'Castle'}, 'orange')
                mesh.scale.x = 13
                mesh.scale.y = 13
                mesh.scale.z = 13
                break
            case 'hero':
                mesh = Models.addHero(5, 4, 'orange')
                break
            case 'tower':
                mesh = Models.addTower(1.5, 3, 'orange')
                mesh.scale.x = 17
                mesh.scale.y = 17
                mesh.scale.z = 17
                break
            case 'ruin':
                mesh = Models.addRuin(1.5, 2, 'gold')
                mesh.scale.x = 30
                mesh.scale.y = 30
                mesh.scale.z = 30
                break
            case 'units':
                for (var unitId in help.list) {
                    mesh = Models.addUnit(5, 4, 'orange', help.list[unitId].name.replace(' ', '_').toLowerCase())
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

        mesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(200, 200), new THREE.MeshLambertMaterial({
            color: 0xffffff,
            side: THREE.DoubleSide
        }))
        mesh.rotation.x = Math.PI / 2
        mesh.position.set(0, -30, 0)
        Scene.add(mesh)
        if (Scene.getShadows()) {
            mesh.receiveShadow = true
        }
        mesh = 0
    }
}
