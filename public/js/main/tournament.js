"use strict"
var TournamentController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        for (var i in r.list) {
            var tournament = r.list[i]


            $('#tournamentList').append(
                $('<tr>').addClass('trlink').attr('id', tournament.tournamentId)
                    .append($('<td>').html(tournament.start))
                    .append($('<td>').html(tournament.start))
                    .append($('<td>').html(tournament.limit))
                    .click(function () {
                        WebSocketSendMain.controller('tournament', 'show', {'id': $(this).attr('id')})
                    })
            )
        }

        if (notSet(i)) {
            $('#tournamentList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="5">').addClass('after').html(translations.Nothingtoshow)
                        )
                )
        }
    }
    this.show = function (r) {
        $('#content').html(r.data)

        paypal.Button.render({

            // Set up a getter to create a Payment ID using the payments api, on your server side:

            payment: function (resolve, reject) {

                // Make an ajax call to get the Payment ID. This should call your back-end,
                // which should invoke the PayPal Payment Create api to retrieve the Payment ID.

                // When you have a Payment ID, you need to call the `resolve` method, e.g `resolve(data.paymentID)`
                // Or, if you have an error from your server side, you need to call `reject`, e.g. `reject(err)`

                jQuery.post('/my-api/create-payment')
                    .done(function (data) {
                        resolve(data.paymentID);
                    })
                    .fail(function (err) {
                        reject(err);
                    });
            },

            // Pass a function to be called when the customer approves the payment,
            // then call execute payment on your server:

            onAuthorize: function (data) {

                console.log('The payment was authorized!');
                console.log('Payment ID = ', data.paymentID);
                console.log('PayerID = ', data.payerID);

                // At this point, the payment has been authorized, and you will need to call your back-end to complete the
                // payment. Your back-end should invoke the PayPal Payment Execute api to finalize the transaction.

                jQuery.post('/my-api/execute-payment', {paymentID: data.paymentID, payerID: data.payerID})
                    .done(function (data) {
                        /* Go to a success page */
                    })
                    .fail(function (err) {
                        /* Go to an error page  */
                    })
            },

            // Pass a function to be called when the customer cancels the payment

            onCancel: function (data) {

                console.log('The payment was cancelled!');
                console.log('Payment ID = ', data.paymentID);
            }

        }, '#paypal')
    }
}
