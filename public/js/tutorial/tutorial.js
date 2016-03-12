"use strict"
var Tutorial = new function () {
    var number = 0,
        step = 0,
        steps = {
            0: {
                0: {
                    goal: 'Set production',
                    description: 'To lead the conquest you need to have an army. To have an army you need to produce units in castles.'
                },
                1: {
                    'goal': 'Change turn',
                    'description': 'When you have done everything (eg.: your armies have no moves left) you are ready to end your turn. Click on wheel in the right-top corner to change turn.'
                },
                2: {
                    'goal': 'Move army',
                    'description': 'This is your new turn. You should have new unit in your castle. Click on the army to select it. Now when you move your mouse you should see green wheels on the ground. They show the path that will be followed by the army. Move your army by clicking on the ground.'
                },
                3: {
                    'goal': 'Conquer "Shadow" castle',
                    'description': 'Now when you have army it is time to conquer some castles. Move your army into castle with grey flag.'
                },
                4: {
                    'goal': 'Set unit relocation',
                    'description': 'You can relocate unit production from one castle to another. Go to your first castle, click on relocate production button and after that click on you second castle.'
                },
                5: {
                    'goal': 'Win',
                    'description': 'There should be only two castle left which doesn\'t belong to you. To win you have to conquer one of them.'
                }
            },
            1: {
                0: {
                    'goal': 'Make ship',
                    'description': ''
                },
                1: {
                    'goal': 'Load hero on ship',
                    'description': ''
                },
                2: {
                    'goal': 'Swim to shore near ruins',
                    'description': ''
                },
                3: {
                    'goal': 'Unload hero on shore',
                    'description': ''
                },
                4: {
                    'goal': 'Take hero to ruins',
                    'description': ''
                },
                5: {
                    'goal': 'Search ruins',
                    'description': ''
                },
                6: {
                    'goal': 'Conquer all castles',
                    'description': ''
                }
            },
            2: {
                0: {
                    'goal': 'Improve castle defense to 4',
                    'description': 'Your castle does not provide sufficient protection. You have to Improve castle defense to maximum.'
                },
                1: {
                    'goal': 'Take over all towers',
                    'description': ''
                },
                2: {
                    'goal': 'Win',
                    'description': ''
                }
            }
        }

    this.showDescription = function () {
        Message.simple('Tutorial', steps[number][step].description)
    }
    this.changeStep = function (s) {
        step = s
        this.showDescription()
        var html = $('<div>'),
            add
        for (var i in steps[number]) {
            if (i < step) {
                add = '+'
            } else if (i == step) {
                add = '>'
            } else {
                add = '-'
            }
            html.append($('<div>').append(add + ' ' + steps[number][i].goal))
        }
        $('#limitBox').html(html)
    }
    this.init = function (t) {
        console.log(t)
        number = t.number
        this.changeStep(t.step)
    }
}