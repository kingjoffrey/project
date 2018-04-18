var EditorRuinWindow = new function () {
    this.form = function (id) {
        var array = ['Attack', 'Defense', 'Moves', 'Random search']


        var select = $('<select>').attr('name', 'type')

        var ruin = Ruins.get(id)

        for (var i = 0; i < 4; i++) {
            if (ruin.getType() == i + 1) {
                select.append($('<option>').attr({
                    'value': ruin.getType(),
                    'selected': 'selected'
                }).html(array[i]))
            } else {
                select.append($('<option>').attr({
                    'value': i + 1
                }).html(array[i]))
            }
        }


        var html = $('<div>').addClass('editorRuinWindow')
            .append($('<div>').html('Ruin type'))
            .append($('<div>').append(select))
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                WebSocketSendEditor.editRuin(id)
            })))

        return html
    }
}
