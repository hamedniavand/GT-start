<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MetaController
{
    #[Route('/api/meta/filters', name: 'meta_filters', methods: ['GET'])]
    public function filters(Connection $db): JsonResponse
    {
        $cities = array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'country' => $row['country'],
            ],
            $db->fetchAllAssociative('SELECT id, name, country FROM cities ORDER BY id ASC')
        );

        $categories = array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
            ],
            $db->fetchAllAssociative('SELECT id, name, slug FROM categories ORDER BY name ASC')
        );

        return new JsonResponse(['data' => ['cities' => $cities, 'categories' => $categories]]);
    }
}
