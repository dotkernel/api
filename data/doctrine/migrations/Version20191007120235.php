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
        // Create structure
        $this->addSql('CREATE TABLE `oauth_access_tokens` (`id` varchar(100) NOT NULL, `user_id` varchar(40) DEFAULT NULL, `client_id` varchar(40) DEFAULT NULL, `name` varchar(255) DEFAULT NULL, `scopes` text DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_auth_codes` (`id` varchar(100) NOT NULL, `user_id` int(11) DEFAULT NULL, `client_id` int(11) DEFAULT NULL, `scopes` text DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_clients` (`name` varchar(40) NOT NULL, `user_id` int(11) DEFAULT NULL, `secret` varchar(100) DEFAULT NULL, `redirect` varchar(255) DEFAULT NULL, `personal_access_client` tinyint(1) DEFAULT NULL, `password_client` tinyint(1) DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_personal_access_clients` (`client_id` int(11) DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_refresh_tokens` (`id` varchar(100) NOT NULL, `access_token_id` varchar(100) DEFAULT NULL, `revoked` tinyint(1) DEFAULT NULL, `expires_at` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `oauth_scopes` (`id` varchar(30) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `identity` varchar(191) NOT NULL, `password` varchar(191) NOT NULL, `status` varchar(20) NOT NULL, `isDeleted` tinyint(1) NOT NULL, `hash` varchar(64) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_avatar` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `userUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `name` varchar(191) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_detail` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `userUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `firstname` varchar(191) DEFAULT NULL, `lastname` varchar(191) DEFAULT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_reset_password` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `userUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `hash` varchar(64) NOT NULL, `status` varchar(20) NOT NULL, `expires` datetime NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_role` (`uuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `name` varchar(20) NOT NULL, `created` datetime NOT NULL, `updated` datetime DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $this->addSql('CREATE TABLE `user_roles` (`userUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', `roleUuid` binary(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        // Add indexes
        $this->addSql('ALTER TABLE `oauth_access_tokens` ADD PRIMARY KEY (`id`), ADD KEY `idx1_oauth_access_tokens` (`user_id`)');
        $this->addSql('ALTER TABLE `oauth_auth_codes` ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE `oauth_clients` ADD PRIMARY KEY (`name`), ADD KEY `idx1_oauth_clients` (`user_id`)');
        $this->addSql('ALTER TABLE `oauth_personal_access_clients` ADD KEY `idx1_oauth_personal_access_clients` (`client_id`)');
        $this->addSql('ALTER TABLE `oauth_refresh_tokens` ADD PRIMARY KEY (`id`), ADD KEY `idx1_oauth_refresh_tokens` (`access_token_id`)');
        $this->addSql('ALTER TABLE `oauth_scopes` ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE `user` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_8D93D649F85E0677` (`identity`), ADD UNIQUE KEY `UNIQ_8D93D649D1B862B8` (`hash`)');
        $this->addSql('ALTER TABLE `user_avatar` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_73256912D73087E9` (`userUuid`)');
        $this->addSql('ALTER TABLE `user_detail` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_4B5464AED73087E9` (`userUuid`)');
        $this->addSql('ALTER TABLE `user_reset_password` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_D21DE3BCD1B862B8` (`hash`), ADD KEY `IDX_D21DE3BCD73087E9` (`userUuid`)');
        $this->addSql('ALTER TABLE `user_role` ADD PRIMARY KEY (`uuid`), ADD UNIQUE KEY `UNIQ_2DE8C6A35E237E06` (`name`)');
        $this->addSql('ALTER TABLE `user_roles` ADD PRIMARY KEY (`userUuid`,`roleUuid`), ADD KEY `IDX_54FCD59FD73087E9` (`userUuid`), ADD KEY `IDX_54FCD59F88446210` (`roleUuid`)');

        // Add foreign keys
        $this->addSql('ALTER TABLE `user_avatar` ADD CONSTRAINT `FK_73256912D73087E9` FOREIGN KEY (`userUuid`) REFERENCES `user` (`uuid`)');
        $this->addSql('ALTER TABLE `user_detail` ADD CONSTRAINT `FK_4B5464AED73087E9` FOREIGN KEY (`userUuid`) REFERENCES `user` (`uuid`)');
        $this->addSql('ALTER TABLE `user_reset_password` ADD CONSTRAINT `FK_D21DE3BCD73087E9` FOREIGN KEY (`userUuid`) REFERENCES `user` (`uuid`)');
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
        $this->addSql('DROP TABLE user_reset_password');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_roles');
    }
}
