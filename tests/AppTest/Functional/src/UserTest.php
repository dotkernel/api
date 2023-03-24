<?php

declare(strict_types=1);

namespace AppTest\Functional;

use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\Service\UserAvatarService;
use DateInterval;
use DateTimeImmutable;
use Dot\Mail\Service\MailService;
use Laminas\Diactoros\UploadedFile;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\UploadedFileInterface;

class UserTest extends AbstractFunctionalTest
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRegisterAccountDuplicateIdentity()
    {
        $this->createUser([
            'status' => User::STATUS_PENDING,
        ]);

        $userAvatarService = $this->createMock(UserAvatarService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $response = $this->post('/user', $this->getValidUserData());

        $this->assertResponseBadRequest($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('error', $data);
        $this->assertNotEmpty($data['error']);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::DUPLICATE_IDENTITY, $data['error']['messages']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRegisterAccountDuplicateEmail()
    {
        $this->createUser([
            'identity' => 'foo@dotkernel.com',
            'status' => User::STATUS_PENDING,
        ]);

        $userAvatarService = $this->createMock(UserAvatarService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);

        $response = $this->post('/user', $this->getValidUserData());

        $this->assertResponseBadRequest($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('error', $data);
        $this->assertNotEmpty($data['error']);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::DUPLICATE_EMAIL, $data['error']['messages']);
    }

    public function testRegisterAccount()
    {
        $userAvatarService = $this->createMock(UserAvatarService::class);
        $mailService = $this->createMock(MailService::class);
        $this->replaceService(UserAvatarService::class, $userAvatarService);
        $this->replaceService(MailService::class , $mailService);

        $user = $this->getValidUserData([
            'status' => User::STATUS_PENDING,
        ]);

        $response = $this->post('/user', $user);
        $this->assertResponseOk($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame($user['identity'], $data['identity']);
        $this->assertSame(User::STATUS_PENDING, $data['status']);
        $this->assertFalse($data['isDeleted']);
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
    public function testCreateMyAvatar()
    {
        $user = $this->createUser();
        $uploadedFile = $this->createUploadedFile();
        $userAvatarRepository = $this->getEntityManager()->getRepository(UserAvatar::class);
        $userAvatarService = $this->getMockBuilder(UserAvatarService::class)
            ->setConstructorArgs([
                $userAvatarRepository,
                []
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

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->post('/user/my-avatar', [], [], ['avatar' => $uploadedFile]);

        $path = __DIR__ . DIRECTORY_SEPARATOR . $uploadedFile->getClientFilename();
        unlink($path);

        $this->assertResponseOk($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testViewMyAvatarNotFound()
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
    public function testViewMyAvatar()
    {
        $user = $this->createUser();
        $userAvatar = new UserAvatar();
        $userAvatar->setUser($user);
        $userAvatar->setName('test');
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
    public function testDeleteMyAvatarNotFound()
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
    public function testDeleteMyAvatar()
    {
        $user = $this->createUser();
        $userAvatar = (new UserAvatar())
            ->setUser($user)
            ->setName('test');
        $this->getEntityManager()->persist($userAvatar);
        $this->getEntityManager()->flush();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->delete('/user/my-avatar');

        $this->assertResponseOk($response);
    }

    public function testActivateMyAccountInvalidCode()
    {
        $response = $this->patch('/account/activate/invalid_hash');
        $this->assertResponseBadRequest($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActivateMyAccountAlreadyActivated()
    {
        $user = $this->createUser();

        $response = $this->patch('/account/activate/' . $user->getHash());
        $this->assertResponseBadRequest($response);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActivateMyAccount()
    {
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $user = $this->createUser([
            'status' => User::STATUS_PENDING,
        ]);
        $this->assertFalse($user->isActive());

        $response = $this->patch('/account/activate/' . $user->getHash());
        $this->assertResponseOk($response);
        $user = $userRepository->find($user->getUuid()->toString());

        $this->assertTrue($user->isActive());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testActivateAccountByEmail()
    {
        $mailService = $this->createMock(MailService::class);
        $user = $this->createUser([
            'status' => User::STATUS_PENDING,
        ]);

        $this->replaceService(MailService::class, $mailService);

        $response = $this->post('/account/activate', [
            'email' => $user->getDetail()->getEmail()
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
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
    public function testDeleteMyAccount()
    {
        $user = $this->createUser();
        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $response = $this->delete('/user/my-account');

        $this->assertResponseOk($response);
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $deletedUser = $userRepository->find($user->getUuid()->toString());

        $this->assertTrue($deletedUser->isDeleted());
    }

    public function testRequestResetPasswordInvalidHash()
    {
        $response = $this->patch('/account/reset-password/invalid_hash');

        $this->assertResponseNotFound($response);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testRequestResetPasswordExpired()
    {
        $user = $this->createUser();

        $resetPassword = (new UserResetPasswordEntity())
            ->setUser($user)
            ->setStatus(UserResetPasswordEntity::STATUS_REQUESTED)
            ->setHash('test')
            ->setExpires((new DateTimeImmutable())->sub(new DateInterval('P1D')));

        $user->addResetPassword($resetPassword);

        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password' => '654321',
            'passwordConfirm' => '654321',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertNotEmpty($data['error']['messages'][0]);
        $this->assertSame(
            sprintf(Message::RESET_PASSWORD_EXPIRED, $resetPassword->getHash()),
            $data['error']['messages'][0]
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testRequestResetPasswordAlreadyUsed()
    {
        $user = $this->createUser();
        $resetPassword = new UserResetPasswordEntity();
        $resetPassword->setUser($user);
        $resetPassword->setStatus(UserResetPasswordEntity::STATUS_COMPLETED);
        $resetPassword->setHash('test');
        $resetPassword->setExpires((new DateTimeImmutable())->add(new DateInterval('P1D')));
        $user->addResetPassword($resetPassword);
        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password' => '654321',
            'passwordConfirm' => '654321',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
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
     */
    public function testResetPassword()
    {
        $user = $this->createUser();
        $resetPassword = new UserResetPasswordEntity();
        $resetPassword->setUser($user);
        $resetPassword->setStatus(UserResetPasswordEntity::STATUS_REQUESTED);
        $resetPassword->setHash('test');
        $resetPassword->setExpires((new DateTimeImmutable())->add(new DateInterval('P1D')));
        $user->addResetPassword($resetPassword);
        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
            'password' => '654321',
            'passwordConfirm' => '654321',
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('messages', $data['info']);
        $this->assertNotEmpty($data['info']['messages'][0]);
        $this->assertSame(Message::RESET_PASSWORD_OK, $data['info']['messages'][0]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testResetPasswordByEmail()
    {
        $user = $this->createUser();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->post('/account/reset-password', [
            'email' => $user->getDetail()->getEmail(),
        ]);

        $this->assertResponseOk($response);
        $this->assertCount(1, $user->getResetPasswords());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testViewMyAccount()
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
    public function testUpdateMyAccount()
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), self::DEFAULT_PASSWORD);

        $updateData = [
            'detail' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ]
        ];

        $response = $this->patch('/user/my-account', $updateData);

        $this->assertResponseOk($response);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame($updateData['detail']['firstName'], $data['detail']['firstName']);
        $this->assertSame($updateData['detail']['lastName'], $data['detail']['lastName']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRecoverAccountByIdentity()
    {
        $user = $this->createUser();

        $mailService = $this->createMock(MailService::class);
        $this->replaceService(MailService::class, $mailService);

        $response = $this->post('/account/recover-identity', [
            'email' => $user->getDetail()->getEmail(),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseOk($response);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('messages', $data['info']);
        $this->assertNotEmpty($data['info']['messages'][0]);
        $this->assertSame(Message::MAIL_SENT_RECOVER_IDENTITY, $data['info']['messages'][0]);
    }

    private function createUploadedFile(): UploadedFileInterface
    {
        $img = imagecreatetruecolor(120, 20);
        $bg = imagecolorallocate ($img, 255, 255, 255);
        imagefilledrectangle($img,0,0,120,20,$bg);
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test.jpg';
        imagejpeg($img, $path,100);

        return new UploadedFile($path, 10, UPLOAD_ERR_OK, 'test.jpg', 'image/jpg');
    }
}
