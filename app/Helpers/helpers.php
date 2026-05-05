<?php
//ici se trouve les fonctions utilitaires

    //conversion nombre en lettre
    function nombreEnLettres($nombre){

        if($nombre == 0){
            return "zero";
        }

        $unites = [
            0=>"zero",1=>"un",2=>"deux",3=>"trois",4=>"quatre",
            5=>"cinq",6=>"six",7=>"sept",8=>"huit",9=>"neuf",
            10=>"dix",11=>"onze",12=>"douze",13=>"treize",
            14=>"quatorze",15=>"quinze",16=>"seize"
        ];

        $dizaines = [
            20=>"vingt",30=>"trente",40=>"quarante",
            50=>"cinquante",60=>"soixante"
        ];

        // --- < 17 ---
        if($nombre < 17){
            return $unites[$nombre];
        }

        // --- < 20 ---
        if($nombre < 20){
            return "dix-".$unites[$nombre-10];
        }

        // --- < 70 ---
        if($nombre < 70){
            $dizaine = floor($nombre/10)*10;
            $reste = $nombre % 10;

            if($reste == 1){
                return $dizaines[$dizaine]." et un";
            } elseif($reste > 0){
                return $dizaines[$dizaine]."-".$unites[$reste];
            } else {
                return $dizaines[$dizaine];
            }
        }

        // --- < 80 ---
        if($nombre < 80){
            return "soixante-".nombreEnLettres($nombre-60);
        }

        // --- < 100 ---
        if($nombre < 100){
            return "quatre-vingt".($nombre==80 ? "s" : "-".nombreEnLettres($nombre-80));
        }

        // --- < 1000 ---
        if($nombre < 1000){
            $centaine = floor($nombre/100);
            $reste = $nombre % 100;

            $texte = ($centaine == 1) ? "cent" : $unites[$centaine]." cent";

            if($reste == 0 && $centaine > 1){
                $texte .= "s"; // deux cents
            }

            if($reste > 0){
                $texte .= " ".nombreEnLettres($reste);
            }

            return $texte;
        }

        // --- < 1 000 000 ---
        if($nombre < 1000000){
            $mille = floor($nombre/1000);
            $reste = $nombre % 1000;

            $texte = ($mille == 1) ? "mille" : nombreEnLettres($mille)." mille";

            if($reste > 0){
                $texte .= " ".nombreEnLettres($reste);
            }

            return $texte;
        }

        // --- < 1 000 000 000 ---
        if($nombre < 1000000000){
            $million = floor($nombre/1000000);
            $reste = $nombre % 1000000;

            $texte = ($million == 1) ? "un million" : nombreEnLettres($million)." millions";

            if($reste > 0){
                $texte .= " ".nombreEnLettres($reste);
            }

            return $texte;
        }

        // --- milliards ---
        if($nombre < 1000000000000){
            $milliard = floor($nombre/1000000000);
            $reste = $nombre % 1000000000;

            $texte = ($milliard == 1) ? "un milliard" : nombreEnLettres($milliard)." milliards";

            if($reste > 0){
                $texte .= " ".nombreEnLettres($reste);
            }

            return $texte;
        }

        return "nombre trop grand";
    }

    // Fonction formatage nombre
    function formatNumber($nb) {
        return number_format($nb, 2, ',', ' ');//2 chiffre apres virgule; ',' comme séparateur décimal et ' ' comme séparateur de milliers
    }
