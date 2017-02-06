$(document).ready(function () {
    Page.init()
    Chat.init()
})

var Page = new function () {
    var index = '',
        touch = 0,
        shadows = 1


    this.getIndex = function () {
        return index
    }
    this.getShadows = function () {
        return shadows
    }
    this.hasTouch = function () {
        return touch
    }


    this.init = function () {
        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        if (!index) {
            index = $('#content').html()
        }

        if (isSet(window.orientation)) {
            touch = 'ontouchstart' in document.documentElement
            shadows = 0

            // $(document).on('touchstart', function (e) {
            //     e.preventDefault()
            // })
        }

        $(document).on('click', function (e) {
            e.preventDefault()
            console.log(e)
        })
    }
}
