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
                    'description': 'Enter into castle management and star ship production. After setting ship production change turn 6 times until ship will be produced.'
                },
                1: {
                    'goal': 'Load hero on ship',
                    'description': 'Select your hero and move him into ship position.'
                },
                2: {
                    'goal': 'Swim to shore near ruins and unload hero on shore',
                    'description': 'Select ship and move it toward ruins. Yellow thing in right bottom corner.'
                },
                3: {
                    'goal': 'Take hero to ruins',
                    'description': 'Unload hero from ship and step onto ruins position.'
                },
                4: {
                    'goal': 'Search ruins',
                    'description': 'When you hero is standing in the same position as ruins you can order him to search it. You should get 3 dragons which can fly and take you hero over water.'
                },
                5: {
                    'goal': 'Win',
                    'description': 'There is 5 castles on the map. To win you have to conquer 3 of them.'
                }
            },
            2: {
                0: {
                    'goal': 'Improve castle defense to 2',
                    'description': 'Your castle does not provide sufficient protection. You have to improve castle defense. Select army which is inside castle (if there is no army inside castle produce one or move existing one) and order it to build defense.'
                },
                1: {
                    'goal': 'Take over all towers',
                    'description': 'Every unit take some amount of gold from your treasure so to build strong army you will need a lot of gold. You can get gold from castles and towers. To increase your gold income you need to take control over towers. There is 8 towers on this map and when you will control all of them it will give you 40 gold every turn.'
                },
                2: {
                    'goal': 'Improve castle defense to 4',
                    'description': 'Your castle does not provide sufficient protection. You have to improve castle defense to maximum.'
                },
                3: {
                    'goal': 'Create one flying unit',
                    'description': 'Flying units added to army boosts up attack of every unit in the army by one. It doesn\'t matter how many flying units are in the army because army attack can only be increased by one.'
                },
                4: {
                    'goal': 'Win',
                    'description': 'When your castle is secure and you have sufficient income you can build your army and attack enemy castle. Don\'t forget to leave part of you troops as garrison of the castle.'
                }
            }
        }

    this.showDescription = function () {
        if (isSet(steps[number]) && isSet(steps[number][step])) {
            Message.info('Tutorial', steps[number][step].description)
        }
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