var Me = new function () {
    var gold = 0,
        costs = 0,
        income = 0

    this.init = function (me) {
        gold = me.gold
        goldUpdate()
        costs = me.costs
        costsUpdate()
        income = me.income
        incomeUpdate()
    }

    var goldUpdate = function () {
        $('#gold #value').fadeOut(300, function () {
            $('#gold #value').html(gold)
            $('#gold #value').fadeIn()
            if (gold > 1000) {
                $('#heroHire').removeClass('buttonOff')
            } else {
                $('#heroHire').addClass('buttonOff')
            }
        })

    }
    var costsUpdate = function () {
        $('#costs #value').fadeOut(300, function () {
            $('#costs #value').html(gold)
            $('#costs #value').fadeIn(300)
        })
    }
    var incomeUpdate = function () {
        $('#income #value').fadeOut(300, function () {
            $('#income #value').html(income)
            $('#income #value').fadeIn(300)
        })
    }

    this.goldIncrement = function (value) {
        gold += value
        goldUpdate()
    }
    this.costIncrement = function (value) {
        costs += value
        costsUpdate()
    }
    this.incomeIncrement = function (value) {
        income += value
        incomeUpdate()
    }
}