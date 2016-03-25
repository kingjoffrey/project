var WebSocketMessage = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                console.log($('.table').length)
                var table = $('.table')
                for (var i in r.game) {
                    table
                        .append($('<h5>').html(r.game[i].title))
                        .append($('<p>').html(nl2br(r.game[i].content)))

                }
                break
            default:
                console.log(r)
        }
    }
}

function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}