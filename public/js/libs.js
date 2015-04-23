function titleBlink(msg) {
    var timeoutId = Game.getTimeoutId()
    if (timeoutId) {
        clearInterval(timeoutId);
    }
    Game.setTimeoutId(setInterval(function () {
        if (document.title == msg) {
            document.title = '...'
        } else {
            document.title = msg
        }
    }))

    $(document).bind("mousemove keypress", function () {
        clearInterval(Game.getTimeoutId())
        document.title = Gui.getDocumentTitle()
        window.onmousemove = null
    });
}

function makeTime() {
    var d = new Date();
    var minutes = d.getMinutes();

    if (minutes < 10) {
        minutes = '0' + minutes;
    }

    if (minutes.length == 1) {
        minutes = '0' + minutes
    }
    return d.getHours() + ':' + minutes;
}

function getISODateTime(d) {
    // padding function
    var s = function (a, b) {
        return (1e15 + a + "").slice(-b)
    };

    // default date parameter
    if (typeof d === 'undefined' || !d) {
        d = new Date();
    } else {
        d = new Date(d.substr(0, 4), d.substr(5, 2), d.substr(8, 2), d.substr(11, 2), d.substr(14, 2), d.substr(17, 2));
    }

    // return ISO datetime
    return d.getFullYear() + '-' +
        s(d.getMonth() + 1, 2) + '-' +
        s(d.getDate(), 2) + ' ' +
        s(d.getHours(), 2) + ':' +
        s(d.getMinutes(), 2) + ':' +
        s(d.getSeconds(), 2);
}

function isDigit(val) {
    if (typeof val == 'undefined') {
        return false;
    }
    var intRegex = /^\d+$/;
    if (intRegex.test(val)) {
        return true;
    } else {
        return false;
    }
}

function isTruthful(val) {
    if (isSet(val) && val) {
        return true;
    }
    return false;
}

function isSet(val) {
    if (typeof val === 'undefined') {
        return false;
    } else {
        return true;
    }
}

function notSet(val) {
    return !isSet(val);
}

function makeId(lenght) {
    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < lenght; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function countProperties(obj) {
    var count = 0;

    for (var prop in obj) {
        if (obj.hasOwnProperty(prop))
            ++count;
    }

    return count;
}