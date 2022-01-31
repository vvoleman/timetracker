<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220128144102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD employer_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE41CD9E7A FOREIGN KEY (employer_id) REFERENCES employer (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE41CD9E7A ON project (employer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE41CD9E7A');
        $this->addSql('DROP INDEX IDX_2FB3D0EE41CD9E7A ON project');
        $this->addSql('ALTER TABLE project DROP employer_id');
    }
}
