Websocket = {
    closed: true,
    i: 0,
    queue: {},
    executing: 0,
    addQueue: function (r) {
        this.i++
        this.queue[this.i] = r
        this.wait()
    },
    wait: function () {
        if (this.executing) {
            setTimeout('Websocket.wait()', 500);
        } else {
            for (var ii in this.queue) {
                this.execute(this.queue[ii])
                delete this.queue[ii]
                return
            }
        }
    },
    execute: function (r) {
        this.executing = 1

        switch (r.type) {
            case 'dead':
                if (notSet(Players.wedges[r.color].skull)) {
                    Players.drawSkull(r.color)
                }
                this.executing = 0
                break;
        }
    },
    init: function (handler) {
        ws = new WebSocket(wsURL + '/' + handler);

        ws.onopen = function () {
            Websocket.closed = false;
            Websocket.open();
        };

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r['type'])) {

                switch (r.type) {

                    case 'move':
                        Websocket.addQueue(r)
                        break;

                    default:
                        console.log(r);

                }
            }
        }

        ws.onclose = function () {
            Websocket.closed = true
            setTimeout('Websocket.init()', 1000)
        }

    },
    open: function () {
        if (Websocket.closed) {
            Message.error(translations.sorryServerIsDisconnected)
            return;
        }

        var token = {
            type: 'open',
            playerId: my.id,
            langId: langId,
            accessKey: accessKey,
            websocketId: websocketId
        }

        ws.send(JSON.stringify(token));
    }
}
