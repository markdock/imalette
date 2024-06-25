<?php

namespace Imalette;

class Palette
{

}

class Imalette
{
    // setup: palette
    public function getColor($image)
    {
        $colors = gd_info();
        /*$img = imagecreatefrompng($image);

        $width = imagesx($img);
        $height = imagesy($img);
        $colors = array();

        for ($y = 0; $y < $height; $y++) {
            $y_array = array() ;

            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $x_array = array($r, $g, $b);
                $y_array[] = $x_array;
            } 
            $colors[] = $y_array;
        }*/
        // convert image to RGB
        // use formula to produce dominant color
        // find shortest distance between dominant color and palette color
        // return color in hex
        return $colors;
    }
}