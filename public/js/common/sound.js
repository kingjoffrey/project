var Sound = new function () {
    var mute = false,
        volume = .1
    this.play = function (name) {
        if (mute) {
            return
        }

        var sound = $('#' + name).get(0)

        sound.volume = volume
        sound.play()
    }
    this.isPlaying = function (name) {
        return !$('#' + name).get(0).paused
    }
    this.setMute = function (m) {
        mute = m
    }
    this.getMute = function () {
        return mute
    }
    this.setVolume = function (v) {
        volume = v
    }
}