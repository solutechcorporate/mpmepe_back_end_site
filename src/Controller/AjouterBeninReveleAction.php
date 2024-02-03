<?php

namespace App\Controller;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use ApiPlatform\Symfony\Util\RequestAttributesExtractor;
use App\Entity\BeninRevele;
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
final class AjouterBeninReveleAction extends AbstractController
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

        if ($request->attributes->get('data') instanceof BeninRevele) {
            /*
            *  On traite ici l'enregistrement dans la base de données
            *  (équivaut à l'attribut de api operation:  write: false)
            */

            /** @var BeninRevele $beninRevele */
            $beninRevele = $request->attributes->get('data');

            // Nouvel enregistrement
            if (!$request->request->get('resourceId')) {
                $fichierUploades = $request->files->all();

                // Gestion des fichiers
                if ($fichierUploades !== null) {
                    // Enregistrement de l'image de beninRevele
                    if (array_key_exists('image', $fichierUploades)) {
                        // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                        do {
                            $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                            $existFiles = $this->filesRepository->findBy([
                                'referenceCode' => $reference
                            ]);

                        } while (count($existFiles) > 0);

                        if ($fichierUploades['image'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $fichierUploades['image'],
                                false,
                                BeninRevele::class,
                                null,
                                $reference
                            );
                        }

                        $beninRevele->setImage($reference);
                    }

                    // Enregistrement du backgroundImage
                    if (array_key_exists('backgroundImage', $fichierUploades)) {
                        // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                        do {
                            $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                            $existFiles = $this->filesRepository->findBy([
                                'referenceCode' => $reference
                            ]);

                        } while (count($existFiles) > 0);

                        if ($fichierUploades['backgroundImage'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $fichierUploades['backgroundImage'],
                                false,
                                BeninRevele::class,
                                null,
                                $reference
                            );
                        }

                        $beninRevele->setBackgroundImage($reference);
                    }

                }

                $this->entityManager->persist($beninRevele);
                $this->entityManager->flush();
                $this->entityManager->refresh($beninRevele);

            } // resourceId n'existe pas

            // Modification des informations de beninRevele
            if ($request->request->get('resourceId')) {
                $beninRevele->setId((int) $request->request->get('resourceId'));

                $existBeninRevele = $this->entityManager->getRepository(BeninRevele::class)
                    ->findOneBy(
                        [
                            'id' => $beninRevele->getId()
                        ]
                    )
                ;

                $attributes = RequestAttributesExtractor::extractAttributes($request);
                $context = $this->serializerContextBuilder->createFromRequest($request, false, $attributes);
                $entitySerialise = $this->serializer->serialize($beninRevele, 'json', []);

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

                if ($existBeninRevele) {
                    $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $existBeninRevele;
                    $beninRevele = $this->serializer->deserialize($entitySerialise, BeninRevele::class, 'json', $context);
                }

                // Gestion des fichiers
                if ($request->files->all() !== null) {
                    // Enregistrement ou modification de l'image
                    if (array_key_exists('image', $request->files->all())) {
                        $reference = $beninRevele->getImage();

                        if ($reference === null || trim($reference) === '') {
                            // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                            do {
                                $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                                $existFiles = $this->filesRepository->findBy([
                                    'referenceCode' => $reference
                                ]);

                            } while (count($existFiles) > 0);
                        }

                        if ($request->files->all()['image'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $request->files->all()['image'],
                                false,
                                BeninRevele::class,
                                $reference,
                                $reference
                            );
                        }

                        $beninRevele->setImage($reference);
                    }

                    // Enregistrement ou modification du backgroundImage
                    if (array_key_exists('backgroundImage', $request->files->all())) {
                        $reference = $beninRevele->getBackgroundImage();

                        if ($reference === null || trim($reference) === '') {
                            // On s'assure que la reference est unique pour ne pas lier d'autres fichiers
                            do {
                                $reference = $this->randomStringGeneratorServices->random_alphanumeric(16);

                                $existFiles = $this->filesRepository->findBy([
                                    'referenceCode' => $reference
                                ]);

                            } while (count($existFiles) > 0);
                        }

                        if ($request->files->all()['backgroundImage'] instanceof UploadedFile) {
                            $this->fileUploader->saveFile(
                                $request->files->all()['backgroundImage'],
                                false,
                                BeninRevele::class,
                                $reference,
                                $reference
                            );
                        }

                        $beninRevele->setBackgroundImage($reference);
                    }

                }  // Fin gestion des fichiers

                $this->entityManager->flush();
                $this->entityManager->refresh($beninRevele);

            } // resourceId existe

            // Récupération des fichiers
            $fileImage = $this->filesRepository->findOneBy(
                [
                    'referenceCode' => $beninRevele->getImage()
                ]
            );

            $fileBackgroundImage = $this->filesRepository->findOneBy(
                [
                    'referenceCode' => $beninRevele->getBackgroundImage()
                ]
            );

            $serverUrl = $this->getParameter('serverUrl');

            $fichiers = [
                'image' => $fileImage ? $serverUrl.$fileImage->getLocation().$fileImage->getFilename() : null,
                'backgroundImage' => $fileBackgroundImage ? $serverUrl.$fileBackgroundImage->getLocation().$fileBackgroundImage->getFilename() : null,
            ];
            $beninRevele->setFichiers($fichiers);

            // On retourne un objet
            $data = $beninRevele;
        }

        return $data;
    }

}
