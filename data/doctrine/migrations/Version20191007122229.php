<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20191007122229
 * @package DotKernelApi\Migrations
 */
final class Version20191007122229 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Initial database content.';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('INSERT INTO `oauth_clients` (`name`, `user_id`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES (\'dotkernel\', NULL, \'$2y$10$5asVMXKmdptyrYZ82k7YcOPCSSFz7xSp5AxzxD3fsr.ZnbAztFW8u\', \'/\', 1, 1, NULL, \'2019-09-24 13:43:51\', NULL)');
        $this->addSql('INSERT INTO `oauth_scopes` (`id`) VALUES (\'api\')');
        $this->addSql('INSERT INTO `user_role` (`uuid`, `name`, `created`, `updated`) VALUES (0x11e9e6a81f24525e9cbbb8ca3aa0178d, \'admin\', \'2019-10-04 00:00:00\', NULL), (0x11e9e6a8238faa8ca090b8ca3aa0178d, \'member\', \'2019-10-04 00:00:00\', NULL)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('TRUNCATE TABLE oauth_clients');
        $this->addSql('TRUNCATE TABLE oauth_scopes');
        $this->addSql('TRUNCATE TABLE user_role');
    }
}
