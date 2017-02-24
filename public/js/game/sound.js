var Sound = new function () {
    var mute = false
    this.play = function (name) {
        if (mute) {
            return
        }
        $('#' + name).get(0).play()
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
}