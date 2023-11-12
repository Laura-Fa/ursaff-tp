<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

use App\Form\CompanyFormType;

class CompanyFormController extends AbstractController
{

    /**
     * Creer le formulaire
     * Consommer l'API du gouvernement pour retourner la liste des entreprises correspondant a l'entree utilisateur
     *
     * @param Request $request
     * @return void
     */
    #[Route('/', name: 'company-form')]
    public function index(Request $request)
    {
        $form = $this->createForm(CompanyFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $input = $data['input'];

            // Requeter l'API
            $client = HttpClient::create();
            $response = $client->request('GET', 'https://recherche-entreprises.api.gouv.fr/search?q=' . $input);

            // Reponse de l'API
            $content = $response->getContent();

            // Convertir le json en array
            $data = json_decode($content, true);

            // Retourner le resultat a la vue (limite a 10 entreprises)
            return $this->render('companyList.html.twig', [
                'data' => array_slice($data['results'], 0, 10)
            ]);
        }

        return $this->render('form/companySearch.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
