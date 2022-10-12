<?php

namespace AppTest\Functional;

use Api\App\Entity\RoleInterface;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\Entity\UserRole;
use Api\User\Service\UserAvatarService;
use AppTest\Functional\Traits\AuthenticationTrait;
use AppTest\Functional\Traits\DatabaseTrait;
use Dot\Mail\Service\MailService;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UserTest
 * @package AppTest\Functional
 */
class UserTest extends AbstractFunctionalTest
{
    use DatabaseTrait, AuthenticationTrait;

//    public function testCreateMyAvatar()
//    {
//        $user = $this->createUser();
//        $uploadedFile = $this->createUploadedFile();
//        $userAvatarRepository = $this->getEntityManager()->getRepository(UserAvatar::class);
//
//        $userAvatarService = $this->getMockBuilder(UserAvatarService::class)
//            ->setConstructorArgs([
//                $userAvatarRepository,
//                []
//            ])
//            ->onlyMethods([
//                'ensureDirectoryExists',
//                'getUserAvatarDirectoryPath',
//                'deleteAvatarFile',
//                'createFileName',
//                'saveAvatarImage',
//            ])
//            ->getMock();
//
//        $this->replaceService(UserAvatarService::class, $userAvatarService);
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->post('/user/my-avatar', [], [], ['avatar' => $uploadedFile]);
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testViewMyAvatarNotFound()
//    {
//        $user = $this->createUser();
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->get('/user/my-avatar');
//
//        $this->assertResponseNotFound($response);
//    }
//
//    public function testViewMyAvatar()
//    {
//        $user = $this->createUser();
//        $userAvatar = new UserAvatar();
//        $userAvatar->setUser($user);
//        $userAvatar->setName('test');
//        $this->getEntityManager()->persist($userAvatar);
//        $this->getEntityManager()->flush();
//
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->get('/user/my-avatar');
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testDeleteMyAvatarNotFound()
//    {
//        $user = $this->createUser();
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->delete('/user/my-avatar');
//
//        $this->assertResponseNotFound($response);
//    }
//
//    public function testDeleteMyAvatar()
//    {
//        $user = $this->createUser();
//        $userAvatar = new UserAvatar();
//        $userAvatar->setUser($user);
//        $userAvatar->setName('test');
//        $this->getEntityManager()->persist($userAvatar);
//        $this->getEntityManager()->flush();
//
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->delete('/user/my-avatar');
//
//        $this->assertResponseOk($response);
//    }
//
//    public function testActivateMyAccountInvalidCode()
//    {
//        $response = $this->patch('/account/activate/invalid_hash');
//        $this->assertResponseBadRequest($response);
//    }
//
//    public function testActivateMyAccountAlreadyActivated()
//    {
//        $user = $this->createUser();
//
//        $response = $this->patch('/account/activate/' . $user->getHash());
//        $this->assertResponseBadRequest($response);
//    }
//
//    public function testActivateMyAccount()
//    {
//        $userRepository = $this->getEntityManager()->getRepository(User::class);
//        $user = $this->createUser(false);
//        $this->assertFalse($user->isActive());
//
//        $response = $this->patch('/account/activate/' . $user->getHash());
//        $this->assertResponseOk($response);
//        $user = $userRepository->find($user->getUuid()->toString());
//
//        $this->assertTrue($user->isActive());
//    }
//
//    public function testActivateAccountByEmail()
//    {
//        $mailService = $this->createMock(MailService::class);
//        $user = $this->createUser(false);
//
//        $this->replaceService(MailService::class, $mailService);
//
//        $response = $this->post('/account/activate', [
//            'email' => $user->getDetail()->getEmail()
//        ]);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertArrayHasKey('info', $data);
//        $this->assertArrayHasKey('messages', $data['info']);
//        $this->assertSame(
//            sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getDetail()->getEmail()),
//            $data['info']['messages'][0]
//        );
//    }
//
//    public function testDeleteMyAccount()
//    {
//        $user = $this->createUser();
//        $this->loginAs($user->getIdentity(), '123456');
//
//        $response = $this->delete('/user/my-account');
//
//        $this->assertResponseOk($response);
//        $userRepository = $this->getEntityManager()->getRepository(User::class);
//        $deletedUser = $userRepository->find($user->getUuid()->toString());
//
//        $this->assertTrue($deletedUser->isDeleted());
//    }
//
//    public function testRequestResetPasswordInvalidHash()
//    {
//        $response = $this->patch('/account/reset-password/invalid_hash');
//
//        $this->assertResponseNotFound($response);
//    }
//
//    public function testRequestResetPasswordExpired()
//    {
//        $user = $this->createUser();
//        $resetPassword = new UserResetPasswordEntity();
//        $resetPassword->setUser($user);
//        $resetPassword->setStatus(UserResetPasswordEntity::STATUS_REQUESTED);
//        $resetPassword->setHash('test');
//        $resetPassword->setExpires((new \DateTimeImmutable())->sub(new \DateInterval('P1D')));
//        $user->addResetPassword($resetPassword);
//        $this->getEntityManager()->persist($resetPassword);
//        $this->getEntityManager()->persist($user);
//        $this->getEntityManager()->flush();
//
//        $mailService = $this->createMock(MailService::class);
//        $this->replaceService(MailService::class, $mailService);
//
//        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
//            'password' => '654321',
//            'passwordConfirm' => '654321',
//        ]);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseBadRequest($response);
//        $this->assertArrayHasKey('error', $data);
//        $this->assertArrayHasKey('messages', $data['error']);
//        $this->assertNotEmpty($data['error']['messages'][0]);
//        $this->assertSame(
//            sprintf(Message::RESET_PASSWORD_EXPIRED, $resetPassword->getHash()),
//            $data['error']['messages'][0]
//        );
//    }
//
//    public function testRequestResetPasswordAlreadyUsed()
//    {
//        $user = $this->createUser();
//        $resetPassword = new UserResetPasswordEntity();
//        $resetPassword->setUser($user);
//        $resetPassword->setStatus(UserResetPasswordEntity::STATUS_COMPLETED);
//        $resetPassword->setHash('test');
//        $resetPassword->setExpires((new \DateTimeImmutable())->add(new \DateInterval('P1D')));
//        $user->addResetPassword($resetPassword);
//        $this->getEntityManager()->persist($resetPassword);
//        $this->getEntityManager()->persist($user);
//        $this->getEntityManager()->flush();
//
//        $mailService = $this->createMock(MailService::class);
//        $this->replaceService(MailService::class, $mailService);
//
//        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
//            'password' => '654321',
//            'passwordConfirm' => '654321',
//        ]);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseBadRequest($response);
//        $this->assertArrayHasKey('error', $data);
//        $this->assertArrayHasKey('messages', $data['error']);
//        $this->assertNotEmpty($data['error']['messages'][0]);
//        $this->assertSame(
//            sprintf(Message::RESET_PASSWORD_USED, $resetPassword->getHash()),
//            $data['error']['messages'][0]
//        );
//    }
//
//    public function testResetPassword()
//    {
//        $user = $this->createUser();
//        $resetPassword = new UserResetPasswordEntity();
//        $resetPassword->setUser($user);
//        $resetPassword->setStatus(UserResetPasswordEntity::STATUS_REQUESTED);
//        $resetPassword->setHash('test');
//        $resetPassword->setExpires((new \DateTimeImmutable())->add(new \DateInterval('P1D')));
//        $user->addResetPassword($resetPassword);
//        $this->getEntityManager()->persist($resetPassword);
//        $this->getEntityManager()->persist($user);
//        $this->getEntityManager()->flush();
//
//        $mailService = $this->createMock(MailService::class);
//        $this->replaceService(MailService::class, $mailService);
//
//        $response = $this->patch('/account/reset-password/' . $resetPassword->getHash(), [
//            'password' => '654321',
//            'passwordConfirm' => '654321',
//        ]);
//
//        $data = json_decode($response->getBody()->getContents(), true);
//
//        $this->assertResponseOk($response);
//        $this->assertArrayHasKey('info', $data);
//        $this->assertArrayHasKey('messages', $data['info']);
//        $this->assertNotEmpty($data['info']['messages'][0]);
//        $this->assertSame(Message::RESET_PASSWORD_OK, $data['info']['messages'][0]);
//    }

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

