"use strict"
var Help = new function () {
    var help,
        text

    this.click = function (id) {
        $('#helpMenu div').removeClass('off')
        $('#' + id).addClass('off')
        this.fillText(help[id])
        switch (id) {
            case 'castle':
                Models.addCastle({x: 0, y: -2, defense: 4,name:'Castle'}, 'orange')
                break
            case 'tower':
                Models.addTower(0, 0, 'orange')
                break
            case 'ruin':
                Models.addRuin(0, 0, 'gold')
                break
        }
    }
    this.fillText = function (action) {
        text.html('')
        for (var i in action) {
            text
                .append($('<h5>').html(action[i].title))
                .append($('<p>').html(Help.nl2br(action[i].content)))
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

        text = $('#text')

        WebSocketHelp.init()
    }
}
