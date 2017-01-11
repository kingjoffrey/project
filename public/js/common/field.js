"use strict"
var Field = function (type) {
    var field = {
        'armies': {},
        'ruinId': 0,
        'towerId': 0,
        'castleId': 0,
        'towerColor': 0,
        'castleColor': 0,
        'type': type
    }
    this.setRuinId = function (ruinId) {
        field.ruinId = ruinId
    }
    this.getRuinId = function () {
        return field.ruinId
    }
    this.setTowerId = function (towerId) {
        field.towerId = towerId
    }
    this.getTowerId = function () {
        return field.towerId
    }
    this.setCastleId = function (castleId) {
        field.castleId = castleId
    }
    this.getCastleId = function () {
        return field.castleId
    }
    this.removeArmyId = function (armyId) {
        delete field.armies[armyId]
        //console.log(field.armies)
    }
    this.addArmyId = function (armyId, color) {
        field.armies[armyId] = color
        //console.log(field.armies)
    }
    this.getArmies = function () {
        return field.armies
    }
    this.hasArmies = function () {
        for (var armyId in field.armies) {
            return armyId
        }
    }
    this.getTowerColor = function () {
        return field.towerColor
    }
    this.setTowerColor = function (color) {
        field.towerColor = color
    }
    this.getCastleColor = function () {
        return field.castleColor
    }
    this.setCastleColor = function (color) {
        field.castleColor = color
    }
    this.getTypeWithoutBridge = function () {
        if (field.type == 'b') {
            return 'w'
        }
        return field.type
    }
    this.getType = function () {
        return field.type
    }
    this.setType = function (type) {
        field.type = type
    }
    this.setCastle = function (castleId, color) {
        field.castleId = castleId
        field.castleColor = color
    }
    this.setTower = function (towerId, color) {
        field.towerId = towerId
        field.towerColor = color
    }
}