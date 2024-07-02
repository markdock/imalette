<?php

namespace Imalette;

function PaletteError($message) {
    echo "<script>console.error('Palette ERROR: ".$message." !');</script>";
}

class Palette {
    private $palette;

    public function __construct() {
        $this->palette = array();
    }
    public function addColorRGB($r, $g, $b) {
        if($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255) {
            PaletteError("invalid color values passed to addColorRGB. They have to be between 0 and 255");
        }
        array_push($this->palette, sprintf("#%02x%02x%02x", $r, $g, $b));
    }
    public function addColorRGBarray($rgb) {
        if (!is_array($rgb) || count($rgb) < 3) {
            PaletteError("Did not pass an array with at least 3 elements to addColorRGBarray");
            return false;
        }
        if($rgb[0] < 0 || $rgb[0] > 255 || $rgb[1] < 0 || $rgb[1] > 255 || $rgb[2] < 0 || $rgb[2] > 255) {
            PaletteError("invalid color values passed to addColorTGBarray. They have to be between 0 and 255");
        }
        array_push($this->palette, sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]));
    }
    public function addColorHEX($hex) {
        if(mb_substr($hex, 0, 1) != "#") $hex = '#'.$hex;
        if (strlen($hex) == 4) {
            $hex = "#".str_repeat(substr($hex, 1, 1), 2).
                str_repeat(substr($hex, 2, 1), 2).
                str_repeat(substr($hex, 3, 1), 2);
        } elseif (strlen($hex) != 7) {
            PaletteError("Did not pass valid HEX to addColorHEX");
            return false;
        }
        array_push($this->palette, $hex);
    }
    public function getPalette() {
        return $this->palette;
    }
    public function getColor($id) {
        return $this->palette[$id];
    }
}