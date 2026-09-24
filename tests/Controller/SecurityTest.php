<?php

namespace App\Tests\Controller;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Tests\DatabaseWebTestCase;

/**
 * Login form and back-office access. The admin pages are only smoke-tested
 * (they render for an admin, and are closed to anonymous visitors).
 */
class SecurityTest extends DatabaseWebTestCase
{
    private const EMAIL = 'josette@poterie-josette.com';
    private const PASSWORD = 'terre-cuite-42';

    private const ADMIN_ENTITIES = ['Product', 'Event', 'Recipe', 'Blog', 'BlogType', 'RecipeCategory'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->purge(ResetPasswordRequest::class);
        $this->purge(User::class);

        $user = (new User())
            ->setEmail(self::EMAIL)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword(password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]));
        $this->persist($user);
    }

    public function testAdminRedirectsAnonymousVisitorToLogin(): void
    {
        $this->client->request('GET', '/admin/');

        $this->assertResponseRedirects('/login');
    }

    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="_csrf_token"]');
    }

    public function testWrongPasswordShowsErrorAndKeepsEmail(): void
    {
        $this->login(self::EMAIL, 'mauvais');

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
        $this->assertInputValueSame('email', self::EMAIL);
    }

    public function testUnknownEmailIsRejected(): void
    {
        $this->login('personne@example.com', self::PASSWORD);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testInvalidCsrfTokenIsRejected(): void
    {
        $this->client->request('POST', '/login', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
            '_csrf_token' => 'faux',
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->request('GET', '/admin/');
        $this->assertResponseRedirects('/login');
    }

    public function testLoginRedirectsToHomeThenOpensAdmin(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->assertResponseRedirects('/');
        $this->client->followRedirects();
        $this->client->request('GET', '/admin/');
        $this->assertResponseIsSuccessful();
        $this->assertStringStartsWith('/admin', parse_url($this->client->getRequest()->getUri(), PHP_URL_PATH));
    }

    public function testLoginReturnsToRequestedAdminPage(): void
    {
        $this->client->request('GET', '/admin/');
        $this->client->followRedirect();
        $this->login(self::EMAIL, self::PASSWORD);

        $this->assertResponseRedirects('http://localhost/admin/');
    }

    public function testLogoutEndsSession(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);
        $this->client->request('GET', '/logout');
        $this->client->request('GET', '/admin/');

        $this->assertResponseRedirects('/login');
    }

    /**
     * @dataProvider adminEntities
     */
    public function testAdminListAndNewFormRender(string $entity): void
    {
        $this->login(self::EMAIL, self::PASSWORD);

        $this->client->request('GET', self::adminUrl($entity, 'list'));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', self::adminUrl($entity, 'new'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function adminEntities(): iterable
    {
        foreach (self::ADMIN_ENTITIES as $entity) {
            yield $entity => [$entity];
        }
    }

    public function testForgotPasswordPageLoads(): void
    {
        $this->client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
    }

    public function testForgotPasswordSendsEmail(): void
    {
        $crawler = $this->client->request('GET', '/reset-password');
        $this->client->submit($crawler->filter('form')->form([
            'reset_password_request_form[email]' => self::EMAIL,
        ]));

        $this->assertResponseRedirects('/reset-password/check-email');
        $this->assertEmailCount(1);
    }

    public function testResetPasswordThenLoginWithNewPassword(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
        $token = self::$container->get('symfonycasts.reset_password.helper')->generateResetToken($user)->getToken();

        // The token URL stores the token in session and redirects to the form.
        $this->client->request('GET', '/reset-password/reset/'.$token);
        $crawler = $this->client->followRedirect();
        $this->client->submit($crawler->filter('form')->form([
            'change_password_form[plainPassword][first]' => 'nouveau-mot-de-passe',
            'change_password_form[plainPassword][second]' => 'nouveau-mot-de-passe',
        ]));
        $this->assertResponseRedirects('/');

        $this->login(self::EMAIL, self::PASSWORD);
        $this->assertResponseRedirects('/login');

        $this->login(self::EMAIL, 'nouveau-mot-de-passe');
        $this->assertResponseRedirects('/');
    }

    private function login(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        $token = $crawler->filter('input[name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/login', [
            'email' => $email,
            'password' => $password,
            '_csrf_token' => $token,
        ]);
    }

    private static function adminUrl(string $entity, string $action): string
    {
        return '/admin/?'.http_build_query(['entity' => $entity, 'action' => $action]);
    }
}
