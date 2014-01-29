$(document).ready(function () {
    Editor.init()
})

var Editor = {
    layer: new Kinetic.Layer(),
    map: null,
    init: function () {
        var stage = new Kinetic.Stage({
            container: 'board',
            width: 1600,
            height: 800
        })
        this.map = new Kinetic.Shape({
            drawFunc: function (context) {
                context.beginPath();
                context.moveTo(0, 0);
                context.lineTo(mapWidth, 0);
                context.lineTo(mapWidth, mapHeight);
                context.lineTo(0, mapHeight);
                context.closePath();
                context.fillStrokeShape(this);
            },
            x: 0,
            y: 0,
            fill: 'green',
            draggable: true
        })

        this.layer.add(this.map)
        stage.add(this.layer)

        var ctx = this.layer.getContext()._context
        ctx.fillRect(10, 10, 1, 1)
    },
    Displace: function (num) {
        var max = num / (getSize().width + getSize().height) * 3;
        return (Math.random() - 0.5) * max;
    },

    //Returns a color based on a color value, c.
    ComputeColor: function (c) {
        var Red = 0,
            Green = 0,
            Blue = 0

        if (c < 0.5) {
            Red = c * 2;
        }
        else {
            Red = (1.0 - c) * 2;
        }

        if (c >= 0.3 && c < 0.8) {
            Green = (c - 0.3) * 2;
        }
        else if (c < 0.3) {
            Green = (0.3 - c) * 2;
        }
        else {
            Green = (1.3 - c) * 2;
        }

        if (c >= 0.5) {
            Blue = (c - 0.5) * 2;
        }
        else {
            Blue = (0.5 - c) * 2;
        }

        return new Color(Red, Green, Blue);
    },

    //This is something of a "helper function" to create an initial grid
    //before the recursive function is called.
    drawPlasma: function (width, height) {
        var c1, c2, c3, c4

        //Assign the four corners of the intial grid random color values
        //These will end up being the colors of the four corners of the applet.
        c1 = Math.random();
        c2 = Math.random();
        c3 = Math.random();
        c4 = Math.random();

        DivideGrid(0, 0, width, height, c1, c2, c3, c4)
    },

    //This is the recursive function that implements the random midpoint
    //displacement algorithm.  It will call itself until the grid pieces
    //become smaller than one pixel.
    DivideGrid: function (x, y, width, height, c1, c2, c3, c4) {
        var Edge1, Edge2, Edge3, Edge4, Middle
        newWidth = width / 2,
            newHeight = height / 2

        if (width > 2 || height > 2) {
            Middle = (c1 + c2 + c3 + c4) / 4 + Displace(newWidth + newHeight);        //Randomly displace the midpoint!
            Edge1 = (c1 + c2) / 2;        //Calculate the edges by averaging the two corners of each edge.
            Edge2 = (c2 + c3) / 2;
            Edge3 = (c3 + c4) / 2;
            Edge4 = (c4 + c1) / 2;

            //Make sure that the midpoint doesn't accidentally "randomly displaced" past the boundaries!
            if (Middle < 0) {
                Middle = 0;
            }
            else if (Middle > 1.0) {
                Middle = 1.0;
            }

            //Do the operation over again for each of the four new grids.
            DivideGrid(g, x, y, newWidth, newHeight, c1, Edge1, Middle, Edge4);
            DivideGrid(g, x + newWidth, y, newWidth, newHeight, Edge1, c2, Edge2, Middle);
            DivideGrid(g, x + newWidth, y + newHeight, newWidth, newHeight, Middle, Edge2, c3, Edge3);
            DivideGrid(g, x, y + newHeight, newWidth, newHeight, Edge4, Middle, Edge3, c4);
        }
        else        //This is the "base case," where each grid piece is less than the size of a pixel.
        {
            //The four corners of the grid piece will be averaged and drawn as a single pixel.
            var c = (c1 + c2 + c3 + c4) / 4;

//                        g.setColor(ComputeColor(c));
//                        g.drawRect((int)x, (int)y, 1, 1);        //Java doesn't have a function to draw a single pixel, so
            //a 1 by 1 rectangle is used.
        }
    }
}
