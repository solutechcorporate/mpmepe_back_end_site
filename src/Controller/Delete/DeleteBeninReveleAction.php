<?php

namespace App\Controller\Delete;

use App\Entity\BeninRevele;
use App\Service\ControlDeletionEntityService;
use ArrayObject;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DeleteBeninReveleAction extends AbstractController
{
    public function __construct(
        private ControlDeletionEntityService $controlDeletionEntityService
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(Request $request): object|null
    {
        $data = new \stdClass();
        $data->message = "Impossible de supprimer la ressource.";

        if ($request->attributes->get('data') instanceof BeninRevele) {
            /** @var BeninRevele $beninRevele */
            $beninRevele = $request->attributes->get('data');

            $this->controlDeletionEntityService->controlDeletion($beninRevele);

            // On retourne null
            $data = null;
        }

        return $data;
    }
}
