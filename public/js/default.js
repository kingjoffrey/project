$(document).ready(function () {

    Page.adjust()

    $(window).resize(function () {
        Page.adjust()
    })

    $('#bg').scroll(function () {
        var x = $(this).scrollTop();
        $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
    })
})

var Page = {
    adjust: function () {
        var height = $(window).height()

        $('#page').css({
            'min-height': height + 'px'
        })
    }
}