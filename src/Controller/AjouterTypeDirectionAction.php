<?php

namespace App\Controller;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use ApiPlatform\Symfony\Util\RequestAttributesExtractor;
use App\Entity\TypeDirection;
use App\Repository\FilesRepository;
use App\Service\FileUploader;
use App\Service\RandomStringGeneratorServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class AjouterTypeDirectionAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploader $fileUploader,
        private RandomStringGeneratorServices $randomStringGeneratorServices,
        private FilesRepository $filesRepository,
        private SerializerInterface $serializer,
        private SerializerContextBuilderInterface $serializerContextBuilder,
    )
    {
    }

    public function __invoke(Request $request): object
    {
        $data = new \stdClass();
        $data->message = "Impossible de désérialiser les données.";

        if ($request->attributes->get('data') instanceof TypeDirection) {
            /*
            *  On traite ici l'enregistrement dans la base de données
            *  (équivaut à l'attribut de api operation:  write: false)
            */

            /** @var TypeDirection $typeDirection */
            $typeDirection = $request->attributes->get('data');

            // Nouvel enregistrement
            if (!$request->request->get('resourceId')) {
                $fichierUploades = $request->files->all();

                // Gestion des fichiers
                if ($fichierUploades !== null) {
                    // Enregistrement du logo
                    if (array_key_exists('logo', $fichierUploades)) {
                        // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                        do {
                            $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                            $existFiles = $this->filesRepository->findBy([
                                'referenceCode' => $reference
                            ]);

                        } while (count($existFiles) > 0);

                        if ($fichierUploades['logo'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $fichierUploades['logo'],
                                false,
                                TypeDirection::class,
                                null,
                                $reference
                            );
                        }

                        $typeDirection->setLogo($reference);
                    }

                }

                $this->entityManager->persist($typeDirection);
                $this->entityManager->flush();
                $this->entityManager->refresh($typeDirection);

            } // resourceId n'existe pas

            // Modification des informations du typeDirection
            if ($request->request->get('resourceId')) {
                $typeDirection->setId((int) $request->request->get('resourceId'));

                $existTypeDirection = $this->entityManager->getRepository(TypeDirection::class)
                    ->findOneBy(
                        [
                            'id' => $typeDirection->getId()
                        ]
                    )
                ;

                $attributes = RequestAttributesExtractor::extractAttributes($request);
                $context = $this->serializerContextBuilder->createFromRequest($request, false, $attributes);
                $entitySerialise = $this->serializer->serialize($typeDirection, 'json', []);

                // Remplacement des valeurs dans $entitySerialise
                $entitySerialise = json_decode($entitySerialise, true);
                foreach ($entitySerialise as $k => $v) {
                    if (\gettype($v) === 'boolean') {
                        $entitySerialise[$k] = $v === true ? "1" : "0";
                    }

                    if (\gettype($v) === 'integer' || \gettype($v) === 'double') {
                        $entitySerialise[$k] = (string) $v;
                    }
                }
                $entitySerialise = json_encode($entitySerialise);

                if ($existTypeDirection) {
                    $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $existTypeDirection;
                    $typeDirection = $this->serializer->deserialize($entitySerialise, TypeDirection::class, 'json', $context);
                }

                // Gestion des fichiers
                if ($request->files->all() !== null) {
                    // Enregistrement ou modification du logo
                    if (array_key_exists('logo', $request->files->all())) {
                        $reference = $typeDirection->getLogo();

                        if ($reference === null || trim($reference) === '') {
                            // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                            do {
                                $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                                $existFiles = $this->filesRepository->findBy([
                                    'referenceCode' => $reference
                                ]);

                            } while (count($existFiles) > 0);
                        }

                        if ($request->files->all()['logo'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $request->files->all()['logo'],
                                false,
                                TypeDirection::class,
                                $reference,
                                $reference
                            );
                        }

                        $typeDirection->setLogo($reference);
                    }

                }  // Fin gestion des fichiers

                $this->entityManager->flush();
                $this->entityManager->refresh($typeDirection);

            } // resourceId existe

            // Récupération des fichiers
            $fileLogo = $this->filesRepository->findOneBy(
                [
                    'referenceCode' => $typeDirection->getLogo()
                ]
            );

            $serverUrl = $this->getParameter('serverUrl');

            $fichiers = [
                'logo' => $fileLogo ? $serverUrl.$fileLogo->getLocation().$fileLogo->getFilename() : null,
            ];
            $typeDirection->setFichiers($fichiers);

            // On retourne un objet
            $data = $typeDirection;
        }

        return $data;
    }

}
