function refresh() {
    $.getJSON(urlRefresh, function(result) {
        var playersReady = 0;
        for(i in result) {
            if(result[i].ready) {
                playersReady++;
            }
            $('#'+result[i].color+'Id').html(result[i].playerId);
            $('#'+result[i].color+'Ready').html(result[i].ready);
        }
        if(numberOfPlayers <= playersReady) {
            top.location = urlRedirect;
        }
    });
}
$(document).ready(function() {
    setInterval ( 'refresh()', 5000 );
    refresh();
});

