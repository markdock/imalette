# Imalette

Trouve la couleur d'une image sur une palette prédéfinie.

## Table des Matières

- [Présentation](#présentation)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Exemples](#exemples)
- [Contribuer](#contribuer)
- [Licence](#licence)

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
