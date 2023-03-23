<?php

declare(strict_types=1);

namespace Api\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221007111306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database structure.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `admin` (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', identity VARCHAR(100) NOT NULL, firstName VARCHAR(255) NOT NULL, lastName VARCHAR(255) NOT NULL, password VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_880E0D766A95E9C4 (identity), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_roles (userUuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', roleUuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', INDEX IDX_1614D53DD73087E9 (userUuid), INDEX IDX_1614D53D88446210 (roleUuid), PRIMARY KEY(userUuid, roleUuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_role (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', name VARCHAR(30) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_7770088A5E237E06 (name), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_access_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, token VARCHAR(100) NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CA42527C19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_access_token_scopes (access_token_id INT UNSIGNED NOT NULL, scope_id INT UNSIGNED NOT NULL, INDEX IDX_9FDF62E92CCB2688 (access_token_id), INDEX IDX_9FDF62E9682B5931 (scope_id), PRIMARY KEY(access_token_id, scope_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_auth_codes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, client_id INT UNSIGNED DEFAULT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expiresDatetime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BB493F8319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_auth_code_scopes (auth_code_id INT UNSIGNED NOT NULL, scope_id INT UNSIGNED NOT NULL, INDEX IDX_988BFFBF69FEDEE4 (auth_code_id), INDEX IDX_988BFFBF682B5931 (scope_id), PRIMARY KEY(auth_code_id, scope_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_clients (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', name VARCHAR(40) NOT NULL, secret VARCHAR(100) DEFAULT NULL, redirect VARCHAR(191) NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, isConfidential TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_13CE8101A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_refresh_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, access_token_id INT UNSIGNED DEFAULT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5AB6872CCB2688 (access_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_scopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, scope VARCHAR(191) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', identity VARCHAR(191) NOT NULL, password VARCHAR(191) NOT NULL, status VARCHAR(20) NOT NULL, isDeleted TINYINT(1) NOT NULL, hash VARCHAR(64) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D6496A95E9C4 (identity), UNIQUE INDEX UNIQ_8D93D649D1B862B8 (hash), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_roles (userUuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', roleUuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', INDEX IDX_54FCD59FD73087E9 (userUuid), INDEX IDX_54FCD59F88446210 (roleUuid), PRIMARY KEY(userUuid, roleUuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_avatar (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', name VARCHAR(191) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', userUuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', UNIQUE INDEX UNIQ_73256912D73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_detail (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', firstName VARCHAR(191) DEFAULT NULL, lastName VARCHAR(191) DEFAULT NULL, email VARCHAR(191) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', userUuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', UNIQUE INDEX UNIQ_4B5464AED73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reset_password (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', expires DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', hash VARCHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', userUuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', UNIQUE INDEX UNIQ_D21DE3BCD1B862B8 (hash), INDEX IDX_D21DE3BCD73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', name VARCHAR(20) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_2DE8C6A35E237E06 (name), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_roles ADD CONSTRAINT FK_1614D53DD73087E9 FOREIGN KEY (userUuid) REFERENCES `admin` (uuid)');
        $this->addSql('ALTER TABLE admin_roles ADD CONSTRAINT FK_1614D53D88446210 FOREIGN KEY (roleUuid) REFERENCES admin_role (uuid)');
        $this->addSql('ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE oauth_access_token_scopes ADD CONSTRAINT FK_9FDF62E92CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id)');
        $this->addSql('ALTER TABLE oauth_access_token_scopes ADD CONSTRAINT FK_9FDF62E9682B5931 FOREIGN KEY (scope_id) REFERENCES oauth_scopes (id)');
        $this->addSql('ALTER TABLE oauth_auth_codes ADD CONSTRAINT FK_BB493F8319EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE oauth_auth_code_scopes ADD CONSTRAINT FK_988BFFBF69FEDEE4 FOREIGN KEY (auth_code_id) REFERENCES oauth_auth_codes (id)');
        $this->addSql('ALTER TABLE oauth_auth_code_scopes ADD CONSTRAINT FK_988BFFBF682B5931 FOREIGN KEY (scope_id) REFERENCES oauth_scopes (id)');
        $this->addSql('ALTER TABLE oauth_clients ADD CONSTRAINT FK_13CE8101A76ED395 FOREIGN KEY (user_id) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB6872CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59F88446210 FOREIGN KEY (roleUuid) REFERENCES user_role (uuid)');
        $this->addSql('ALTER TABLE user_avatar ADD CONSTRAINT FK_73256912D73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE user_detail ADD CONSTRAINT FK_4B5464AED73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)');
        $this->addSql('ALTER TABLE user_reset_password ADD CONSTRAINT FK_D21DE3BCD73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_roles DROP FOREIGN KEY FK_1614D53DD73087E9');
        $this->addSql('ALTER TABLE admin_roles DROP FOREIGN KEY FK_1614D53D88446210');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C19EB6921');
        $this->addSql('ALTER TABLE oauth_access_token_scopes DROP FOREIGN KEY FK_9FDF62E92CCB2688');
        $this->addSql('ALTER TABLE oauth_access_token_scopes DROP FOREIGN KEY FK_9FDF62E9682B5931');
        $this->addSql('ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8319EB6921');
        $this->addSql('ALTER TABLE oauth_auth_code_scopes DROP FOREIGN KEY FK_988BFFBF69FEDEE4');
        $this->addSql('ALTER TABLE oauth_auth_code_scopes DROP FOREIGN KEY FK_988BFFBF682B5931');
        $this->addSql('ALTER TABLE oauth_clients DROP FOREIGN KEY FK_13CE8101A76ED395');
        $this->addSql('ALTER TABLE oauth_refresh_tokens DROP FOREIGN KEY FK_5AB6872CCB2688');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD73087E9');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59F88446210');
        $this->addSql('ALTER TABLE user_avatar DROP FOREIGN KEY FK_73256912D73087E9');
        $this->addSql('ALTER TABLE user_detail DROP FOREIGN KEY FK_4B5464AED73087E9');
        $this->addSql('ALTER TABLE user_reset_password DROP FOREIGN KEY FK_D21DE3BCD73087E9');
        $this->addSql('DROP TABLE `admin`');
        $this->addSql('DROP TABLE admin_roles');
        $this->addSql('DROP TABLE admin_role');
        $this->addSql('DROP TABLE oauth_access_tokens');
        $this->addSql('DROP TABLE oauth_access_token_scopes');
        $this->addSql('DROP TABLE oauth_auth_codes');
        $this->addSql('DROP TABLE oauth_auth_code_scopes');
        $this->addSql('DROP TABLE oauth_clients');
        $this->addSql('DROP TABLE oauth_refresh_tokens');
        $this->addSql('DROP TABLE oauth_scopes');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE user_avatar');
        $this->addSql('DROP TABLE user_detail');
        $this->addSql('DROP TABLE user_reset_password');
        $this->addSql('DROP TABLE user_role');
    }
}
