var Websocket = new function () {
    var closed = true,
        queue = {},
        executing = 0,
        i = 0,
        handler = '',
        wait = function () {
            if (executing) {
                setTimeout('wait()', 500);
            } else {
                for (var ii in queue) {
                    Websocket.execute(queue[ii])
                    delete queue[ii]
                    return
                }
            }
        },
        open = function () {
            if (closed) {
                Message.error(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                playerId: id,
                langId: langId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        }

    this.init = function (handler) {
        handler = handler
        ws = new WebSocket(wsURL + '/' + handler)

        ws.onopen = function () {
            closed = false
            open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r['type'])) {
                Websocket.onMessage(r)
            }
        }

        ws.onclose = function () {
            closed = true
            setTimeout('Websocket.init(handler)', 1000)
        }

    }
    this.addQueue = function (r) {
        i++
        queue[i] = r
        wait()
    }
    this.execute = function (r) {

    }
    this.onMessage = function (r) {

    }
    this.setExecuting = function (value) {
        executing = value
    }
    this.getI = function () {
        return i
    }
}