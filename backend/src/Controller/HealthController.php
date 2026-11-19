<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    #[Route('/api/health', name: 'health', methods: ['GET'])]
    public function __invoke(Connection $db): JsonResponse
    {
        $db->fetchOne('SELECT 1');

        return new JsonResponse(['status' => 'ok', 'database' => 'ok']);
    }
}
