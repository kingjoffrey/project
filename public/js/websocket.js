var Websocket = new function () {
    var closed = true,
        queue = {},
        executing = 0,
        i = 0,
        handler = '',
        addQueue = function (r) {
            i++
            queue[i] = r
            wait()
        },
        wait = function () {
            if (executing) {
                setTimeout('wait()', 500);
            } else {
                for (var ii in queue) {
                    execute(queue[ii])
                    delete queue[ii]
                    return
                }
            }
        },
        execute = function (r) {
            executing = 1

            switch (r.type) {
                case 'dead':

                    executing = 0
                    break;
            }
        },
        open = function () {
            if (closed) {
                Message.error(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                playerId: my.id,
                langId: langId,
                accessKey: websocket.accessKey,
                websocketId: websocket.websocketId
            }

            ws.send(JSON.stringify(token));
        }

    this.init = function (handler) {
        handler = handler
        var ws = new WebSocket(wsURL + '/' + handler)

        ws.onopen = function () {
            closed = false
            open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r['type'])) {
                switch (r.type) {
                    case 'move':
                        addQueue(r)
                        break;

                    default:
                        console.log(r);
                }
            }
        }

        ws.onclose = function () {
            closed = true
            setTimeout('Websocket.init(handler)', 1000)
        }

    }
}