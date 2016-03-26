<?php

class Admin_View_Helper_JavascriptLang extends Zend_View_Helper_Abstract
{

    public function javascriptLang($controllerName)
    {
        $script = '
$().ready(function () {
    Lang.init()
})

var Lang = new function () {
    var get = function () {
        var data = {
            "id": $("#id").val(),
            "id_lang": $("#lang #id_lang").val(),
            "c": "' . $controllerName . '"
        }
        $.ajax({
            type: "POST",
            url: "/admin/ajaxlang/get",
            data: data,
            success: function (r) {
                var result = $.parseJSON(r);
                $("#lang #name").val(result.name);
            }
        })
    }
    this.init = function () {
        get()
        $("#zapisz").click(function () {
            var data = {
                "id": $("#id").val(),
                "id_lang": $("#lang #id_lang").val(),
                "c": "' . $controllerName . '"
            }
            $.ajax({
                type: "POST",
                url: "/admin/ajaxlang/save",
                data: data,
                success: function (r) {
                    var result = $.parseJSON(r)
                    if (result) {
                    console.log(result)
                        alert("Zapisano")
                    }
                }
            })
        })

        $("#lang #id_lang").change(function () {
            get()
        })
    }
}';

        $this->view->headScript()->appendScript($script);
    }

}
