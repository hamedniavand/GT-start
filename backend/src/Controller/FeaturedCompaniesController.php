<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class FeaturedCompaniesController
{
    #[Route('/api/companies/featured', name: 'companies_featured', methods: ['GET'])]
    public function __invoke(Connection $db): JsonResponse
    {
        $companies = $db->fetchAllAssociative(
            'SELECT id, name, industry FROM companies WHERE is_featured = 1 ORDER BY name ASC'
        );

        $result = [];

        foreach ($companies as $company) {
            $openJobs = (int) $db->fetchOne(
                'SELECT COUNT(*) FROM jobs WHERE company_id = ? AND is_active = 1',
                [$company['id']]
            );

            $latestJobs = $db->fetchAllAssociative(
                'SELECT id, title, city_id, category_id, posted_at
                   FROM jobs
                  WHERE company_id = ? AND is_active = 1
                  ORDER BY posted_at DESC, id DESC
                  LIMIT 3',
                [$company['id']]
            );

            $jobs = [];

            foreach ($latestJobs as $job) {
                $city = $db->fetchAssociative(
                    'SELECT id, name, country FROM cities WHERE id = ?',
                    [$job['city_id']]
                );
                $category = $db->fetchAssociative(
                    'SELECT id, name FROM categories WHERE id = ?',
                    [$job['category_id']]
                );

                $jobs[] = [
                    'id' => (int) $job['id'],
                    'title' => $job['title'],
                    'posted_at' => (new \DateTimeImmutable($job['posted_at']))->format('Y-m-d\TH:i:s'),
                    'city' => ['id' => (int) $city['id'], 'name' => $city['name'], 'country' => $city['country']],
                    'category' => ['id' => (int) $category['id'], 'name' => $category['name']],
                ];
            }

            $result[] = [
                'id' => (int) $company['id'],
                'name' => $company['name'],
                'industry' => $company['industry'],
                'open_jobs' => $openJobs,
                'latest_jobs' => $jobs,
            ];
        }

        return new JsonResponse(['data' => $result]);
    }
}
