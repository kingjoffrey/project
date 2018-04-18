var EditorRuinWindow = new function () {
    this.form = function (id) {

        var html = $('<div>').addClass('editorRuinWindow')
            .append(
                $('<div>')
                    .append('Ruin type: ')
                    .append(
                        $('<select>')
                            .attr('name', 'type')
                            .append($('<option>').attr('value', 1).html('Attack'))
                            .append($('<option>').attr('value', 2).html('Defense'))
                            .append($('<option>').attr('value', 3).html('Moves'))
                            .append($('<option>').attr('value', 4).html('Random search'))
                    )
            )
            .append($('<div>').append($('<input>').attr({'value': 'Ok', 'type': 'submit'}).click(function () {
                WebSocketSendEditor.editRuin(id)
            })))

        return html
    }
}
