var WebSocketEditor = new function () {
    var closed = true,
        ws,
        onMessage = function (r) {
            //console.log(r)
            switch (r.type) {
                case 'init':
                    if (!Editor.getInit()) {
                        Editor.init(r)
                    }
                    break;
                case 'castleId':
                    Players.get('neutral').getCastles().add(r.value, {
                        x: Picker.getX(),
                        y: Picker.getZ(),
                        name: 'Unknown',
                        defense: 1
                    })
                    break
                case 'towerId':
                    Players.get('neutral').getTowers().add(r.value, {x: Picker.getX(), y: Picker.getZ()})
                    break
                case 'ruinId':
                    Ruins.add(r.value, {x: Picker.getX(), y: Picker.getZ()})
                    break
            }
        },
        open = function () {
            if (closed) {
                console.log(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                mapId: mapId,
                playerId: id,
                langId: langId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        }

    this.init = function () {
        ws = new WebSocket(wsURL + '/editor')

        ws.onopen = function () {
            closed = false
            open()
        }
        ws.onmessage = function (e) {
            onMessage($.parseJSON(e.data))
        }
        ws.onclose = function () {
            closed = true;
            setTimeout('WebSocketEditor.init()', 1000)
        }
    }

    this.add = function (itemName, x, y) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'add',
            mapId: mapId,
            itemName: itemName,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.edit = function (castleId) {
        if (closed) {
            console.log(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'edit',
            mapId: mapId,
            castleId: castleId,
            name: $('input[name=name]').val(),
            income: $('input[name=income]').val(),
            color: $('select[name=color]').val(),
            defence: $('select[name=defence]').val(),
            capital: Boolean($('input[name=capital]').is(':checked')),
            production: {
                0: {'unitId': $('select[name=unitId0]').val(), 'time': $('select[name=time0]').val()},
                1: {'unitId': $('select[name=unitId1]').val(), 'time': $('select[name=time1]').val()},
                2: {'unitId': $('select[name=unitId2]').val(), 'time': $('select[name=time2]').val()},
                3: {'unitId': $('select[name=unitId3]').val(), 'time': $('select[name=time3]').val()}
            }
        }

        ws.send(JSON.stringify(token))
    }
}
