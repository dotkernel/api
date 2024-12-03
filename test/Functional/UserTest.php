<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserResetPassword;
use Api\User\Enum\UserResetPasswordStatusEnum;
use Api\User\Enum\UserStatusEnum;
use Api\User\Service\UserAvatarService;
use DateInterval;
use DateTimeImmutable;
use Dot\Mail\Service\MailService;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\UploadedFileInterface;

use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagefilledrectangle;
use function imagejpeg;
use function json_decode;
use function sprintf;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const UPLOAD_ERR_OK;

class UserTest extends AbstractFunctionalTest
{
    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRegisterAccountDuplicateIdentity(): void
    {
        $this->createUser([
            'status' => UserStatusEnum::Pending,
        ]);

        $userAvatarService = $this->createMock(UserAvatarService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $response = $this->post('/user', $this->getValidUserData(['status' => UserStatusEnum::Pending->value]));
        $this->assertResponseConflict($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('error', $data);
        $this->assertNotEmpty($data['error']);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::DUPLICATE_IDENTITY, $data['error']['messages']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRegisterAccountDuplicateEmail(): void
    {
        $this->createUser([
            'identity' => 'foo@dotkernel.com',
            'status'   => UserStatusEnum::Pending,
        ]);

        $userAvatarService = $this->createMock(UserAvatarService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $response = $this->post('/user', $this->getValidUserData(['status' => UserStatusEnum::Pending->value]));
        $this->assertResponseConflict($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('error', $data);
        $this->assertNotEmpty($data['error']);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::DUPLICATE_EMAIL, $data['error']['messages']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRegisterAccount(): void
    {
        $userAvatarService = $this->createMock(UserAvatarService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $user = $this->getValidUserData([
            'status' => UserStatusEnum::Pending->value,
        ]);

        $response = $this->post('/user', $user);
        $this->assertResponseCreated($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame($user['identity'], $data['identity']);
        $this->assertSame(UserStatusEnum::Pending->value, $data['status']);
        $this->assertArrayHasKey('detail', $data);
        $this->assertArrayHasKey('email', $data['detail']);
        $this->assertArrayHasKey('firstName', $data['detail']);
        $this->assertArrayHasKey('lastName', $data['detail']);
        $this->assertSame($user['detail']['email'], $data['detail']['email']);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testCreateMyAvatar(): void
    {
        $userAvatarRepository = $this->getEntityManager()->getRepository(UserAvatar::class);
        $userAvatarService    = $this->getMockBuilder(UserAvatarService::class)
            ->setConstructorArgs([
                $userAvatarRepository,
                [],
            ])
            ->onlyMethods([
                'ensureDirectoryExists',
                'getUserAvatarDirectoryPath',
                'deleteAvatarFile',
                'createFileName',
                'saveAvatarImage',
            ])
            ->getMock();
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $uploadedFile = $this->createUploadedFile();

        $response = $this->post('/user/my-avatar', [], [], ['avatar' => $uploadedFile]);
        $this->assertResponseCreated($response);

        $path = __DIR__ . DIRECTORY_SEPARATOR . $uploadedFile->getClientFilename();
        unlink($path);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testViewMyAvatarNotFound(): void
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->get('/user/my-avatar');
        $this->assertResponseNotFound($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testViewMyAvatar(): void
    {
        $user = $this->createUser();

        $userAvatar = (new UserAvatar())
            ->setUser($user)
            ->setName('test');

        $this->getEntityManager()->persist($userAvatar);
        $this->getEntityManager()->flush();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->get('/user/my-avatar');
        $this->assertResponseOk($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeleteMyAvatarNotFound(): void
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->delete('/user/my-avatar');
        $this->assertResponseNotFound($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDeleteMyAvatar(): void
    {
        $user = $this->createUser();

        $userAvatar = (new UserAvatar())
            ->setUser($user)
            ->setName('test');

        $this->getEntityManager()->persist($userAvatar);
        $this->getEntityManager()->flush();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->delete('/user/my-avatar');
        $this->assertResponseNoContent($response);
    }

    public function testActivateMyAccountInvalidCode(): void
    {
        $response = $this->patch('/account/activate/invalid_hash');
        $this->assertResponseNotFound($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActivateMyAccountAlreadyActivated(): void
    {
        $user = $this->createUser();

        $response = $this->patch('/account/activate/' . $user->getHash());
        $this->assertResponseConflict($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActivateMyAccount(): void
    {
        $user = $this->createUser([
            'status' => UserStatusEnum::Pending,
        ]);
        $this->assertFalse($user->isActive());

        $response = $this->patch('/account/activate/' . $user->getHash());
        $this->assertResponseOk($response);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user           = $userRepository->find($user->getUuid()->toString());
        $this->assertTrue($user?->isActive());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testActivateAccountByEmail(): void
    {
        $user = $this->createUser([
            'status' => UserStatusEnum::Pending,
        ]);

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->post('/account/activate', [
            'email' => $user->getDetail()->getEmail(),
        ]);
        $this->assertResponseCreated($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('messages', $data['info']);
        $this->assertSame(
            sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getDetail()->getEmail()),
            $data['info']['messages'][0]
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDeleteMyAccount(): void
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->delete('/user/my-account');
        $this->assertResponseNoContent($response);

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $deletedUser    = $userRepository->find($user->getUuid()->toString());
        $this->assertTrue($deletedUser?->isDeleted());
    }

    public function testRequestResetPasswordInvalidHash(): void
    {
        $response = $this->patch('/account/reset-password/invalid_hash');
        $this->assertResponseNotFound($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRequestResetPasswordExpired(): void
    {
        $user = $this->createUser();

        $resetPassword = (new UserResetPassword())
            ->setUser($user)
            ->setStatus(UserResetPasswordStatusEnum::Requested)
            ->setHash('test')
            ->setExpires((new DateTimeImmutable())->sub(new DateInterval('P1D')));
        $user->addResetPassword($resetPassword);

        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password'        => '654321',
            'passwordConfirm' => '654321',
        ]);
        $this->assertResponseGone($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertNotEmpty($data['error']['messages'][0]);
        $this->assertSame(
            sprintf(Message::RESET_PASSWORD_EXPIRED, $resetPassword->getHash()),
            $data['error']['messages'][0]
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRequestResetPasswordAlreadyUsed(): void
    {
        $user = $this->createUser();

        $resetPassword = (new UserResetPassword())
            ->setUser($user)
            ->setStatus(UserResetPasswordStatusEnum::Completed)
            ->setHash('test')
            ->setExpires((new DateTimeImmutable())->add(new DateInterval('P1D')));
        $user->addResetPassword($resetPassword);

        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password'        => '654321',
            'passwordConfirm' => '654321',
        ]);
        $this->assertResponseConflict($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertNotEmpty($data['error']['messages'][0]);
        $this->assertSame(
            sprintf(Message::RESET_PASSWORD_USED, $resetPassword->getHash()),
            $data['error']['messages'][0]
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testResetPassword(): void
    {
        $user = $this->createUser();

        $resetPassword = (new UserResetPassword())
            ->setUser($user)
            ->setStatus(UserResetPasswordStatusEnum::Requested)
            ->setHash('test')
            ->setExpires((new DateTimeImmutable())->add(new DateInterval('P1D')));
        $user->addResetPassword($resetPassword);

        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password'        => '654321',
            'passwordConfirm' => '654321',
        ]);
        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('messages', $data['info']);
        $this->assertNotEmpty($data['info']['messages'][0]);
        $this->assertSame(Message::RESET_PASSWORD_OK, $data['info']['messages'][0]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testResetPasswordByEmail(): void
    {
        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $user = $this->createUser();

        $response = $this->post('/account/reset-password', [
            'email' => $user->getDetail()->getEmail(),
        ]);
        $this->assertResponseCreated($response);
        $this->assertCount(1, $user->getResetPasswords());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testViewMyAccount(): void
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->get('/user/my-account');
        $this->assertResponseOk($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUpdateMyAccount(): void
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $updateData = [
            'detail' => [
                'firstName' => 'John',
                'lastName'  => 'Doe',
            ],
        ];

        $response = $this->patch('/user/my-account', $updateData);
        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertSame($updateData['detail']['firstName'], $data['detail']['firstName']);
        $this->assertSame($updateData['detail']['lastName'], $data['detail']['lastName']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testRecoverAccountByIdentity(): void
    {
        $user = $this->createUser();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->post('/account/recover-identity', [
            'email' => $user->getDetail()->getEmail(),
        ]);
        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('messages', $data['info']);
        $this->assertNotEmpty($data['info']['messages'][0]);
        $this->assertSame(Message::MAIL_SENT_RECOVER_IDENTITY, $data['info']['messages'][0]);
    }

    private function createUploadedFile(): UploadedFileInterface
    {
        $img = imagecreatetruecolor(120, 20);
        $bg  = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 120, 20, $bg);
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test.jpg';
        imagejpeg($img, $path, 100);

        return new UploadedFile($path, 10, UPLOAD_ERR_OK, 'test.jpg', 'image/jpg');
    }
}
