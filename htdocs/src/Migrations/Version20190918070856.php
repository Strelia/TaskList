<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190918070856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tasks_lists (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, eta INT NOT NULL, left_eta INT NOT NULL, spend INT NOT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, task_list_id INT DEFAULT NULL, left_eta INT NOT NULL, name VARCHAR(50) NOT NULL, status SMALLINT NOT NULL, description TEXT DEFAULT NULL, eta INT NOT NULL, spend INT NOT NULL, INDEX IDX_F06D3970FE54D947 (group_id), INDEX IDX_F06D3970224F3C61 (task_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, created_at DATETIME NOT NULL, spend TIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(50) NOT NULL, status SMALLINT NOT NULL, description TEXT DEFAULT NULL, eta INT NOT NULL, INDEX IDX_50586597FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D3970FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D3970224F3C61 FOREIGN KEY (task_list_id) REFERENCES tasks_lists (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY FK_F06D3970224F3C61');
        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY FK_F06D3970FE54D947');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597FE54D947');
        $this->addSql('DROP TABLE tasks_lists');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE tasks');
    }
}
