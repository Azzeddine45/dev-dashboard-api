<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\SourceResult;
use App\Service\DashboardAggregator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class DashboardController
{
    public function __construct(private readonly DashboardAggregator $aggregator)
    {
    }

    #[Route('/api/dashboard', name: 'api_dashboard', methods: ['GET'])]
    public function __invoke(
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] string $github,
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] float $lat,
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] float $lon,
    ): JsonResponse {
        if (1 !== preg_match('/^[A-Za-z0-9-]{1,39}$/', $github)) {
            return new JsonResponse(['error' => 'Invalid GitHub username.'], Response::HTTP_BAD_REQUEST);
        }

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return new JsonResponse(['error' => 'Invalid coordinates.'], Response::HTTP_BAD_REQUEST);
        }

        $report = $this->aggregator->aggregate($github, $lat, $lon);

        return new JsonResponse(
            [
                'github' => $this->format($report->github),
                'weather' => $this->format($report->weather),
            ],
            $report->isFullyUnavailable() ? Response::HTTP_SERVICE_UNAVAILABLE : Response::HTTP_OK,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function format(SourceResult $result): array
    {
        return [
            'status' => $result->status,
            'data' => $result->data?->toArray(),
        ];
    }
}
