$(document).ready(function () {
    $('.trlink').each(function () {
        var id = $(this).attr('id')
        $(this).click(function () {
            window.location = '/' + lang + '/profile/show/playerId/' + id
        })
    })
})