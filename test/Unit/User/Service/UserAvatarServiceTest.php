<?php

declare(strict_types=1);

namespace ApiTest\Unit\User\Service;

use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Service\UserAvatarService;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserAvatarServiceTest extends TestCase
{
    private UserAvatarService|MockObject $subject;
    private UploadedFile $uploadedFile;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $userAvatarRepository = $this->createMock(UserAvatarRepository::class);
        $this->uploadedFile   = $this->createMock(UploadedFile::class);
        $this->subject        = $this->getMockBuilder(UserAvatarService::class)
            ->setConstructorArgs([
                $userAvatarRepository,
                [],
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

    public function testCreateAvatarOverwrite(): void
    {
        $fileName = 'file_name';

        $this->subject->method('getUserAvatarDirectoryPath')->willReturn('/test');
        $this->subject->method('createFileName')->willReturn($fileName);

        $user   = $this->getUser();
        $avatar = $this->subject->createAvatar($user, $this->uploadedFile);

        $this->assertSame($fileName, $avatar->getName());
    }

    public function testCreateAvatarDefault(): void
    {
        $fileName = 'file_name';

        $this->subject->method('getUserAvatarDirectoryPath')->willReturn('/test');
        $this->subject->method('createFileName')->willReturn($fileName);

        $user   = new User();
        $avatar = $this->subject->createAvatar($user, $this->uploadedFile);

        $this->assertSame($fileName, $avatar->getName());
    }

    private function getUser(): User
    {
        $user   = new User();
        $avatar = new UserAvatar();
        $avatar->setName('test');
        $avatar->setUser($user);
        $user->setAvatar($avatar);

        return $user;
    }
}
