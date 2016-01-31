var CastleWindow = new function () {
    this.form = function (id) {



        var html = $('<div>')
            .append($('<div>').append('Name:').append($('<input>').attr(name, 'name')))
            .append($('<div>').append('Color:').append($('<select>').attr(name, 'color').append($('<option>').attr('value', 1).html(1)).append($('<option>').attr('value', 2).html(2))))
            .append($('<div>').append('Defence:').append($('<select>').attr(name, 'defence').append($('<option>').attr('value', 1).html(1)).append($('<option>').attr('value', 2).html(2)).append($('<option>').attr('value', 3).html(3)).append($('<option>').attr('value', 4).html(4))))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', type: 'submit'})))
            .append($('<hidden>').attr({name: 'id', value: id}))

        return html.html()
    }
}
