<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110095033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Enforce the intended post type constraint
        $this->addSql('ALTER TABLE post CHANGE type type VARCHAR(20) NOT NULL');

        // Prepare to replace state_code / country_code with a nullable relation
        $this->addSql('ALTER TABLE states ADD country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE states DROP INDEX unique_state_country');
        $this->addSql('ALTER TABLE states DROP INDEX idx_country_code');
        $this->addSql('ALTER TABLE states DROP state_code');
        $this->addSql('ALTER TABLE states DROP country_code');
        $this->addSql('ALTER TABLE states CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE states ADD CONSTRAINT FK_31C2774DF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_31C2774DF92F3E70 ON states (country_id)');

        // Keep the legacy JSON stash for posts in sync with the entity
        $this->addSql('ALTER TABLE post ADD extra_data LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert post type constraint change
        $this->addSql('ALTER TABLE post CHANGE type type VARCHAR(20) DEFAULT NULL');

        // Remove the foreign key and restore text-based codes
        $this->addSql('ALTER TABLE states DROP FOREIGN KEY FK_31C2774DF92F3E70');
        $this->addSql('DROP INDEX IDX_31C2774DF92F3E70 ON states');
        $this->addSql('ALTER TABLE states ADD state_code VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE states ADD country_code CHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE states DROP country_id');
        $this->addSql('ALTER TABLE states CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_state_country ON states (state_code, country_code)');
        $this->addSql('CREATE INDEX idx_country_code ON states (country_code)');

        // Remove the extra data column added to posts
        $this->addSql('ALTER TABLE post DROP extra_data');
    }
}
