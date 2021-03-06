<?php

declare(strict_types=1);

namespace VOSTPT\Tests\Integration\Controllers\AcronymController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use VOSTPT\Models\Acronym;
use VOSTPT\Models\Role;
use VOSTPT\Models\User;
use VOSTPT\Tests\Integration\TestCase;

class UpdateEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itFailsToUpdateAcronymDueToInvalidContentTypeHeader(): void
    {
        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => 1,
        ]));

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
    public function itFailsToUpdateAcronymDueToMissingJwtToken(): void
    {
        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => 1,
        ]), [], [
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
    public function itFailsToUpdateAcronymDueToRecordNotFound(): void
    {
        $this->withoutMiddleware(Authenticate::class);

        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => 1,
        ]), [], [
            'Content-Type' => 'application/vnd.api+json',
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(404);
        $response->assertJson([
            'errors' => [
                [
                    'status' => 404,
                    'detail' => 'Acronym Not Found',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itFailsToUpdateAcronymDueToValidation(): void
    {
        $user = factory(User::class)->create()->assign(Role::ADMINISTRATOR);

        factory(Acronym::class)->create([
            'initials' => 'FAP',
        ]);

        $acronym = factory(Acronym::class)->create();

        $token = auth()->login($user);

        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => $acronym->getKey(),
        ]), [
            'initials' => 'FAP',
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
                    'detail' => 'The initials has already been taken.',
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
    public function itSuccessfullyUpdatesAcronym(): void
    {
        $user = factory(User::class)->create()->assign(Role::ADMINISTRATOR);

        $acronym = factory(Acronym::class)->create([
            'initials' => 'PSP',
            'meaning'  => 'Polícia de Segurança Pública',
        ]);

        $this->assertDatabaseMissing('acronyms', [
            'initials' => 'GNR',
            'meaning'  => 'Guarda Nacional Republicana',
        ]);

        $this->assertDatabaseHas('acronyms', [
            'initials' => 'PSP',
            'meaning'  => 'Polícia de Segurança Pública',
        ]);

        $token = auth()->login($user);

        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => $acronym->getKey(),
        ]), [
            'initials' => 'GNR',
            'meaning'  => 'Guarda Nacional Republicana',
        ], [
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => \sprintf('Bearer %s', $token),
        ]);

        $this->assertDatabaseMissing('acronyms', [
            'initials' => 'PSP',
            'meaning'  => 'Polícia de Segurança Pública',
        ]);

        $this->assertDatabaseHas('acronyms', [
            'initials' => 'GNR',
            'meaning'  => 'Guarda Nacional Republicana',
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus(200);
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
     * @dataProvider updateDataProvider
     *
     * @param string $role
     * @param int    $status
     */
    public function itVerifiesRoleAccessPermissionsToUpdateAcronym(string $role, int $status): void
    {
        $user = factory(User::class)->create()->assign($role);

        $acronym = factory(Acronym::class)->create();

        $token = auth()->login($user);

        $response = $this->json('PATCH', route('acronyms::update', [
            'acronym' => $acronym->getKey(),
        ]), [], [
            'Content-Type'  => 'application/vnd.api+json',
            'Authorization' => \sprintf('Bearer %s', $token),
        ]);

        $response->assertHeader('Content-Type', 'application/vnd.api+json');
        $response->assertStatus($status);
    }

    /**
     * @return array
     */
    public function updateDataProvider(): array
    {
        return [
            'Administrator' => [
                Role::ADMINISTRATOR,
                200,
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
