<?php
namespace App\Services;

use FPDF;

//ici se trouve la classe PDFService qu'on peut utiliser dans le controller 
class PDFService extends FPDF
{
    //Utile si creation de tableau dans le pdf
    // Largeurs des colonnes
    protected $widths;
    // Définir les largeurs
    function SetWidths($w) {
        $this->widths = $w;
    }

    // Fonction principale pour afficher une ligne
    function Row($data,$test) {
        $nb = 0;
        
        // Calcul du nombre de lignes max
        for($i=0; $i<count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 6 * $nb;//5=hauteur de texte

        // Saut de page si nécessaire
        $this->CheckPageBreak($h);

        // Dessin des cellules
        for($i=0; $i<count($data); $i++) {
            $w = $this->widths[$i];
            $x = $this->GetX();
            $y = $this->GetY();

            if($data[$i]!= "" && $test == true){
                // Bordure
                $this->Rect($x, $y, $w, $h);
            }

            // Texte
            $this->MultiCell($w, 5, $data[$i], 0,'C');

            // Repositionnement
            $this->SetXY($x + $w, $y);
        }

        // Aller à la ligne suivante
        $this->Ln($h);
    }

    // Vérifier saut de page
    function CheckPageBreak($h) {
        if($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    // Calcul nombre de lignes d'un texte
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];

        if($w == 0)
            $w = $this->w - $this->rMargin - $this->x;

        $wmax = ($w - 2*$this->cMargin) * 1000 / $this->FontSize;

        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);

        if($nb > 0 && $s[$nb-1] == "\n")
            $nb--;

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while($i < $nb) {
            $c = $s[$i];

            if($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }

            if($c == ' ')
                $sep = $i;

            $l += $cw[$c];

            if($l > $wmax) {
                if($sep == -1) {
                    if($i == $j)
                        $i++;
                } else {
                    $i = $sep + 1;
                }

                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }
}