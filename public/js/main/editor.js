"use strict"
var EditorController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        // content.append(
        //     $('<div>').addClass('table').append(
        //
        //     )
        // )
        //
        // for(var i in data){
        //
        // }
    }
}