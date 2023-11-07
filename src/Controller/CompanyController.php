<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class CompanyController extends AbstractController
{

    private $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder(), new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * Enregistrer dans un fichier et en session l'entreprise choisie
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/write-company-file', name: 'app_write_company_file', methods: 'POST')]
    public function write_company_file(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            // Enregistrer l'entreprise dans un fichier
            file_put_contents(__DIR__ . '/../../var/tmp/' . $data['siren'] . '.json', json_encode($data));

            // Enregistrer l'entreprise dans la session
            $session = $request->getSession();
            $session->set('siren',  $data['siren']);

            return new JsonResponse(['message' => 'Success'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Retourne au format JSON ou CSV la liste des entreprises 
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/api/companies', name: 'get_companies', methods: 'GET')]
    public function get_companies(Request $request): Response
    {
        // Récuperer le format souhaité
        $accept_header = $request->headers->get('Accept');
        $array_format = explode('/', $accept_header);
        $format =  count($array_format) == 2 ? $array_format[1] : null;

        if ($format != null && ($format === 'json' || $format === 'csv')) {

            // Récuperer les siren des entreprises
            $finder = new Finder();
            $files = $finder->files()->in(__DIR__ . '/../../var/tmp/');

            $companiesFiles = [];
            foreach ($files as $file) {
                // Supprimer l'extension du fichier avant de l'enregistrer
                $companiesFiles[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }
            $companies['siren'] = $companiesFiles;

            // Serializer
            $data = $this->serializer->serialize($companies, $format);

            return new Response($data, 200, ['Content-Type' => $accept_header]);
        } else {
            // Format non pris en compte (HTTP 406)
            $response = new Response();
            $response->setStatusCode(406);
            return $response;
        }
    }

    /**
     * Retourne au format JSON les informations de l'entreprise
     * @param int $siren
     * @return Response
     */
    #[Route('/api/companies/{siren}', name: 'get_company', methods: 'GET')]
    public function get_company(int $siren): Response
    {
        $filePath = __DIR__ . '/../../var/tmp/' . $siren . '.json';

        if (!file_exists($filePath)) {
            return new Response("Aucune entreprise avec ce SIREN", 404);
        }

        $content = file_get_contents($filePath);
        return new Response($content, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Creer une entreprise
     * @param Request $request
     * @return Response
     */
    #[Route('/api/companies', name: 'create_company', methods: 'POST')]
    public function create_company(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Verifier que le format JSON est valide
        $constraint = new Assert\Collection([
            'Siren' => [new Assert\Type('string'), new Assert\Length(['min' => 9, 'max' => 9])],
            'Raison_sociale' => [new Assert\Type('string'), new Assert\NotBlank()],
            'Adresse' => new Assert\Collection([
                'Num' => new Assert\Type(type: 'integer'),
                'Voie' => new Assert\Type('string'),
                'Code_postale' => [new Assert\Type('string'), new Assert\Length(['min' => 5, 'max' => 5])],
                'Ville' => [new Assert\Type('string'), new Assert\NotBlank()],
                'GPS' => new Assert\Collection([
                    'Latitude' =>  new Assert\Type('string'),
                    'Longitude' =>  new Assert\Type('string'),
                ]),
            ]),
        ]);

        $validator = Validation::createValidator();
        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0 || $data == null) {
            return new Response("Format JSON invalide ou donnee manquante", 400);
        }

        // Enregistrer le fichier
        $filePath = __DIR__ . '/../../var/tmp/' . $data['Siren'] . '.json';

        if (file_exists($filePath)) {
            return new Response("Entreprise existe déjà ", 409);
        }

        file_put_contents($filePath, json_encode($data));

        return new Response("Entreprise créée", 201);
    }
}
