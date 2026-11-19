<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ProviderException;
use App\Provider\GithubProfileProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GithubProfileController
{
    public function __construct(private readonly GithubProfileProviderInterface $provider)
    {
    }

    #[Route(
        '/api/github/{username}',
        name: 'api_github_profile',
        requirements: ['username' => '[A-Za-z0-9-]{1,39}'],
        methods: ['GET'],
    )]
    public function __invoke(string $username): JsonResponse
    {
        try {
            $profile = $this->provider->fetchProfile($username);
        } catch (ProviderException) {
            return new JsonResponse(
                ['error' => 'Upstream provider unavailable.'],
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        return new JsonResponse([
            'login' => $profile->login,
            'name' => $profile->name,
            'publicRepos' => $profile->publicRepos,
            'followers' => $profile->followers,
            'profileUrl' => $profile->profileUrl,
            'stale' => $profile->stale,
        ]);
    }
}
