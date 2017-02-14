$(document).ready(function () {
    $('#lang').change(function () {
        window.location = '/' + $(this).val() + '/login'
    })

    var url_parts = window.location.pathname.split('/')

    $('#lang option').each(function () {
        if (url_parts[1] == $(this).val()) {
            $(this).attr('selected', '')
        }
    })
})
