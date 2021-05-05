<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200515144435 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quickbooks_user (qb_username VARCHAR(40) NOT NULL, user_id INT NOT NULL, qb_password VARCHAR(255) NOT NULL, company_name VARCHAR(255) DEFAULT NULL, qb_company_file VARCHAR(255) DEFAULT NULL, base_currency VARCHAR(3) DEFAULT NULL, multi_currency_enabled TINYINT(1) NOT NULL, qbwc_wait_before_next_update INT DEFAULT NULL, qbwc_min_run_every_n_seconds INT DEFAULT NULL, status VARCHAR(1) NOT NULL, write_datetime DATETIME NOT NULL, touch_datetime DATETIME NOT NULL, decimal_symbol VARCHAR(1) NOT NULL, digit_grouping_symbol VARCHAR(1) NOT NULL, xml LONGTEXT DEFAULT NULL, INDEX IDX_EE77BEA9A76ED395 (user_id), PRIMARY KEY(qb_username)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quickbooks_queue (quickbooks_queue_id INT AUTO_INCREMENT NOT NULL, qb_username VARCHAR(40) NOT NULL, quickbooks_ticket_id INT DEFAULT NULL, qb_action VARCHAR(32) NOT NULL, ident VARCHAR(40) NOT NULL, extra LONGTEXT DEFAULT NULL, qbxml LONGTEXT DEFAULT NULL, priority INT DEFAULT NULL, qb_status VARCHAR(1) NOT NULL, msg LONGTEXT DEFAULT NULL, enqueue_datetime DATETIME NOT NULL, dequeue_datetime DATETIME DEFAULT NULL, INDEX IDX_DF947973B1AEBF2B (qb_username), INDEX quickbooks_ticket_id (quickbooks_ticket_id), INDEX qb_status (qb_status), INDEX qb_username (qb_username, qb_action, ident, qb_status), INDEX priority (priority), PRIMARY KEY(quickbooks_queue_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quickbooks_account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, qb_username VARCHAR(40) NOT NULL, full_name VARCHAR(159) NOT NULL, currency VARCHAR(64) DEFAULT NULL, account_type VARCHAR(64) NOT NULL, special_account_type VARCHAR(64) DEFAULT NULL, account_number VARCHAR(7) DEFAULT NULL, INDEX IDX_DE9741A9A76ED395 (user_id), INDEX IDX_DE9741A9B1AEBF2B (qb_username), INDEX search_idx (full_name, account_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quickbooks_user ADD CONSTRAINT FK_EE77BEA9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quickbooks_queue ADD CONSTRAINT FK_DF947973B1AEBF2B FOREIGN KEY (qb_username) REFERENCES quickbooks_user (qb_username)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quickbooks_account ADD CONSTRAINT FK_DE9741A9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quickbooks_account ADD CONSTRAINT FK_DE9741A9B1AEBF2B FOREIGN KEY (qb_username) REFERENCES quickbooks_user (qb_username)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quickbooks_queue DROP FOREIGN KEY FK_DF947973B1AEBF2B');
        $this->addSql('ALTER TABLE quickbooks_account DROP FOREIGN KEY FK_DE9741A9B1AEBF2B');
        $this->addSql('ALTER TABLE quickbooks_user DROP FOREIGN KEY FK_EE77BEA9A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE quickbooks_account DROP FOREIGN KEY FK_DE9741A9A76ED395');
        $this->addSql('DROP TABLE quickbooks_user');
        $this->addSql('DROP TABLE quickbooks_queue');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE quickbooks_account');
    }
}
