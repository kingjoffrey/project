"use strict"
var Field = function (field) {
    field.armies = {}
    field.ruinId = 0
    field.towerId = 0
    field.castleId = 0
    field.towerColor = 0
    field.castleColor = 0

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
    this.setTowerColor = function (towerColor) {
        field.towerColor = towerColor
    }
    this.getCastleColor = function () {
        return field.castleColor
    }
    this.setCastleColor = function (color) {
        field.castleColor = color
    }
    this.getType = function () {
        return field.type
    }
    this.setType = function (type) {
        field.type = type
    }
}