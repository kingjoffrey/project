var Message = {
    drag: 0,
    x: 0,
    y: 0,
    element: function () {
        return $('#goldBox')
    },
    remove: function (id) {
        if (isSet(id)) {
            $('#' + id).fadeOut(200, function () {
                this.remove();
            })
        } else {
            if (notSet($('.message'))) {
                return;
            }
            $('.message').remove();
        }
    },
    show: function (title, txt) {
        this.remove()
        var id = makeId(10)
        this.element().after(
            $('<div>')
                .addClass('message box')
                .append($('<h3>').html(title).addClass('msgTitle'))
                .append($(txt).addClass('overflow'))
                .attr('id', id)
                .fadeIn(200)
        )
        this.adjust(id)
        return id
    },
    adjust: function (id) {
        var maxHeight = Three.getHeight() - 140,
            maxWidth = Three.getWidth() - 480

        if (isSet(id)) {
            if (maxHeight < parseInt($('#' + id).css('min-height'))) {
                maxHeight = parseInt($('#' + id).css('min-height'))
            }
            if (maxWidth < parseInt($('#' + id).css('min-width'))) {
                maxWidth = parseInt($('#' + id).css('min-width'))
            }

            $('#' + id).css({
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px'
            })

            var left = Three.getWidth() / 2 - $('#' + id).outerWidth() / 2

            if (left < $('#mapBox').width() + 30) {
                left = $('#mapBox').width() + 30
            }

            $('#' + id).css({
                left: left + 'px'
            })
        } else if ($('.message').length) {
            if (maxHeight < parseInt($('.message').css('min-height'))) {
                maxHeight = parseInt($('.message').css('min-height'))
            }
            if (maxWidth < parseInt($('.message').css('min-width'))) {
                maxWidth = parseInt($('.message').css('min-width'))
            }

            $('.message').css({
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px'
            })

            var left = Three.getWidth() / 2 - $('.message').outerWidth() / 2;

            if (left < $('#mapBox').width() + 30) {
                left = $('#mapBox').width() + 30
            }

            $('.message').css({
                left: left + 'px'
            })
        }
    },
    setOverflowHeight: function (id) {
        var minus = 0
        if (isSet(id)) {
            var height = $('#' + id).height() - minus;
            $('#' + id + ' div.overflow').css('height', height + 'px')
        } else {
            var height = $('.message').height() - minus;
            $('.message' + ' div.overflow').css('height', height + 'px')
        }
        if (Me.isSelected()) {
            Me.setIsSelected(0)
        }
    },
    ok: function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors go')
                .html(translations.ok)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        );

        this.setOverflowHeight(id)
    },
    cancel: function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.cancel)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        )
    },
    simple: function (title, message) {
        var id = this.show(title, $('<div>').html(message).addClass('simple'));
        this.ok(id)
        return id
    },
    error: function (message) {
        Sound.play('error');
        var div = $('<div>').html(message).addClass('error')
        this.simple(translations.error, div);
    },
    surrender: function () {
        var id = this.show(translations.surrender, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.surrender);
        this.cancel(id)
    },
    nextTurn: function () {
        var id = this.show(translations.nextTurn, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.nextTurn);
        this.cancel(id)
    },
    disband: function () {
        var id = this.show(translations.disbandArmy, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.disband);
        this.cancel(id)
    },
    raze: function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }
        var id = this.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.raze);
        this.cancel(id)
    },
    build: function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())

        if (castle.getDefense() == 4) {
            var div = $('<div>')
                .append($('<h3>').html(translations.maximumCastleDefenceReached))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
            var id = this.show(translations.buildCastleDefense, div);
        } else {
            var costBuildDefense = 0;
            for (i = 1; i <= castle.getDefense(); i++) {
                costBuildDefense += i * 100;
            }
            var newDefense = castle.getDefense() + 1;

            var div = $('<div>')
                .append($('<h3>').html(translations.doYouWantToBuildCastleDefense))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
                .append($('<div>').html(translations.newDefense + ': ' + newDefense))
                .append($('<div>').html(translations.cost + ': ' + costBuildDefense + ' ' + translations.gold))
            var id = this.show(translations.buildCastleDefense, div);
            this.ok(id, Websocket.defense);
        }
        this.cancel(id)
    },
    end: function () {
        var div = $('<div>')
            .append($('<div>').html(translations.thisIsTheEnd))
        var id = this.show(translations.gameOver, div)
        this.ok(id, Gui.end)
    },
    hire: function () {
        var id = this.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
        this.ok(id, Websocket.hire)
        this.cancel(id)
    },
    resurrection: function () {
        var id = this.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
        this.ok(id, Websocket.resurrection)
        this.cancel(id)
    },
    battleConfiguration: function (type) {
        var sequenceNumber = $('<div>'),
            sequenceImage = $('<div>').attr('id', 'sortable'),
            i = 0

        for (k in Me.getBattleSequence(type)) {
            var unitId = Me.getBattleSequence(type)[k],
                unit = Units.get(unitId)
            if (unit.canFly) {
                continue
            }
            if (unit.canSwim) {
                continue
            }
            i++
            if (isSet(unit.name_lang)) {
                var name = unit.name_lang
            } else {
                var name = unit.name
            }
            sequenceNumber
                .append($('<div>').html(i).addClass('battleNumber'))
            sequenceImage
                .append(
                $('<div>')
                    .append($('<img>').attr({
                        src: Unit.getImage(unitId, Me.getColor()),
                        id: unitId,
                        alt: name
                    }))
                    .addClass('battleUnit')
            )
        }

        return sequenceNumber.add(sequenceImage)
    },
    battleAttack: function () {

        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleAttackSequenceByMovingUnits))
            .append($('<br>'))
            .append(this.battleConfiguration('attack'))

        var id = this.show(translations.battleConfiguration, div)
        this.ok(id, Websocket.battleAttack)
        this.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    },
    battleDefence: function () {

        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleDefenceSequenceByMovingUnits))
            .append($('<br>'))
            .append(this.battleConfiguration('defense'))

        var id = this.show(translations.battleConfiguration, div)
        this.ok(id, Websocket.battleDefence)
        this.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    }
}
