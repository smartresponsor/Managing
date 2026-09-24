<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260924190351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manage_crud_field_view_profile_rule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject_identifier VARCHAR(220) NOT NULL, resource_key VARCHAR(255) NOT NULL, page_name VARCHAR(80) NOT NULL, visible_fields CLOB NOT NULL, hidden_fields CLOB NOT NULL, actor_identifier VARCHAR(220) DEFAULT NULL, reason CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX idx_manage_crud_field_view_profile_subject ON manage_crud_field_view_profile_rule (subject_identifier)');
        $this->addSql('CREATE INDEX idx_manage_crud_field_view_profile_resource ON manage_crud_field_view_profile_rule (resource_key)');
        $this->addSql('CREATE INDEX idx_manage_crud_field_view_profile_page ON manage_crud_field_view_profile_rule (page_name)');
        $this->addSql('CREATE UNIQUE INDEX uniq_manage_crud_field_view_profile_rule_scope ON manage_crud_field_view_profile_rule (subject_identifier, resource_key, page_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE manage_crud_field_view_profile_rule');
    }
}
