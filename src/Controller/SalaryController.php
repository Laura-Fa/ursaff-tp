<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SalaryFormType;

class SalaryController extends AbstractController
{
    #[Route('/salary', name: 'app_salary')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SalaryFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $data = $form->getData();
            // $salarynet = $data * 0.78;

            // ... perform some action, such as saving the task to the database

        return $this->json(['success', $data]);
        }

        return $this->render('salary/index.html.twig', [
            'controller_name' => 'SalaryController',
             'form' => $form,
        ]);
    }

}
