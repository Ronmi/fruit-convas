# Convas

This package is part of Fruit Framework.

Convas is a console drawing library. Currently it supports only UTF-8 encoding.

[![Build Status](https://travis-ci.org/Ronmi/fruit-convas.svg)](https://travis-ci.org/Ronmi/fruit-convas)

## Synopsis

```php
$buf = new Fruit\Convas\Buffer; // create a canvas, origin point is at top-left corner
$g = new Fruit\Convas\Graphics($buf); // allocate graphics object to paint on this canvas
$g
    ->transit(2, 3) // move origin to (2, 3)
    ->drawString(0, 0, 'a string')
    ->drawString(0, 1, '中文') // convas can handle (most of) CJK and graphical characters
    ->drawLine(0, 2, 55, 2) // draw a line from (0, 2) to (55, 2)
    ->drawLine(0, 2, 0, 15) // convas can handle cross point of lines and ellipses
    ->drawEllipse(2, 5, 55, 20) // draw an ellipse within the square (2, 5) (15, 20)
    ->setColor(new Fruit\Convas\Color(1, 37, 41)) // set color
    ->drawString(5, 12, 'highlighted, white text on red background');
echo implode("\n", $buf->exportAll()) . "\n";

$buf->clear(4, 8, 57, 23); // clear the area, take care of the origin point

$g->overwrite = true; // enable overwrite mode, color will always in overwrite mode.
$g->drawLine(5, 2, 5, 15); // no cross point handling
```

[![Lines, Ellipses, Wide characters](http://i.imgur.com/9aDHS6S.png)](http://imgur.com/9aDHS6S)
[![Block of text](http://i.imgur.com/8HjVHi4.png)](http://imgur.com/8HjVHi4)

## Wide characters

Convas detect wide characters (characters occupied two cells in console, most are CJK characters) using unicode blocks. Feel free to file an issue if any wide character is missing.

## Algorithms, PR Plz!

The algorithms used to draw lines and ellipses are too simple to mark as stable. We need your help!

See [Issue#2](https://github.com/Ronmi/fruit-convas/issues/2) and [Issue#3](https://github.com/Ronmi/fruit-convas/issues/3).

## License

Any version of MIT, GPL or LGPL.
