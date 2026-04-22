<?php

declare(strict_types=1);

namespace ApiTest\Unit\User\Service;

use Api\User\Service\UserAvatarService;
use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Core\User\Repository\UserAvatarRepository;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class UserAvatarServiceTest extends TestCase
{
    private UserAvatarService&MockObject $subject;
    private UploadedFile&Stub $uploadedFile;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $userAvatarRepository = $this->createStub(UserAvatarRepository::class);
        $this->uploadedFile   = $this->createStub(UploadedFile::class);
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

        $this->subject->expects($this->once())
            ->method('getUserAvatarDirectoryPath')
            ->willReturn('/test/');

        $this->subject->expects($this->once())
            ->method('ensureDirectoryExists')
            ->with('/test/');

        $this->subject->expects($this->once())
            ->method('deleteAvatarFile')
            ->with('/test/test');

        $this->subject->expects($this->once())
            ->method('createFileName')
            ->willReturn($fileName);

        $user   = $this->getUser();
        $avatar = $this->subject->saveAvatar($user, $this->uploadedFile);

        $this->assertSame($fileName, $avatar->getName());
    }

    public function testCreateAvatarDefault(): void
    {
        $fileName = 'file_name';

        $this->subject->expects($this->once())
            ->method('getUserAvatarDirectoryPath')
            ->willReturn('/test/');

        $this->subject->expects($this->once())
            ->method('ensureDirectoryExists')
            ->with('/test/');

        $this->subject->expects($this->never())
            ->method('deleteAvatarFile');

        $this->subject->expects($this->once())
            ->method('createFileName')
            ->willReturn($fileName);

        $user   = new User();
        $avatar = $this->subject->saveAvatar($user, $this->uploadedFile);

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
