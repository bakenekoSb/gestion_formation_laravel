<?php

namespace App\Http\Controllers;

/*
    "use"(laravel) equivalent de "require"(php)
    "$request"(laravel) equivalent de "$_POST"(php)
    DB Laravel au lieu de PDO
*/
//appel de fonction existant qu'on va utiliser (equivalent de "import" en java)
use Illuminate\Http\Request;
use App\Services\PDFService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class FactureController extends Controller
{
    //ici je definis les fonctions qui vont etre appeler dans les routes (web.php)
    //fonction pour afficher le formulaire de facture
    public function showForm()
    {
        $formations = DB::table('formations')->get(); //prend les données de la table formations
        return view('facture', compact('formations'));//le rend sous form =e  tableau associatif avec comme clé le nom de la variable et comme valeur les données
    }

    //fonction pour generer la facture en pdf
    public function generate(Request $request){
        //récuperation des données du formulaire
        $client = $request->input('nom', 'Client inconnu');
        $adresse = $request->input('adresse', 'Adresse inconnue');
        $date_debut = $request->input('date_debut');
        $date_fin = $request->input('date_fin');
        $designation = DB::table('formations')
            ->where('id_formation', $request->input('designation'))
            ->value('titre_formation');
        if($designation == null){
            $designation = 'Formation inconnue';
        }
        $montant_unitaire = (double)($request->input('prix', 0));
        $indemnite = (double)($request->input('indemnite', 0));
        $tva = (double)($request->input('tva', 0));
        $date_facture = Carbon::now()->format('d/m/Y');
        $date1 = Carbon::now()->format('Y-m');

        //calcul jour de formation
        if ($date_debut && $date_fin) {
            $d1 = Carbon::parse($date_debut);
            $d2 = Carbon::parse($date_fin);

            $quantite = $d1->diffInDays($d2) + 1; // inclut les deux dates
        } else {
            $quantite = 0;
        }

        //calcul montant
        $montant_service = $quantite * $montant_unitaire;
        $montant_indemnite = $quantite * $indemnite;
        $tva_total = ($montant_service + $montant_indemnite) * ($tva / 100);
        $total = $montant_service + $montant_indemnite + $tva_total;

        // Créer PDF
        $pdf = new PDFService('P','mm','A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetMargins(15,15,15);
        $pdf->SetAutoPageBreak(true,15);

        // ====================================
        // EN-TÊTE SOCIÉTÉ
        // ====================================
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,5,'Entreprise:',0,1);
        $pdf->Cell(0,-5,'GASY TECH',0,1,'C');
        $pdf->Cell(0,6,'Antananarivo le '.$date_facture,0,0,'R');
        $pdf->SetFont('Arial','U',11);
        $pdf->Cell(0,6,$date_facture,0,1,'R');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,'Adresse:',0,1);
        $pdf->Cell(0,-10,'Anjanahary Antananarivo, Madagascar',0,1,'C');
        $pdf->Cell(0,28,'NIF/STAT:',0,1);
        $pdf->Cell(0,-28,'6005717692/74908',0,1,'C');
        $pdf->SetFont('Arial','U',11);
        $pdf->Cell(0,45,'RCS:',0,1);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,-45,'112021 007713',0,1,'C');
        $pdf->Cell(0,48,'Doit',0,1,'R');
        $pdf->SetFont('Arial','U',11);
        $pdf->Cell(0,-33,'Email:',0,1,);
        $pdf->SetTextColor(0,100,255);
        $pdf->Cell(0,33,'contact@gasy-tech.com',0,1,'C');
        $pdf->SetFont('Arial','',11);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(0,-33,$client,0,1,'R');
        $pdf->Cell(0,45,$adresse,0,1,'R');

        //prendre position de y
        $y = $pdf->GetY();
        $pdf->SetDrawColor(0,150,200);
        $pdf->SetLineWidth(1);
        $pdf->Rect(50,$y-18, 120, 28);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(39,0,'',0,0);
        $pdf->Cell(55,-25,'Date de formation achevee : ',0,0);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,-25,$date_debut.' au '.$date_fin,0,1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(40,0,'',0,0);
        $pdf->Cell(40,40,'Num Proforma GT : ',0,0,'C');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,40,'PRO-F'.$date1,0,1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(41,0,'',0,0);
        $pdf->Cell(25,-25,'Num de BC : ',0,0,'C');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,-25,'DREECI/IOE-03.03.N0250011',0,1);
        $pdf->Ln(25);

        // ====================================
        // TABLEAU PRESTATIONS
        // ====================================
        $pdf->SetLineWidth(0);
        $pdf->SetDrawColor(0,0,0);
        // Définir les largeurs
        $pdf->SetWidths([100, 25, 30, 35]);
        $test = true;
        $pdf->SetFont('Arial','B',11);
        // En-tête
        $pdf->Row(["Designation", "Quantite (Nb de jours)", "Montant en ariary", "Montant Total (TTC)"],$test);

        $pdf->SetFont('Arial','',11);
        //Données
        $pdf->Row([$designation, $quantite, formatNumber($montant_unitaire), formatNumber($montant_service)],$test);
        $pdf->Row(['Indeminite de repas et transport', $quantite, formatNumber($indemnite), formatNumber($montant_indemnite)],$test);
        $pdf->Row(['TVA', $tva.'%', formatNumber($tva_total), formatNumber($tva_total)],$test);
        $pdf->Row(['','','Total', formatNumber($total)],$test);
        $pdf->Ln(5);
        // ====================================
        // MONTANT EN LETTRES
        //ucfirst(); pour faire en majuscule la première lettre
        // ====================================
        $lettre = nombreEnLettres((int)$total);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(43,8,'Arrete a la somme de ',0,0);
        $pdf->SetFont('Arial','',11);
        $pdf->MultiCell(0,8,'" '.ucfirst($lettre).' ariary "',0);
        $pdf->Ln(9);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,0,'Conditions de paiement',0,1);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,'Les modes de paiements accptes sont le virement bancaire et Orange Money',0,1);
        $pdf->Ln(10);

        //prendre position de y
        $y = $pdf->GetY();

        $pdf->Rect(10,$y-5, 195, 45);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,0,'Details bancaire',0,1);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,15,' - BANQUE : BRED Madagasikara',0,1);
        $pdf->Cell(0,0,' - RIB : 00008 00024 05003023618 71',0,1);
        $pdf->Cell(0,15,' - NOM : GASY TECH',0,1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(50,0,'Numero Orange money: ',0,0);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,0,'032 05 504 93',0,1);
        $pdf->Ln(20);

        // ====================================
        // FOOTER
        // ====================================
        $x = $pdf->GetX();
        $pdf->SetWidths([$x,55,40, 75]);
        $test = false;
        $pdf->SetFont('Arial','U',11);
        $pdf->Row(['','Le Client','','Le Fournisseur'],$test);
        $pdf->Ln(15);
        //$pdf->Row(['','','','']);
        $pdf->SetFont('Arial','',11);
        $pdf->Row(['','','','RAMAHEFARITOLOTRA Rafaly Antoni CEO et Gerant de Gasy Tech'],$test);

        /* ====================================
        // OUTPUT
        | Mode  | Signification                                  |
        | ----- | ---------------------------------------------- |
        | `'I'` | Afficher dans le navigateur                    |
        | `'D'` | Forcer le téléchargement                       |
        | `'F'` | Sauvegarder dans un fichier                    |
        | `'S'` | Retourner le PDF en string (texte binaire)     |

        S = FPDF donne juste le PDF → Laravel décide car il a besoin de garder le controle de la reponse http lui-même
        On ne fait pas I ou D car
        - FPDF envoie directement des headers HTTP
        - Laravel n’a plus le contrôle de la réponse
        - peut provoquer conflits (headers already sent)
        - FPDF force les headers et Laravel ne peut pas ajouter ses propres headers

        response() de Laravel nous permet de construire une réponse HTTP personnalisée
        200 = OK ; cad qu'ici je dis à Laravel : voici la réponse (le PDF) et tout est correct
        par defaut c'est 200 donc $pdf->Output('S') suffit, mais c'est mieux de le préciser pour la clarté du code

        HTTP Status Codes :
        - 200 OK : La requête a réussi et la réponse contient le résultat attendu.
        - 400 Bad Request : La requête est mal formée ou contient des données invalides.
        - 401 Unauthorized : L'authentification est requise et a échoué ou n'a pas été fournie.
        - 403 Forbidden : Le serveur a compris la requête, mais refuse de l'exécuter. L'utilisateur n'a pas les permissions nécessaires.
        - 404 Not Found : La ressource demandée n'a pas été trouvée sur le serveur.
        - 422 Validation error : utilisé pour les formulaires invalides, indique que les données envoyées ne respectent pas les règles de validation définies.
        - 500 Internal Server Error : Une erreur inattendue s'est produite sur le serveur lors du traitement de la requête.
        // ====================================*/
        $filename = 'facture_gasy_tech_' . Carbon::now()->format('Y-m-d_His') . '.pdf';
        if($request->has('btn_apercu')) {
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf');
        }else if($request->has('btn_telecharge')){
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }
    }
}
