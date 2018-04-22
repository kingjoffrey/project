"use strict"
var Field = function (type) {
    var field = {
        'armies': {},
        'ruinId': 0,
        'towerId': 0,
        'castleId': 0,
        'towerColor': 0,
        'castleColor': 0,
        'type': type,
        'level': 0
    }
    this.setLevel = function (level) {
        field.level = level
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
    this.setTowerColor = function (color) {
        field.towerColor = color
    }
    this.setTower = function (towerId, color) {
        field.towerId = towerId
        field.towerColor = color
    }
    this.getTowerId = function () {
        return field.towerId
    }
    this.getTowerColor = function () {
        return field.towerColor
    }

    this.setCastleId = function (castleId) {
        field.castleId = castleId
    }
    this.setCastleColor = function (color) {
        field.castleColor = color
    }
    this.setCastle = function (castleId, color) {
        field.castleId = castleId
        field.castleColor = color
    }
    this.getCastleId = function () {
        return field.castleId
    }
    this.getCastleColor = function () {
        return field.castleColor
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

    this.setType = function (type) {
        field.type = type
    }
    this.getType = function () {
        return field.type
    }
    this.getLevel = function () {
        return field.level
    }
    this.getTypeWithoutBridge = function () {
        if (field.type == 'b') {
            return 'w'
        }
        return field.type
    }
    this.getGrassOrWater = function () {
        if (field.type == 'b' || field.type == 'w') {
            return 'w'
        } else {
            return 'g'
        }
    }
    this.getSwampAndWater = function () {
        if (field.type == 'w' || field.type == 's') {
            return 1
        } else {
            return 0
        }
    }
    this.getHill = function () {
        if (field.type == 'h' || field.type == 'm') {
            return 1
        } else {
            return 0
        }
    }
    this.getMountain = function () {
        if (field.type == 'm') {
            return 1
        } else {
            return 0
        }
    }
}