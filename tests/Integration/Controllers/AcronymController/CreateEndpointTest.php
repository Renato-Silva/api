<?php

declare(strict_types=1);

namespace VOSTPT\Tests\Integration\Controllers\AcronymController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VOSTPT\Models\Role;
use VOSTPT\Models\User;
use VOSTPT\Tests\Integration\TestCase;

class CreateEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itFailsToCreateAcronymDueToInvalidContentTypeHeader(): void
    {
        $response = $this->json('POST', route('acronyms::create'));

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(415);
        $response->assertJson([
            'errors' => [
                [
                    'status' => 415,
                    'detail' => 'Wrong media type',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itFailsTCreateAcronymDueToMissingJwtToken(): void
    {
        $response = $this->json('POST', route('acronyms::create'), [], [
            'Content-Type' => 'application/vnd.api+json',
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(401);
        $response->assertJson([
            'errors' => [
                [
                    'status' => 401,
                    'detail' => 'Token not provided',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itFailsToCreateAcronymDueToValidation(): void
    {
        $user = factory(User::class)->create()->assign(Role::ADMINISTRATOR);

        $token = auth()->login($user);

        $response = $this->json('POST', route('acronyms::create'), [
            'initials' => \str_repeat('initials', 3),
            'meaning'  => \str_repeat('meaning', 40),
        ], [
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => \sprintf('Bearer %s', $token),
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                [
                    'detail' => 'The initials may not be greater than 16 characters.',
                    'meta'   => [
                        'field' => 'initials',
                    ],
                ],
                [
                    'detail' => 'The meaning may not be greater than 255 characters.',
                    'meta'   => [
                        'field' => 'meaning',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itSuccessfullyCreatesAcronym(): void
    {
        $user = factory(User::class)->create()->assign(Role::ADMINISTRATOR);

        $this->assertDatabaseMissing('acronyms', [
            'initials' => 'FAP',
            'meaning'  => 'Força Aérea Portuguesa',
        ]);

        $token = auth()->login($user);

        $response = $this->json('POST', route('acronyms::create'), [
            'initials' => 'FAP',
            'meaning'  => 'Força Aérea Portuguesa',
        ], [
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => \sprintf('Bearer %s', $token),
        ]);

        $this->assertDatabaseHas('acronyms', [
            'initials' => 'FAP',
            'meaning'  => 'Força Aérea Portuguesa',
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'initials',
                    'meaning',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider createDataProvider
     *
     * @param string $role
     * @param int    $status
     */
    public function itVerifiesRoleAccessPermissionsToCreateAcronym(string $role, int $status): void
    {
        $user = factory(User::class)->create()->assign($role);

        $token = auth()->login($user);

        $response = $this->json('POST', route('acronyms::create'), [], [
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => \sprintf('Bearer %s', $token),
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus($status);
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            'Administrator' => [
                Role::ADMINISTRATOR,
                422,
            ],
            'Moderator' => [
                Role::MODERATOR,
                403,
            ],
            'Contributor' => [
                Role::CONTRIBUTOR,
                403,
            ],
        ];
    }
}
