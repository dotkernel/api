<?php

namespace AppTest\Functional;

use Api\App\Entity\RoleInterface;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use AppTest\Functional\Traits\AuthenticationTrait;
use AppTest\Functional\Traits\DatabaseTrait;
use Laminas\Diactoros\UploadedFile;

/**
 * Class UserTest
 * @package AppTest\Functional
 */
class UserTest extends AbstractFunctionalTest
{
    use DatabaseTrait, AuthenticationTrait;

    public function testCreateUserAvatar()
    {
        $user = $this->createUser([], true);
        $uploadedFile = new UploadedFile();

        $this->loginAs($user->getIdentity(), '123456');

        $response = $this->post('/user/my-avatar', [], [], ['avatar' => $uploadedFile]);
        echo "<pre>"; var_dump($response->getBody()->getContents()); exit;
    }

    private function createUser(array $data = [], bool $activated = false): User
    {
        /** @var RoleInterface $userRole */
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);
        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);

        $user = new User();
        $userDetail = new UserDetail();

        $user->setIdentity($data['identity'] ?? 'user@test.com');
        $user->setPassword(password_hash($data['password'] ?? '123456', PASSWORD_DEFAULT));
        if ($activated) {
            $user->activate();
        }
        $userDetail->setFirstName($data['detail']['firstName'] ?? 'Test');
        $userDetail->setLastName($data['detail']['firstName'] ?? 'User');
        $userDetail->setEmail($data['detail']['email'] ?? 'user@test.com');
        $userDetail->setUser($user);

        $user->setDetail($userDetail);
        $user->addRole($userRole);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }
}
