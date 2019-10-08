<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20191007120235
 * @package DotKernelApi\Migrations
 */
final class Version20191007120235 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Initial database schema.';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE `oauth_access_tokens` (`id` varchar(100) NOT NULL, `user_id` varchar(40) DEFAULT NULL, `client_id` varchar(40) DEFAULT NULL, `name` varchar(255) DEFAULT NULL, `scopes` text DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_auth_codes` (`id` varchar(100) NOT NULL, `user_id` int(11) DEFAULT NULL, `client_id` int(11) DEFAULT NULL, `scopes` text DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_clients` (`name` varchar(40) NOT NULL, `user_id` int(11) DEFAULT NULL, `secret` varchar(100) DEFAULT NULL, `redirect` varchar(255) DEFAULT NULL, `personal_access_client` tinyint(1) DEFAULT NULL, `password_client` tinyint(1) DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_personal_access_clients` (`client_id` int(11) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_refresh_tokens` (`id` varchar(100) NOT NULL, `access_token_id` varchar(100) DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_scopes` (`id` varchar(30) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `avatarUuid` binary(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `detailUuid` binary(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `username` varchar(255) NOT NULL, `password` varchar(255) NOT NULL, `status` enum(\'pending\',\'active\') DEFAULT NULL, `isDeleted` tinyint(1) NOT NULL, `hash` varchar(255) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_avatar` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `name` varchar(255) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_detail` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `firstname` varchar(255) NOT NULL, `lastname` varchar(255) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_role` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `name` varchar(255) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_roles` (`userUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `roleUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->addSql('ALTER TABLE `oauth_access_tokens` ADD PRIMARY KEY (`id`), ADD KEY `idx1_oauth_access_tokens` (`user_id`)');
        $this->addSql('ALTER TABLE `oauth_auth_codes` ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE `oauth_clients` ADD PRIMARY KEY (`name`), ADD KEY `idx1_oauth_clients` (`user_id`)');
        $this->addSql('ALTER TABLE `oauth_personal_access_clients` ADD KEY `idx1_oauth_personal_access_clients` (`client_id`)');
        $this->addSql('ALTER TABLE `oauth_refresh_tokens` ADD PRIMARY KEY (`id`), ADD KEY `idx1_oauth_refresh_tokens` (`access_token_id`)');
        $this->addSql('ALTER TABLE `oauth_scopes` ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE `user` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_8D93D649B9934F6` (`detailUuid`), ADD UNIQUE KEY `UNIQ_8D93D6494AC3A6AE` (`avatarUuid`)');
        $this->addSql('ALTER TABLE `user_avatar` ADD PRIMARY KEY (`uuid`)');
        $this->addSql('ALTER TABLE `user_detail` ADD PRIMARY KEY (`uuid`)');
        $this->addSql('ALTER TABLE `user_role` ADD PRIMARY KEY (`uuid`)');
        $this->addSql('ALTER TABLE `user_roles` ADD PRIMARY KEY (`userUuid`,`roleUuid`), ADD KEY `IDX_54FCD59FD73087E9` (`userUuid`), ADD KEY `IDX_54FCD59F88446210` (`roleUuid`)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT `FK_8D93D6494AC3A6AE` FOREIGN KEY (`avatarUuid`) REFERENCES `user_avatar` (`uuid`) ON DELETE CASCADE, ADD CONSTRAINT `FK_8D93D649B9934F6` FOREIGN KEY (`detailUuid`) REFERENCES `user_detail` (`uuid`) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `user_roles` ADD CONSTRAINT `FK_54FCD59F88446210` FOREIGN KEY (`roleUuid`) REFERENCES `user_role` (`uuid`), ADD CONSTRAINT `FK_54FCD59FD73087E9` FOREIGN KEY (`userUuid`) REFERENCES `user` (`uuid`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE oauth_access_tokens');
        $this->addSql('DROP TABLE oauth_auth_codes');
        $this->addSql('DROP TABLE oauth_clients');
        $this->addSql('DROP TABLE oauth_personal_access_clients');
        $this->addSql('DROP TABLE oauth_refresh_tokens');
        $this->addSql('DROP TABLE oauth_scopes');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_avatar');
        $this->addSql('DROP TABLE user_detail');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_roles');
    }
}
