<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CompanyFormType;

class CompanyFormController extends AbstractController
{

    #[Route('/company-form', name: 'company-form')]
    public function index(Request $request)
    {
        $form = $this->createForm(CompanyFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitez les données du formulaire ici

            return $this->json([
                'status' => 'success',
                'message' => 'Form submitted successfully',
            ]);
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
