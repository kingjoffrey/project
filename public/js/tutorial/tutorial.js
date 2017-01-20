"use strict"
var Tutorial = new function () {
    var number = 0,
        step = 0,
        steps = [
            new Array(
                {
                    goal: 'Set production',
                    description: 'To lead the conquest you need to have an army. To have an army you need to produce units in castles (there should be open window of your capital castle properties). Click "Start" button to set unit production (you will get new unit in next turn).'
                },
                {
                    'goal': 'Change turn',
                    'description': 'When you have done everything (eg.: your armies have no moves left) you are ready to end your turn. Click next turn button ">>" in the right-top corner to change turn.'
                },
                {
                    'goal': 'Move army',
                    'description': 'This is your new turn. You should have new unit in your castle. Click on the army to select it (now when you move your mouse you should see green wheels on the ground, green wheels show the path that will be followed by the army). Than move your army by clicking on the ground.'
                },
                {
                    'goal': 'Conquer "Shadow" castle',
                    'description': 'Now when you have army and you know how to move it, it is time to conquer some castles. Move your army into castle with grey flag to attack it.'
                },
                {
                    'goal': 'Set unit relocation',
                    'description': 'You can relocate unit production from one castle to another. Go to your first castle, click on relocate production button and after that click on you second castle.'
                },
                {
                    'goal': 'Try to win',
                    'description': 'There should be only two castle left which doesn\'t belong to you. To win you have to conquer all of them.'
                }
            ),
            new Array(
                {
                    'goal': 'Make ship',
                    'description': 'Enter into castle management and set ship production. After setting ship production change turn 6 times (until ship is produced).'
                },
                {
                    'goal': 'Load hero on ship',
                    'description': 'Select your hero and move him into ship position.'
                },
                {
                    'goal': 'Swim to shore near ruins and unload hero on shore',
                    'description': 'Select ship and move it toward ruins. Yellow thing in right bottom corner.'
                },
                {
                    'goal': 'Take hero to ruins',
                    'description': 'Unload hero from ship and move him onto ruins position.'
                },
                {
                    'goal': 'Search ruins',
                    'description': 'When you hero is standing in the same position as ruins location order him to search it. After search you should get 3 dragons which can fly and take you hero over water.'
                },
                {
                    'goal': 'Try to win',
                    'description': 'There is 5 castles on the map. To win you have to conquer all of them.'
                }
            ),
            new Array(
                {
                    'goal': 'Improve castle defense to 2',
                    'description': 'Your castle does not provide sufficient protection. You have to improve castle defense. Select army which is inside castle (if there is no army inside castle produce one or move existing one) and order it to build defense.'
                },
                {
                    'goal': 'Take over all towers',
                    'description': 'Every unit take some amount of gold from your treasure so to build strong army you will need a lot of gold. You can get gold from castles and towers. To increase your gold income you need to take control over towers. There is 8 towers on this map and when you will control all of them it will give you 40 gold every turn.'
                },
                {
                    'goal': 'Improve castle defense to 4',
                    'description': 'Your castle does not provide sufficient protection. You have to improve castle defense to maximum.'
                },
                {
                    'goal': 'Create one flying unit',
                    'description': 'Flying units added to army boosts up attack of every unit in the army by one. It doesn\'t matter how many flying units are in the army because army attack can only be increased by one.'
                },
                {
                    'goal': 'Try to win',
                    'description': 'When your castle is secure and you have sufficient income you can build your army and attack enemy castle. Don\'t forget to leave part of you troops as garrison of the castle.'
                }
            )
        ]

    this.showDescription = function () {
        if (isSet(steps[number]) && isSet(steps[number][step])) {
            var stepPlus = step * 1 + 1,
                numberPlus = number * 1 + 1,
                html = $('<div>')
                    .append($('<div>').html('Goal ' + stepPlus + '/' + steps[number].length + ': ' + steps[number][step].goal).addClass('goal'))
                    .append($('<div>').html(steps[number][step].description))
            Message.tutorial('Tutorial ' + numberPlus + '/' + steps.length, html.html())
        }
    }
    this.changeStep = function (s) {
        step = s

        this.showDescription()

        $('#tutorial').html('Goal: ' + steps[number][step].goal)
    }
    this.init = function (t) {
        console.log(t)
        number = t.number

        var tutorial = $('<div id="tutorial">').click(function () {
            Tutorial.showDescription()
        })

        $('#terrain').after(tutorial)

        this.changeStep(t.step)
    }
}