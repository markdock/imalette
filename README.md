# Imalette

Trouve la couleur d'une image sur une palette prédéfinie.

## Table des Matières

- [Présentation](#présentation)
- [Installation](#installation)
- [Utilisation](#utilisation)

## Présentation

Imalette utilise l'algorithme de "K-means clustering" pour analyser les couleurs d'une image, les regrouper et identifier le groupe de couleurs le plus représentatif.

Ensuite, à partir d'une palette de couleurs fournie, il calcule la distance euclidienne entre la couleur prédominante et chaque couleur de la palette afin de déterminer la couleur la plus proche.

## Installation

1. Assurez-vous que PHP est installé sur votre système. La bibliothèque GD est requise pour le traitement d'images. (http://www.webassist.com/tutorials/Enabling-the-GD-library-setting)

2. Installez avec composer:
    ```sh
    cd votre/projet/php
    composer require markdock/imalette
    ```

## Utilisation

1. Créez une palette
2. Utilisez la fonction findColor(palette, image) pour identifier la couleur

    ```php
    <?php
    use Imalette\Imalette;
    use Imalette\Palette;

    // Image
    $image = "images/image.png";

    // Palette
    $palette = new Palette();
    $palette->addColorRGB(0, 0, 0); // noir
    $palette->addColorRGB(127, 127, 127); // gris
    $palette->addColorRGB(255, 255, 255); // blanc
    $palette->addColorRGBarray(array(255, 0, 0)); // rouge
    $palette->addColorRGBarray(array(0, 255, 0)); // vert
    $palette->addColorRGBarray(array(0, 0, 255)); // bleu
    $palette->addColorRGBarray(array(255, 255, 0)); // jaune
    $palette->addColorRGBarray(array(255, 0, 255)); // magenta
    $palette->addColorRGBarray(array(0, 255, 255)); // cyan
    $palette->addColorHEX("#FFA500"); // Orange
    $palette->addColorHEX("#800080"); // Violet
    $palette->addColorHEX("#FFC0CB"); // Rose
    $palette->addColorHEX("#A52A2A"); // Marron
    $palette->addColorHEX("#00FF00"); // Lime
    $palette->addColorHEX("#808000"); // Olive
    $palette->addColorHEX("#800000"); // Bordeaux
    $palette->addColorHEX("#000080"); // Bleu marine
    $palette->addColorHEX("#008080"); // Sarcelle
    $palette->addColorHEX("#C0C0C0"); // Argent
    $palette->addColorHEX("#FFD700"); // Or

    // Imalette
    $imalette = new Imalette();
    $palcol = $imalette->findColor($palette, $image); // La couleur en HEX (exemple: #808000)
    ?>
    ```

3. Autres fonctions:

    ```php
    // Palette
    $palette = $palette->getPalette(); // array avec toutes les couleurs en HEX
    $palette = $palette->getColor(3); // récupère une couleur id = [ 0 - (n-1) ]

    // Imalette
    $colors = $imalette->getColors($image); // array contenant l'ensemble des pixels de l'image en RGB
    $distance = $imalette->getDistance($p1, $p2); // calcule la distance Euclidenne entre deux points
    $hex = $imalette->rgb2hex($rgb); // conversion rgb -> hex
    $rgb = $imalette->hex2rgb($rgb); // conversion hex -> rgb
    $lab = $imalette->rgb2lab($rgb); // conversion rgb -> lab
    $rgb = $imalette->lab2rgb($rgb); // conversion lab -> rgb
    ```
