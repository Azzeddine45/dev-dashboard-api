<?php

declare(strict_types=1);

namespace App\Provider;

use App\Dto\GithubProfile;
use App\Exception\ProviderException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GithubProvider
{
    public function __construct(private readonly HttpClientInterface $githubClient)
    {
    }

    public function fetchProfile(string $username): GithubProfile
    {
        try {
            $data = $this->githubClient
                ->request('GET', '/users/'.rawurlencode($username))
                ->toArray();
        } catch (ExceptionInterface $e) {
            throw new ProviderException(sprintf('GitHub is unavailable for "%s".', $username), 0, $e);
        }

        return new GithubProfile(
            login: (string) ($data['login'] ?? $username),
            name: isset($data['name']) ? (string) $data['name'] : null,
            publicRepos: (int) ($data['public_repos'] ?? 0),
            followers: (int) ($data['followers'] ?? 0),
            profileUrl: (string) ($data['html_url'] ?? ''),
        );
    }
}
