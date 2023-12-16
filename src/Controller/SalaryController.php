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
    #[Route('/salary/cdi', name: 'app_salary')]
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
                        "valeur" => $salaireBrut,
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
    
            return $this->render('Salary.html.twig',[
                'data' => $datas['evaluate'],
                'type'  => 'cdi',
            ]);
        }
    
        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
            'form' => $form,
        ]);
    }  

    #[Route('/salary/stage', name: 'app_stage_salary')]
    public function stage(Request $request): Response
    {
        $form = $this->createForm(SalaryFormType::class);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            
            // Assurez-vous de récupérer la valeur correcte du salaire brut
            $gratification = $datas['input'];
    
            $body = [
                "situation" => [
                    "salarié . contrat . stage . gratification minimale" => [
                        "valeur" => $gratification,
                        "unité" => "€ / mois"
                    ],
                    "salarié . contrat" => "stage"
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
            $datass = json_decode($content, true);
    
            return $this->render('Salary.html.twig',[
                'data' => $datass['evaluate'],
                'type'  => 'stage',
            ]);
        }
    
        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
            'form' => $form,
        ]);
    }

    #[Route('/salary/alternance', name: 'app_alternance_salary')]
    public function alternance(Request $request): Response
    {
        $form = $this->createForm(SalaryFormType::class);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            
            // Assurez-vous de récupérer la valeur correcte du salaire brut
            $salaire = $datas['input'];
    
            $body = [
                "situation" => [
                    "salarié . contrat . salaire brut" => [
                        "valeur" => $salaire,
                        "unité" => "€ / mois"
                    ],
                    "salarié . contrat" => "apprentissage"
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
            $datass = json_decode($content, true);
    
            return $this->render('Salary.html.twig',[
                'data' => $datass['evaluate'],
                'type'  => 'alternance',
            ]);
        }
    
        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
            'form' => $form,
        ]);
    }

    #[Route('/salary/cdd', name: 'app_cdd_salary')]
    public function cdd(Request $request): Response
    {
        $form = $this->createForm(SalaryFormType::class);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            
            // Assurez-vous de récupérer la valeur correcte du salaire brut
            $salairecdd = $datas['input'];
    
            $body = [
                "situation" => [
                    "salarié . contrat . salaire brut" => [
                        "valeur" => $salairecdd,
                        "unité" => "€ / mois"
                    ],
                    "salarié . contrat" => "CDD"
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
            $datasss = json_decode($content, true);
    
            return $this->render('Salary.html.twig',[
                'data' => $datasss['evaluate'],
                'type'  => 'cdd',
            ]);
        }
    
        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
            'form' => $form,
        ]);
    }
}

