<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201208131949 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Added email column to user_detail table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user_detail` ADD `email` varchar(191) DEFAULT NULL AFTER `userUuid`, ADD UNIQUE KEY (`email`)');
        $this->addSql("UPDATE `user_role` SET `name` = CASE `name` WHEN 'admin' THEN 'user' WHEN 'member' THEN 'guest' END WHERE `name` IN ('admin', 'member')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
