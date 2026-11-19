<?php

namespace App\Controller;

// TODO (candidate): implement these endpoints according to docs/api-spec.md

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class JobController
{
    #[Route('/api/jobs', name: 'jobs_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->notImplemented();
    }

    #[Route('/api/jobs/{id}', name: 'jobs_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->notImplemented();
    }

    #[Route('/api/jobs/{id}/apply', name: 'jobs_apply', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function apply(int $id): JsonResponse
    {
        return $this->notImplemented();
    }

    private function notImplemented(): JsonResponse
    {
        return new JsonResponse(
            ['error' => ['code' => 'NOT_IMPLEMENTED', 'message' => 'This endpoint is part of the assignment.']],
            501
        );
    }
}
