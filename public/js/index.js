$(document).ready(function () {

    Title.adjust()

    $(window).resize(function () {
        Title.adjust()
    })
})

var Title = {
    adjust: function () {
        var height = $(window).height()
        var title = $('#content #title')

        var top = (height - title.height()) / 2
        if (top < 0) {
            top = 0
        }

        console.log(top)

        title.
            css({
                'margin-top': top + 'px'
            })
    }
}