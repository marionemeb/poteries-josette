<?php

namespace App\Controller;

use App\Entity\Recipe;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    /**
     * @Route("/pdf/{id}", name="pdf")
     * @param int $id
     */
    public function generate_pdf($id){
        $recipe = new Recipe();
        $recipe = $this->getDoctrine()->getRepository(Recipe::class)->find($id);

        $options = new Options();
        $options->set('defaultFont', 'Roboto');

        $dompdf = new Dompdf($options);

        $data = array(
            'headline' => 'my headline'
        );
        $html = $this->renderView('pdf/index.html.twig', [
            'recipe' => $recipe
        ]);


        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("recette_josette.pdf", [
            "Attachment" => true
        ]);
    }
}
