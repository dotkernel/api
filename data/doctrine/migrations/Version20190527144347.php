<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\Version;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190527144347 extends AbstractMigration
{
    public function __construct(Version $version)
    {
        parent::__construct($version);

        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
        "INSERT INTO oauth_clients (name, secret, redirect, personal_access_client, password_client, created_at) 
            VALUES ('dotkernel', '$2y$10$5asVMXKmdptyrYZ82k7YcOPCSSFz7xSp5AxzxD3fsr.ZnbAztFW8u', '/', 1, 1, NOW())"
        );

        $this->addSql("INSERT INTO user_role(uuid, name) VALUES(0x11e9650380c6d846818a00155daa5500, 'member')");

        $this->addSql("INSERT INTO oauth_scopes(id) VALUES('api')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('TRUNCATE TABLE oauth_clients');

        $this->addSql('TRUNCATE TABLE oauth_scopes');

        $this->addSql('TRUNCATE TABLE user_role');
    }
}
