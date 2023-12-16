<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
     * Retourne au format JSON ou CSV la liste des entreprises 
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/api/companies', name: 'get_companies', methods: 'GET')]
    public function getCompanies(Request $request): Response
    {
        // Récuperer le format souhaité ('text/csv' ou 'application/json')
        $accept_header = $request->headers->get('Accept');
        $array_format = explode('/', $accept_header);
        $format =  count($array_format) == 2 ? $array_format[1] : null;

        if ($format != null && ($format === 'json' || $format === 'csv')) {

            // Récuperer les siren des entreprises
            $finder = new Finder();
            $files = $finder->files()->in(__DIR__ . '/../../public/companies/');

            $companiesFiles = [];
            foreach ($files as $file) {
                // Supprimer l'extension du fichier avant de l'enregistrer
                $companiesFiles[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }
            $companies['Siren'] = $companiesFiles;

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
    public function getCompany(int $siren): Response
    {
        $filePath = __DIR__ . '/../../public/companies/' . $siren . '.json';

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
    public function createCompany(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Verifier que le JSON est valide
        $constraint = new Assert\Collection([
            'Siren' => [new Assert\Type('string'), new Assert\Length(['min' => 9, 'max' => 9])],
            'Raison_sociale' => [new Assert\Type('string'), new Assert\NotBlank()],
            'Adresse' => new Assert\Collection([
                'Num' => new Assert\Type(type: 'integer'),
                'Voie' => new Assert\Type('string'),
                'Code_postal' => [new Assert\Type('string'), new Assert\Length(['min' => 5, 'max' => 5])],
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

        $filePath = __DIR__ . '/../../public/companies/' . $data['Siren'] . '.json';
        // Verifier si l'entreprise existe deja
        if (file_exists($filePath)) {
            return new Response("Entreprise existe déjà ", 409);
        }

        // Enregistrer le fichier
        file_put_contents($filePath, json_encode($data));

        return new Response("Entreprise créée", 201);
    }


    #[Route('/api/companies/{siren}', name: 'update_company', methods: 'PATCH')]
    public function updateCompany(Request $request, int $siren): Response
    {
        $requestData = json_decode($request->getContent(), true);

        // Verifier que le format JSON est valide
        $constraint = new Assert\Collection([
            'Raison_sociale' => new Assert\Optional([new Assert\Type('string'), new Assert\NotBlank]),
            'Adresse' => new Assert\Optional(new Assert\Collection([
                'Num' => new Assert\Optional(new Assert\Type(type: 'integer')),
                'Voie' => new Assert\Optional(new Assert\Type('string')),
                'Code_postal' => new Assert\Optional([new Assert\Type('string'), new Assert\Length(['min' => 5, 'max' => 5])]),
                'Ville' => new Assert\Optional([new Assert\Type('string'), new Assert\NotBlank()]),
                'GPS' => new Assert\Optional([new Assert\Collection([
                    'Latitude' =>  new Assert\Optional(new Assert\Type('string')),
                    'Longitude' =>  new Assert\Optional(new Assert\Type('string')),
                ]), new Assert\NotBlank()]),
            ])),
        ]);

        $validator = Validation::createValidator();
        $violations = $validator->validate($requestData, $constraint);

        if (count($violations) > 0 || $requestData == null) {
            return new Response("Format JSON invalide ou donnee manquante", 400);
        }

        $filePath = __DIR__ . '/../../public/companies/' . $siren . '.json';

        if (!file_exists($filePath)) {
            return new Response("Aucune entreprise avec ce SIREN", 404);
        }

        // Constuire le nouveau tableau : recuperer les donnees du fichier et les fusionner avec celles de la requete
        $data = json_decode(file_get_contents($filePath), true);
        // Afin de ne pas perdre de donnees si celles-ci ne sont pas fournies dans la requete, les tableaux de tableaux de la requete sont enrichis des donnees du fichier
        // Traitement du GPS
        $requestData['Adresse']['GPS'] = array_merge($data['Adresse']['GPS'], isset($requestData['Adresse']['GPS']) ? $requestData['Adresse']['GPS'] : []);
        // Traitement de l'Adresse
        $requestData['Adresse'] =  array_merge($data['Adresse'], isset($requestData['Adresse']) ? $requestData['Adresse'] : []);
        // Fusion de toutes les donnees
        $updatedData = array_merge($data, $requestData);

        file_put_contents($filePath, json_encode($updatedData));

        return new Response("Entreprise modifiée", 200);
    }

    /**
     * Supprimer une entreprise
     *
     * @param integer $siren
     * @return Response
     */
    #[Route('/api/companies/{siren}', name: 'delete_company', methods: 'DELETE')]
    public function deleteCompany(int $siren): Response
    {
        $filePath = __DIR__ . '/../../public/companies/' . $siren . '.json';

        if (!file_exists($filePath)) {
            return new Response("Aucune entreprise avec ce SIREN", 404);
        }

        try {
            unlink($filePath);
            return new Response("Entreprise supprimée", 200);
        } catch (Exception $e) {
            return new Response("Erreur interne", 500);
        }
    }
}
