<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Form\SalaryFormType;

class SalaryController extends AbstractController
{
    #[Route('/salary', name: 'app_salary')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SalaryFormType::class);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Assurez-vous de récupérer la valeur correcte du salaire brut
            $salaireBrut = $data['input'];
    
            $body = [
                "situation" => [
                    "salarié . contrat . salaire brut" => [
                        "valeur" => $salaireBrut, // Utilisez la valeur du salaire brut ici
                        "unité" => "€ / mois"
                    ],
                    "salarié . contrat" => "CDI"
                ],
                "expressions" => [
                    "salarié . rémunération . net . à payer avant impôt"
                ]
            ];
    
            $client = HttpClient::create();
            $response = $client->request('POST', 'https://mon-entreprise.urssaf.fr/api/v1/evaluate', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $body
            ]);
    
            $content = $response->getContent();
            $datas = json_decode($content, true);

           // return $this->json($datas);
    
            return $this->render('Salary.html.twig',[
                'data' => $datas['evaluate'],
                ]); // Retourne les données récupérées à la vue
        }
    
        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
            'form' => $form,
        ]);
    }    

}