    public function testRegisterAccountDuplicateIdentity()
    {
        $user = $this->createUser();

        $response = $this->get('/user/my-account', [
            'identity' => $user->getIdentity(),
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseBadRequest($response);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertNotEmpty($data['error']['messages'][0]);
        $this->assertSame(Message::DUPLICATE_IDENTITY, $data['error']['messages'][0]);
    }

    public function testViewMyAccount()
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), '123456');
        $response = $this->get('/user/my-account');

        $this->assertResponseOk($response);
    }

    public function testUpdateMyAccount()
    {
        $user = $this->createUser();

        $this->loginAs($user->getIdentity(), '123456');

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

    private function createUser(bool $activated = true): User
    {
        /** @var RoleInterface $userRole */
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);

        $user = new User();
        $userDetail = new UserDetail();

        $user->setIdentity('user@test.com');
        $user->setPassword(password_hash('123456', PASSWORD_DEFAULT));
        if ($activated) {
            $user->activate();
        }
        $userDetail->setFirstName('Test');
        $userDetail->setLastName('User');
        $userDetail->setEmail('user@test.com');
        $userDetail->setUser($user);

        $user->setDetail($userDetail);
        $user->addRole($userRole);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    private function createUploadedFile(): UploadedFileInterface
    {
        $path = realpath(__DIR__ . '/../../../avatar.jpg');
        return new UploadedFile($path, 10, UPLOAD_ERR_OK, 'test.jpg', 'image/jpg');
    }
}
