var DiamondSquare = {
    make: function (DATA_SIZE) {
        var SEED = 128.0;
        var data = [];
        for (var i = 0; i < DATA_SIZE; ++i) {
            data[i] = [];
        }
        data[0][0] = data[0][DATA_SIZE - 1] = data[DATA_SIZE - 1][0] = data[DATA_SIZE - 1][DATA_SIZE - 1] = SEED;

        var h = 128.0;//the range (-h -> +h) for the average offset

        for (var sideLength = DATA_SIZE - 1; sideLength >= 2; sideLength /= 2, h /= 2.0) {
            var halfSide = sideLength / 2;

            for (var x = 0; x < DATA_SIZE - 1; x += sideLength) {
                for (var y = 0; y < DATA_SIZE - 1; y += sideLength) {
                    var avg = data[x][y] + //top left
                        data[x + sideLength][y] +//top right
                        data[x][y + sideLength] + //lower left
                        data[x + sideLength][y + sideLength];//lower right
                    avg /= 4.0;

                    data[x + halfSide][y + halfSide] = parseInt(avg + (Math.random() * 2 * h) - h)
                }
            }

            for (var x = 0; x < DATA_SIZE - 1; x += halfSide) {
                for (var y = (x + halfSide) % sideLength; y < DATA_SIZE - 1; y += sideLength) {
                    var avg =
                        data[(x - halfSide + DATA_SIZE - 1) % (DATA_SIZE - 1)][y] + //left of center
                            data[(x + halfSide) % (DATA_SIZE - 1)][y] + //right of center
                            data[x][(y + halfSide) % (DATA_SIZE - 1)] + //below center
                            data[x][(y - halfSide + DATA_SIZE - 1) % (DATA_SIZE - 1)]; //above center

                    avg /= 4.0;

                    avg = parseInt(avg + (Math.random() * 2 * h) - h)
                    data[x][y] = avg;

                    if (x == 0) {
                        data[DATA_SIZE - 1][y] = avg;
                    }

                    if (y == 0) {
                        data[x][DATA_SIZE - 1] = avg;
                    }
                }
            }
        }

        return data;
    }
}
