<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class WriteCompanyFileController extends AbstractController
{
    /**
     * Enregistrer dans un fichier l'entreprise choisie
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/write-company-file', name: 'app_write_company_file', methods: 'POST')]
    public function index(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        try {
            file_put_contents(__DIR__ . '/../../var/tmp/' . $data['siren'] . '.json', json_encode($data));
            return new JsonResponse(['message' => 'Success'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Internal Server Error' . $e], 500);
        }
    }
}
