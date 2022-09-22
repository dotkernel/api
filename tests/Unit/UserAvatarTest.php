<?php

namespace Unit;

use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Service\UserAvatarService;
use Api\User\Service\UserAvatarService as Subject;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * Class UserAvatarTest
 * @package Unit
 */
class UserAvatarTest extends TestCase
{
    private Subject $subject;

    private UploadedFile $uploadedFile;

    private array $config;

    private UserAvatarRepository $userAvatarRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userAvatarRepository = $this->createMock(UserAvatarRepository::class);
        $this->uploadedFile = $this->createMock(UploadedFile::class);
        $this->config = [];

        $this->subject = $this->getMockBuilder(UserAvatarService::class)
            ->setConstructorArgs([
                $this->userAvatarRepository,
                $this->config,
            ])
            ->onlyMethods([
                'ensureDirectoryExists',
                'getUserAvatarDirectoryPath',
                'deleteAvatarFile',
                'createFileName',
            ])
            ->getMock();

        $this->uploadedFile->method('getClientMediaType')->willReturn('image/jpg');

    }

    public function testCreateAvatarOverwrite()
    {
        $path = '/test';
        $fileName = 'file_name';
        $user = $this->getUser();

        $this->subject->method('getUserAvatarDirectoryPath')->willReturn($path);
        $this->subject->method('createFileName')->willReturn($fileName);

        $avatar = $this->subject->createAvatar($user, $this->uploadedFile);

        $this->assertInstanceOf(UserAvatar::class, $avatar);
        $this->assertSame($fileName, $avatar->getName());
    }

    public function testCreateAvatarDefault()
    {
        $path = '/test';
        $fileName = 'file_name';
        $user = new User();

        $this->subject->method('getUserAvatarDirectoryPath')->willReturn($path);
        $this->subject->method('createFileName')->willReturn($fileName);

        $avatar = $this->subject->createAvatar($user, $this->uploadedFile);

        $this->assertInstanceOf(UserAvatar::class, $avatar);
        $this->assertSame($fileName, $avatar->getName());
    }

    public function removeAvatarUserAvatarNotFound()
    {
        $result = $this->subject->removeAvatar(new User());

        $this->assertFalse($result);
    }

    public function removeAvatar()
    {
        $this->subject->method('deleteAvatarFile')->willReturn(true);

        $result = $this->subject->removeAvatar(new User());

        $this->assertTrue($result);
    }

    private function getUser(): User
    {
        $user = new User();
        $avatar = new UserAvatar();
        $avatar->setName('test');
        $avatar->setUser($user);
        $user->setAvatar($avatar);

        return $user;
    }
}
