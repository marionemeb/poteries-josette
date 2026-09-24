<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    #[Route('/pdf/{id}', name: 'pdf')]
    public function generate_pdf(int $id, RecipeRepository $recipes): Response
    {
        $recipe = $recipes->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException();
        }

        $options = new Options();
        $options->set('defaultFont', 'Roboto');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('pdf/index.html.twig', [
            'recipe' => $recipe
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // dompdf's stream() sends headers and echoes the PDF directly, bypassing
        // Symfony's Response entirely — output() instead returns the PDF bytes so
        // they can go through a normal Response, which Symfony then sends once.
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recette_josette.pdf"',
        ]);
    }
}
