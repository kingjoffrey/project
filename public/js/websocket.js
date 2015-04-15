var Websocket = new function () {
    var closed = true,
        queue = {},
        executing = 0,
        i = 0,
        handler = ''

    this.wait = function () {
        if (executing) {
            setTimeout('Websocket.wait()', 500);
        } else {
            for (var ii in queue) {
                Websocket.execute(queue[ii])
                delete queue[ii]
                return
            }
        }
    }
    this.open = function () {
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
    this.message = function (r) {
        console.log(r)
    }
    this.close = function () {
        setTimeout('Websocket.init(handler)', 1000)
    }
    this.addQueue = function (r) {
        i++
        queue[i] = r
        Websocket.wait()
    }
    this.execute = function (r) {
        executing = 1
        console.log(r)
        switch (r.type) {

        }
    }
    this.setExecuting = function (value) {
        executing = value
    }
    this.getI = function () {
        return i
    }
    this.isClosed = function () {
        return closed
    }
    this.init = function (h) {
        handler = h
        ws = new WebSocket(wsURL + '/' + handler)

        ws.onopen = function () {
            closed = false
            Websocket.open()
        }

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r.type)) {
                Websocket.message(r)
            }
        }

        ws.onclose = function () {
            closed = true
            Websocket.close()
        }
    }
}