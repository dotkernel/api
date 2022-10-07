<?php

namespace AppTest\Functional;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use AppTest\Helper\AbstractFunctionalTest;
use AppTest\Helper\DatabaseTrait;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Class AdminTest
 * @package AppTest\Functional
 */
class AdminTest extends AbstractFunctionalTest
{
    use DatabaseTrait;

    public function testAdminCanListAdminAccounts()
    {
        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $response = $this->get('/admin');

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testAdminCanCreateAdmin()
    {
        $this->loginAs('admin', 'dotadmin', 'admin', 'admin');

        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);
        $adminRepository = $this->getEntityManager()->getRepository(Admin::class);

        /** @var AdminRole $adminRole */
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);

        $requestBody = [
            'identity' => 'admin@test.com',
            'password' => '123456',
            'passwordConfirm' => '123456',
            'firstName' => 'Admin',
            'lastName' => 'Test',
            'roles' => [
                [
                    'uuid' => $adminRole->getUuid()->toString()
                ]
            ]
        ];

        $response = $this->post('/admin', $requestBody);

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        /** @var Admin $newAdmin */
        $newAdmin = $adminRepository->findOneBy(['identity' => $requestBody['identity']]);


    }
}
