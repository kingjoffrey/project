"use strict"
var Help = new function () {
    var help,
        text,
        graphics,
        mesh = 0

    this.click = function (id) {
        this.fillText(id)
    }
    this.fillText = function (id) {
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
            case 'castle':
                mesh = Models.addCastle({x: 0, y: -2, defense: 4, name: 'Castle'}, 'orange')
                break
            case 'hero':
                mesh = Models.addHero(0, 0, 'orange')
                break
            case 'tower':
                mesh = Models.addTower(0, 0, 'orange')
                break
            case 'ruin':
                mesh = Models.addRuin(0, 0, 'gold')
                break
            case 'units':
console.log('aaa')
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
    }
}
