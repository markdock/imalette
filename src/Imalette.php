<?php

namespace Imalette;

function ImaletteError($message) {
    echo "<script>console.error('Imalette ERROR: ".$message." !');</script>";
}

class Imalette
{
    public function findColor(Palette $palette, $image, $ignore_color = array(250, 250, 250, '>=')) {
        // get colors of image
        $colors = $this->getColors($image, $ignore_color);

        // find dominant color out of 20 centric points
        $dominant = $this->findDominant($colors, 20);

        // get palette color
        $palcol = $this->findPaletteCol($palette, $dominant);

        //return $this->rgb2hex($dominant);
        return $palcol;
    }
    public function getColors($path, $ignore_color = array(250, 250, 250, '>=')) {
        // load image
        $pathInfo = pathinfo($path);
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : null;
        if($extension == null) {
            ImaletteError("L'image n'a aucun format");
            return false;
        }
        switch ($extension) {
            case 'jpg':
            case 'JPG':
            case 'jpeg':
            case 'JPEG':
                $image = imagecreatefromjpeg($path);
                break;

            case 'png':
            case 'PNG':
                $image = imagecreatefrompng($path);
                break;
    
            case 'gif':
            case 'GIF':
                $image = imagecreatefromgif($path);
                break;
    
            case 'bmp':
            case 'BMP':
                $image = imagecreatefrombmp($path);
                break;
    
            case 'webp':
            case 'WEBP':
                $image = imagecreatefromwebp($path);
                break;
    
            default:
                ImaletteError("L'image n'a pas le bon format: png, jpg, jpeg, gif, bmp ou webp");
                return false;
        }
        if(!$image) {
            ImaletteError("Image pas trouvée");
            return false;
        }

        // rescale for more efficient color analysis
        $image = imagescale($image, 100);
        $width = imagesx($image);
        $height = imagesy($image);

        // fetch all colors, but exclude invisible, too transparent and ignore_color
        $colors = array();
        $first_pixel = null;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $a = ($rgb >> 24) & 0x7F;
                if($a > 63) continue;
                if($x == 0 && $y == 0) $first_pixel = $rgb;
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $skip = false;
                if($ignore_color == -1) {
                    if($first_pixel != null) {
                        $r2 = ($first_pixel >> 16) & 0xFF;
                        $g2 = ($first_pixel >> 8) & 0xFF;
                        $b2 = $first_pixel & 0xFF;
                        if($r == $r2 && $g == $g2 && $b == $b2) $skip = true;
                    }
                } else {
                    if(!is_array($ignore_color) || count($ignore_color) < 4) {
                        ImaletteError("Did not pass an array 'ignore_color' with at least 4 elements to findColor or getColors");
                        return false;
                    }

                    switch ($ignore_color[3]) {
                        case '<':
                            if($r < $ignore_color[0] && $g < $ignore_color[1] && $b < $ignore_color[2]) $skip = true;
                            break;
            
                        case '>':
                            if($r > $ignore_color[0] && $g > $ignore_color[1] && $b > $ignore_color[2]) $skip = true;
                            break;
                
                        case '<=':
                            if($r <= $ignore_color[0] && $g <= $ignore_color[1] && $b <= $ignore_color[2]) $skip = true;
                            break;
                
                        case '>=':
                            if($r >= $ignore_color[0] && $g >= $ignore_color[1] && $b >= $ignore_color[2]) $skip = true;
                            break;
                
                        case '==':
                            if($r == $ignore_color[0] && $g == $ignore_color[1] && $b == $ignore_color[2]) $skip = true;
                            break;
    
                        case '!=':
                            if($r != $ignore_color[0] || $g != $ignore_color[1] || $b != $ignore_color[2]) $skip = true;
                            break;
                
                        default:
                            break;
                    }
                }
                if($skip) continue;

                array_push($colors, array($r, $g, $b));
            }
        }
        return $colors;
    }
    protected function findDominant($colors, $k = 10) {
        // initial setup
        $centric_points = array();
        $clusters = array();
        for($i = 0; $i < $k; $i++) {
            $point = array();
            array_push($point, rand(0, 255));
            array_push($point, rand(0, 255));
            array_push($point, rand(0, 255));
            array_push($centric_points, $point);
            array_push($clusters, array());
        }

        $loop = 0;
        while($loop < 1000) {
            // reset cluster
            for($i = 0; $i < $k; $i++) {
                $clusters[$i] = array();
            }

            // find closest centric point for each pixel
            for($i = 0; $i < sizeof($colors); $i++) {
                $shortest_distance = -1;
                $closest_Cpoint = -1;
                for($j = 0; $j < sizeof($centric_points); $j++) {
                    $distance = $this->getDistance($colors[$i], $centric_points[$j]);
                    if ($shortest_distance == -1 || $distance < $shortest_distance) {
                        $shortest_distance = $distance;
                        $closest_Cpoint = $j;
                    }
                }
                array_push($clusters[$closest_Cpoint], $colors[$i]);
            }

            // calculate the mean within each cluster
            $change = false;
            for($i = 0; $i < sizeof($clusters); $i++) {
                $lab_mean = array(0, 0, 0);
                if(sizeof($clusters[$i]) != 0) {
                    for($j = 0; $j < sizeof($clusters[$i]); $j++) {
                        $lab_mean[0] += $clusters[$i][$j][0];
                        $lab_mean[1] += $clusters[$i][$j][1];
                        $lab_mean[2] += $clusters[$i][$j][2];
                    }
                    $lab_mean[0] /= sizeof($clusters[$i]);
                    $lab_mean[1] /= sizeof($clusters[$i]);
                    $lab_mean[2] /= sizeof($clusters[$i]);

                    if($lab_mean !== $centric_points[$i]) {
                        $centric_points[$i] = $lab_mean;
                        $change = true;
                    }
                } else {
                    $point = array();
                    array_push($point, rand(0, 255));
                    array_push($point, rand(0, 255));
                    array_push($point, rand(0, 255));
                    $centric_points[$i] = $point;
                }
            }

            if(!$change) break;
            $loop++;
        }

        // find the biggest cluster
        $dominant = array();
        $cluster_qty = -1;
        for($i = 0; $i < sizeof($clusters); $i++) {
            if($cluster_qty == -1 || sizeof($clusters[$i]) > $cluster_qty) {
                $cluster_qty = sizeof($clusters[$i]);
                $dominant = $centric_points[$i];
            }
        }

        return $dominant;
    }
    protected function findPaletteCol(Palette $palette, $dominant) {
        $colors = $palette->getPalette();
        for($i = 0; $i < sizeof($colors); $i++) {
            $colors[$i] = $this->hex2rgb($colors[$i]);
        }

        $shortest_distance = -1;
        $palette_color = -1;
        for($i = 0; $i < sizeof($colors); $i++) {
            $distance = $this->getDistance($colors[$i], $dominant);
            if ($shortest_distance == -1 || $distance < $shortest_distance) {
                $shortest_distance = $distance;
                $palette_color = $i;
            }
        }
        
        return $palette->getColor($palette_color);
    }
    public function getDistance($p1, $p2) {
        return sqrt(pow($p1[0] - $p2[0], 2) + pow($p1[1] - $p2[1], 2) + pow($p1[2] - $p2[2], 2));
    }
    public function rgb2hex($rgb) {
        return sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
    }
    public function hex2rgb($hex) {
        $hex = ltrim($hex, '#');
        $rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        return $rgb;
    }
    public function rgb2lab($rgb) {
        // convert RGB to XYZ
        $rgb[0] = $rgb[0]/255;
        $rgb[1] = $rgb[1]/255;
        $rgb[2] = $rgb[2]/255;
        $rgb[0] = ($rgb[0] > 0.04045) ? pow((($rgb[0] + 0.055) / 1.055), 2.4) : ($rgb[0] / 12.92);
        $rgb[1] = ($rgb[1] > 0.04045) ? pow((($rgb[1] + 0.055) / 1.055), 2.4) : ($rgb[1] / 12.92);
        $rgb[2] = ($rgb[2] > 0.04045) ? pow((($rgb[2] + 0.055) / 1.055), 2.4) : ($rgb[2] / 12.92);
        $xyz = array();
        array_push($xyz, $rgb[0] * 0.4124564 + $rgb[1] * 0.3575761 + $rgb[2] * 0.1804375);
        array_push($xyz, $rgb[0] * 0.2126729 + $rgb[1] * 0.7151522 + $rgb[2] * 0.0721750);
        array_push($xyz, $rgb[0] * 0.0193339 + $rgb[1] * 0.1191920 + $rgb[2] * 0.9503041);
        $xyz[0] = $xyz[0] / 0.95047;
        $xyz[1] = $xyz[1] / 1.00000;
        $xyz[2] = $xyz[2] / 1.08883;

        // convert XYZ to L*a*b*
        $xyz[0] = ($xyz[0] > 0.008856) ? pow($xyz[0], 1/3) : (7.787 * $xyz[0]) + (16 / 116);
        $xyz[1] = ($xyz[1] > 0.008856) ? pow($xyz[1], 1/3) : (7.787 * $xyz[1]) + (16 / 116);
        $xyz[2] = ($xyz[2] > 0.008856) ? pow($xyz[2], 1/3) : (7.787 * $xyz[2]) + (16 / 116);
        $lab = array();
        array_push($lab, (116 * $xyz[1]) - 16);
        array_push($lab, 500 * ($xyz[0] - $xyz[1]));
        array_push($lab, 200 * ($xyz[1] - $xyz[2]));

        return $lab;
    }
    public function lab2rgb($lab) {
        // Convert Lab to XYZ
        $xyz = array();
        array_push($xyz, 0);
        array_push($xyz, ($lab[0] + 16) / 116);
        $xyz[0] = $lab[1] / 500 + $xyz[1];
        array_push($xyz, $xyz[1] - $lab[2] / 200);
        $xyz[0] = ($xyz[0] > 0.206897) ? pow($xyz[0], 3) : ($xyz[0] - 16 / 116) / 7.787;
        $xyz[1] = ($xyz[1] > 0.206897) ? pow($xyz[1], 3) : ($xyz[1] - 16 / 116) / 7.787;
        $xyz[2] = ($xyz[2] > 0.206897) ? pow($xyz[2], 3) : ($xyz[2] - 16 / 116) / 7.787;
        $xyz[0] = $xyz[0] * 95.047;
        $xyz[1] = $xyz[1] * 100.000;
        $xyz[2] = $xyz[2] * 108.883;

        // Convert XYZ to RGB
        $xyz[0] = $xyz[0] / 100;
        $xyz[1] = $xyz[1] / 100;
        $xyz[2] = $xyz[2] / 100;
        $rgb = array();
        array_push($rgb, $xyz[0] * 3.2406 + $xyz[1] * -1.5372 + $xyz[2] * -0.4986);
        array_push($rgb, $xyz[0] * -0.9689 + $xyz[1] * 1.8758 + $xyz[2] * 0.0415);
        array_push($rgb, $xyz[0] * 0.0557 + $xyz[1] * -0.2040 + $xyz[2] * 1.0570);
        $rgb[0] = ($rgb[0] > 0.0031308) ? 1.055 * pow($rgb[0], 1 / 2.4) - 0.055 : 12.92 * $rgb[0];
        $rgb[1] = ($rgb[1] > 0.0031308) ? 1.055 * pow($rgb[1], 1 / 2.4) - 0.055 : 12.92 * $rgb[1];
        $rgb[2] = ($rgb[2] > 0.0031308) ? 1.055 * pow($rgb[2], 1 / 2.4) - 0.055 : 12.92 * $rgb[2];
        $rgb[0] = round(max(0, min(255, $rgb[0] * 255)));
        $rgb[1] = round(max(0, min(255, $rgb[1] * 255)));
        $rgb[2] = round(max(0, min(255, $rgb[2] * 255)));
        
        return $rgb;
    }
}