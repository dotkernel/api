<?php

namespace Unit;

use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserService;
use Api\User\Service\UserService as Subject;
use Doctrine\ORM\ORMException;
use Dot\Mail\Service\MailService;
use Laminas\Diactoros\UploadedFile;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Class UserServiceTest
 * @package Unit
 */
class UserServiceTest extends TestCase
{
    /** @var Subject $subject */
    private Subject $subject;

    private UserRoleService $userRoleService;

    private MailService $mailService;

    private TemplateRendererInterface $templateRendererInterface;

    private UserRepository $userRepository;

    private UserAvatarRepository $userAvatarRepository;

    private UserDetailRepository $userDetailRepository;

    private array $config;

    public function setUp(): void
    {
        $this->userRoleService = $this->createMock(UserRoleService::class);
        $this->mailService = $this->createMock(MailService::class);
        $this->templateRendererInterface = $this->createMock(TemplateRendererInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userDetailRepository = $this->createMock(UserDetailRepository::class);
        $this->config = [];

        $this->subject = new Subject(
            $this->userRoleService,
            $this->mailService,
            $this->templateRendererInterface,
            $this->userRepository,
            $this->userDetailRepository,
            $this->config,
        );
    }

    public function testCreateUserThrowsExceptionDuplicateIdentity()
    {
        $this->userRepository->method('exists')->willReturn(true);

        $this->expectException(ORMException::class);

        $this->subject->createUser([
            'identity' => 'test@dotkernel.com',
        ]);
    }

    public function testCreateUserThrowsExceptionRestrictionRoles()
    {
        $this->userRoleService->method('findOneBy')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::RESTRICTION_ROLES);

        $this->subject->createUser($this->getUser());
    }

    public function testCreateUserWithMultipleRoles()
    {
        $this->userRoleService->method('findOneBy')->willReturn(new UserRole());

        $data = $this->getUser([
            'roles' => [
                [
                    'uuid' => 1,
                ],
                [
                    'uuid' => 2,
                ],
            ]
        ]);

        $user = $this->subject->createUser($data);
        $this->assertCount(count($data['roles']), $user->getRoles());
    }

    public function testCreateUserWithDefaultRole()
    {
        $defaultRole = (new UserRole())->setName(UserRole::ROLE_USER);
        $this->userRoleService->method('findOneBy')->willReturn($defaultRole);

        $user = $this->subject->createUser($this->getUser());

        $this->assertCount(1, $user->getRoles());
        $this->assertSame($defaultRole->getName(), ($user->getRoles()->first())->getName());
    }

    public function testCreateUser()
    {
        $this->userRoleService->method('findOneBy')->willReturn(new UserRole());

        $data = $this->getUser();
        $user = $this->subject->createUser($data);

        $this->assertSame($data['identity'], $user->getIdentity());
        $this->assertTrue(password_verify($data['password'], $user->getPassword()));
        $this->assertInstanceOf(UserDetail::class, $user->getDetail());
        $this->assertSame($data['detail']['firstName'], $user->getDetail()->getFirstName());
        $this->assertSame($data['detail']['lastName'], $user->getDetail()->getLastName());
        $this->assertSame($data['detail']['email'], $user->getDetail()->getEmail());
        $this->assertSame(User::STATUS_PENDING, $user->getStatus());
        $this->assertFalse($user->isDeleted());
        $this->assertFalse($user->isActive());
    }

    public function testUpdateUserThrowsExceptionDuplicateIdentity()
    {
        $this->userRepository->method('exists')->willReturn(true);

        $this->expectException(ORMException::class);

        $user = new User();

        $this->subject->updateUser($user, [
            'identity' => 'test@dotkernel.com',
        ]);
    }

    public function testUpdateUserThrowsExceptionDuplicateUserDetailEmail()
    {
        $user = new User();
        $this->userRepository->method('emailExists')->willReturn($user);

        $this->expectException(ORMException::class);

        $this->subject->updateUser($user, [
            'detail' => [
                'email' => 'test@dotkernel.com'
            ],
        ]);
    }

    public function testUpdateUserThrowsExceptionRestrictionRoles()
    {
        $user = new User();
        $this->userRoleService->method('findOneBy')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Message::RESTRICTION_ROLES);

        $this->subject->updateUser($user, [
            'roles' => [
                [
                    'uuid' => 1
                ]
            ]
        ]);
    }

    public function testUpdateUser()
    {
        $data = $this->getUser();

        $user = new User();
        $userDetail = new UserDetail();
        $role = new UserRole();

        $userDetail->setEmail($data['detail']['firstName']);
        $userDetail->setEmail($data['detail']['lastName']);
        $userDetail->setEmail($data['detail']['email']);

        $user->setIdentity($data['identity']);
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setDetail($userDetail);
        $user->addRole($role);

        $updateData = [
            'identity' => 'test@test.com',
            'password' => '654321',
            'detail' => [
                'firstName' => 'firstname',
                'lastName' => 'lastname',
                'email' => 'email@test.com',
            ],
        ];

        $updatedUser = $this->subject->updateUser($user, $updateData);

        $this->assertSame($updateData['identity'], $updatedUser->getIdentity());
        $this->assertTrue(password_verify($updateData['password'], $updatedUser->getPassword()));
        $this->assertSame($updateData['detail']['firstName'], $updatedUser->getDetail()->getFirstName());
        $this->assertSame($updateData['detail']['lastName'], $updatedUser->getDetail()->getLastName());
        $this->assertSame($updateData['detail']['email'], $updatedUser->getDetail()->getEmail());
    }

//    public function testCreateUserAvatar()
//    {
//        $subject = $this->getMockBuilder(UserService::class)
//            ->setConstructorArgs([
//                $this->userRoleService,
//                $this->mailService,
//                $this->templateRendererInterface,
//                $this->userRepository,
//                $this->userAvatarRepository,
//                $this->userDetailRepository,
//                $this->config,
//            ])
//            ->onlyMethods([
//                'deleteAvatarFile',
//                'getUserAvatarDirectoryPath',
//                'ensurePathExists',
//            ])
//            ->getMock();
//
//        $user = new User();
//        $uploadedFile = $this->createMock(UploadedFile::class);
//        $uploadedFile->method('getClientMediaType')->willReturn('image/jpg');
//
//        $userAvatar = $subject->createAvatar($user, $uploadedFile);
//
//        $this->assertSame($user, $userAvatar->getUser());
//        $this->assertStringContainsString('.jpg', $userAvatar->getName());
//    }

    private function getUser(array $data = []): array
    {
        $user = [
            'identity' => 'test@dotkernel.com',
            'password' => '123456',
            'detail' => [
                'firstName' => 'first',
                'lastName' => 'last',
                'email' => 'test@dotkernel2.com',
            ]
        ];

        return array_merge($user, $data);
    }
}

