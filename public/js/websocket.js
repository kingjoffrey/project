var Websocket = {
    closed: true,
    queue: {},
    executing: 0,
    i: 0,
    handler: '',
    init: function (handler) {
        this.handler = handler
        ws = new WebSocket(wsURL + '/' + handler)

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
            setTimeout('Websocket.init(Websocket.handler)', 1000)
        }

    },
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

                this.executing = 0
                break;
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
            accessKey: websocket.accessKey,
            websocketId: websocket.websocketId
        }

        ws.send(JSON.stringify(token));
    }
}