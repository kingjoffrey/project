$(document).ready(function() {
    ws.onmessage = function(e) {
        var r=$.parseJSON( e.data );
        function clb(){};

        if(typeof r['type'] != 'undefined'){

            switch(r.type){

                case 'move':
                    removeM();
                    if(typeof r.data == 'undefined' || typeof r.color == 'undefined'){
                        console.log('?');
                        return;
                    }
                    walk(r.data, r.color, r.data.deletedIds);
                    break;

                case 'fightNeutralCastle':
                    zoomer.lensSetCenter(r.x*40, r.y*40);

                    battleM(r.battle, r.attackerColor, r.defenderColor, clb);

                    if(r.victory) {
                        players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
                        if(r.attackerColor==my.color){
                            newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
                            newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;
                        }
                        castleOwner(r.castleId, r.attackerColor);
                    } else {
                        deleteArmy('army' + r.attackerArmy.armyId, r.attackerColor);
                    }

                    if(r.attackerColor==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemyCastle':
                    zoomer.lensSetCenter(r.x*40, r.y*40);

                    battleM(r.battle, r.attackerColor, r.defenderColor, clb);

                    if(r.victory) {
                        players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
                        if(r.attackerColor==my.color){
                            newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
                            newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;
                        }
                        for(i in r.defenderArmy) {
                            deleteArmy('army' + r.defenderArmy[i].armyId, r.defenderColor);
                        }
                        castleOwner(r.castleId, r.attackerColor);
                    } else {
                        for(i in r.defenderArmy){
                            players[r.defenderColor].armies['army'+r.defenderArmy[i].armyId] = new army(r.defenderArmy[i], r.defenderColor);
                        }
                        deleteArmy('army' + r.attackerArmy.armyId, r.attackerColor);
                    }

                    if(r.attackerColor==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemy':
                    zoomer.lensSetCenter(r.x*40, r.y*40);

                    $.when(battleM(r.battle, r.attackerColor, r.defenderColor, clb)).then(function(){
                        if(r.victory) {
                            players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
                            if(r.attackerColor==my.color){
                                newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
                                newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;
                            }
                            for(i in r.defenderArmy) {
                                deleteArmy('army' + r.defenderArmy[i].armyId, r.defenderColor);
                            }
                        } else {
                            for(i in r.defenderArmy){
                                players[r.defenderColor].armies['army'+r.defenderArmy[i].armyId] = new army(r.defenderArmy[i], r.defenderColor);
                            }
                            deleteArmy('army' + r.attackerArmy.armyId, r.attackerColor);
                        }

                        if(r.attackerColor==my.color){
                            unselectEnemyArmy();
                            unlock();
                        }
                    });
                    break;

                case 'computer':
                    console.log(r);
                    removeM();

                    if(typeof r.data.oldArmyId == 'undefined'){
                        //                        wsPlayerArmies(turn.color);
                        //                        $.when(getPlayerArmiesA(turn.color)).then(computerA());
                        $.when(wsPlayerArmies(turn.color)).then(wsComputer());
                    }else if(typeof r.data.path != 'undefined'){
                        //                        waitOn();
                        enemyWalk(r.data);
                    }else{
                        wsComputer();
                    }
                    break;

                case 'computerEnd':
                    changeTurn(r.data.color, r.data.nr);
                    if(players[r.data.color].computer){
                        wsComputer();
                    }
                    break;

                case 'computerGameover':
                    wsComputer();
                    break;

                case 'army':
                    if(typeof r.data.armyId != 'undefined') {
                        if(typeof players[r.data.color].armies['army' + r.data.armyId] != 'undefined'){
                            armyFields(players[r.data.color].armies['army' + r.data.armyId]);
                        }
                        players[r.data.color].armies['army' + r.data.armyId] = new army(r.data, r.data.color);
                        if(r.data.center == 1){
                            removeM();
                            zoomer.lensSetCenter(players[r.data.color].armies['army' + r.data.armyId].x*40, players[r.data.color].armies['army' + r.data.armyId].y*40);
                        }
                    }
                    break;

                case 'ruin':
                    zoomer.lensSetCenter(players[r.color].armies['army' + r.data.army.armyId].x*40, players[r.color].armies['army' + r.data.army.armyId].y*40);
                    players[r.color].armies['army' + r.data.army.armyId] = new army(r.data.army, r.color);
                    ruinUpdate(r.data.ruin.ruinId, r.data.ruin.empty);
                    if(my.color==r.color){
                        switch(r.data.find[0]){
                            case 'gold':
                                var gold = r.data.find[1] + parseInt($('#gold').html());
                                goldUpdate(gold);
                                simpleM('You have found '+r.data.find[1]+' gold.');
                                break;
                            case 'death':
                                simpleM('You have found death.');
                                break
                            case 'alies':
                                simpleM(r.data.find[1]+' alies joined your army.');
                                break
                            case 'null':
                                simpleM('You have found nothing.');
                                break
                            case 'artefact':
                                simpleM('You have found an ancient artefact.');
                                break
                            case 'empty':
                                simpleM('Ruins are empty.');
                                break;

                        }
                    }
                    break;

                case 'armies':
                    for(i in r.data){
                        players[r.color].armies[i] = new army(r.data[i], r.color);
                    }
                    break;

                case 'splitArmy':
                    removeM();
                    players[r.color].armies['army'+r.data.parentArmy.armyId] = new army(r.data.parentArmy, r.color);
                    setParentArmy(players[r.color].armies['army'+r.data.parentArmy.armyId]);
                    players[r.color].armies['army'+r.data.childArmy.armyId] = new army(r.data.childArmy, r.color);
                    if(my.color==turn.color){
                        selectArmy(players[r.color].armies['army'+r.data.childArmy.armyId]);
                    }
                    else{
                        zoomer.lensSetCenter(r.data.parentArmy.x*40, r.data.parentArmy.y*40);
                    }
                    break;

                case 'joinArmy':
                    removeM();
                    zoomer.lensSetCenter(r.data.army.x*40, r.data.army.y*40);
                    deleteArmyByPosition(r.data.army.x, r.data.army.y, r.color);
                    players[r.color].armies['army'+r.data.army.armyId] = new army(r.data.army, r.color);
                    break;

                case 'disbandArmy':
                    if(typeof r.data.armyId != 'undefined'){
                        removeM();
                        deleteArmy('army' + r.data.armyId, r.color);
                    }
                    break;

                case 'heroResurrection':
                    removeM();
                    zoomer.lensSetCenter(r.data.army.x*40, r.data.army.y*40);
                    players[r.color].armies['army'+r.data.army.armyId] = new army(r.data.army, r.color);
                    if(my.color==turn.color){
                        goldUpdate(r.data.gold);
                    }
                    break;

                case 'open':
                    webSocketOpen(r.wssuid);
                    break;

                case 'chat':
                    if(r.msg){
                        titleBlink('Incoming chat!');
                        chat(r.color,r.msg,makeTime());
                    }
                    break;

                case 'turn':
                    unselectArmy();
                    if(r.data['lost']){
                        lostM();
                    }else{
                        //                        if(!data[3]){
                        //                            unset(data[3]);
                        //                        }
                        changeTurn(r.data['color'], r.data['nr']);
                        wsComputer();
                    }
                    break;

                case 'castle':
                    castleUpdate(r.data);
                    castleOwner(r.data.castleId, r.data.color);
                    if(r.data.color==my.color){
                        removeM();
                        goldUpdate(r.data.gold);
                    }
                    break;

                default:
                    console.log(r);

            }
        }
    };

});

function wsCastleBuildDefense(){
    var castleId = $('input[name=defense]:checked').val();
    if(!castleId) {
        return;
    }
    var token = {
        type: 'castleBuildDefense',
        data: {
            castleId:castleId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsRazeCastle() {
    var castleId = $('input[name=raze]:checked').val();
    if(!castleId) {
        return;
    }
    var token = {
        type: 'razeCastle',
        data: {
            castleId:castleId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsNextTurn() {
    var token = {
        type: 'turn',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChat() {
    var msg = $('#msg').val();
    $('#msg').val('');
    if(msg){
        chat(my.color,msg,makeTime());

        var token = {
            type: 'chat',
            data: msg,
            gameId: gameId,
            playerId: my.id,
            color: my.color,
            accessKey: lAccessKey
        };

        ws.send(JSON.stringify(token));
    }
}

function wsPlayerArmies(color){
    var token = {
        type: 'armies',
        data:{
            color:color
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsArmyMove(x, y, armyId) {
    var token = {
        type: 'move',
        data:{
            x: x,
            y: y,
            armyId: armyId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

//function wsArmy(armyId, center) {
//    var token = {
//        type: 'army',
//        gameId: gameId,
//        playerId: my.id,
//        color: my.color,
//        accessKey: lAccessKey
//    };
//    if(center){
//        token['data']={
//            armyId: armyId,
//            center: 1
//        };
//    }else{
//        token['data']={
//            armyId: armyId,
//            center: 0
//        };
//    }
//    ws.send(JSON.stringify(token));
//}

function wsSplitArmy(armyId) {
    if(!my.turn){
        return;
    }
    var h = '';
    var s = '';
    $('.message input[type="checkbox"]:checked').each(function() {
        if($(this).attr('name') == 'heroId'){
            if(h){
                h += ',';
            }
            h += $(this).val();
        }else{
            if(s){
                s += ',';
            }
            s += $(this).val();
        }
    });
    var token = {
        type: 'splitArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            s:s,
            h:h
        }
    };

    ws.send(JSON.stringify(token));
}

function wsDisbandArmy() {
    if(!my.turn){
        return;
    }
    if(selectedArmy == null){
        return;
    }
    unselectArmy(1);

    var token = {
        type: 'disbandArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:unselectedArmy.armyId,
            x:unselectedArmy.x,
            y:unselectedArmy.y
        }
    };

    ws.send(JSON.stringify(token));
}

function wsHeroResurrection(castleId) {
    if(!my.turn){
        return;
    }
    unselectArmy();

    var token = {
        type: 'heroResurrection',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsJoinArmy(armyId1, armyId2){
    if(!my.turn){
        return;
    }

    var token = {
        type: 'joinArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId1:armyId1,
            armyId2:armyId2
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightNeutralCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightNeutralCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemyCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightEnemyCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemy(armyId, x, y, enemyArmyId){
    var token = {
        type: 'fightEnemy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            enemyArmyId:enemyArmyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsSearchRuins(){
    if(!my.turn){
        return;
    }
    if(selectedArmy == null){
        return;
    }
    unselectArmy();
    var token = {
        type: 'ruin',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:unselectedArmy.armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsComputer(){
    if(!my.game){
        return
    }
    if(!players[turn.color].computer){
        return;
    }

    var token = {
        type: 'computer',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}
//function wsBattle(battle,army,armies){
//    var data = my.color+'.b.';
//    var tmp = '';
//    for(i in battle){
//        if(typeof battle[i].soldierId != 'undefined'){
//            if(tmp){
//                tmp += ',';
//            }
//            tmp += 's'+battle[i].soldierId;
//        }
//        if(typeof battle[i].heroId != 'undefined'){
//            if(tmp){
//                tmp += ',';
//            }
//            tmp += 'h'+battle[i].heroId;
//        }
//    }
//    data += tmp+'.'+army.color+','+army.armyId;
//    tmp = '';
//    if(typeof armies != 'undefined'){
//        if(typeof armies[0] != 'undefined' && typeof armies[0].color != 'undefined' && armies[0].color == 'neutral'){
//            tmp = 'n';
//        }else{
//            for(i in armies){
//                if(tmp){
//                    tmp += '|';
//                }
//                tmp += armies[i].armyId;
//            }
//            if(tmp){
//                tmp += ','+armies[i].color;
//            }
//        }
//    }
//    if(tmp){
//        data += '.'+tmp;
//    }
//    lWSC.channelPublish(channel,data);
//}