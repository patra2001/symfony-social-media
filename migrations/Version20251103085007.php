<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103085007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE external_post (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD extra_data LONGTEXT DEFAULT NULL, CHANGE type type VARCHAR(20) NOT NULL');
        $this->addSql('DROP INDEX unique_state_country ON states');
        $this->addSql('DROP INDEX idx_country_code ON states');
        $this->addSql('ALTER TABLE states ADD country_id INT NOT NULL, DROP state_code, DROP country_code, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE states ADD CONSTRAINT FK_31C2774DF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('CREATE INDEX IDX_31C2774DF92F3E70 ON states (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE external_post');
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16B5D83CC1');
        $this->addSql('ALTER TABLE post DROP extra_data, CHANGE type type VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE states DROP FOREIGN KEY FK_31C2774DF92F3E70');
        $this->addSql('DROP INDEX IDX_31C2774DF92F3E70 ON states');
        $this->addSql('ALTER TABLE states ADD state_code VARCHAR(100) NOT NULL, ADD country_code CHAR(2) NOT NULL, DROP country_id, CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_state_country ON states (state_code, country_code)');
        $this->addSql('CREATE INDEX idx_country_code ON states (country_code)');
    }
}
