<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190722114323 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quickbooks_config (quickbooks_config_id INT UNSIGNED AUTO_INCREMENT NOT NULL, qb_username VARCHAR(40) NOT NULL COLLATE utf8_general_ci, module VARCHAR(40) NOT NULL COLLATE utf8_general_ci, cfgkey VARCHAR(40) NOT NULL COLLATE utf8_general_ci, cfgval VARCHAR(40) NOT NULL COLLATE utf8_general_ci, cfgtype VARCHAR(40) NOT NULL COLLATE utf8_general_ci, cfgopts TEXT NOT NULL COLLATE utf8_general_ci, write_datetime DATETIME NOT NULL, mod_datetime DATETIME NOT NULL, PRIMARY KEY(quickbooks_config_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quickbooks_log (quickbooks_log_id INT UNSIGNED AUTO_INCREMENT NOT NULL, quickbooks_ticket_id INT UNSIGNED DEFAULT NULL, batch INT UNSIGNED NOT NULL, msg TEXT NOT NULL COLLATE utf8_general_ci, log_datetime DATETIME NOT NULL, INDEX batch (batch), INDEX quickbooks_ticket_id (quickbooks_ticket_id), PRIMARY KEY(quickbooks_log_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quickbooks_oauthv1 (quickbooks_oauthv1_id INT UNSIGNED AUTO_INCREMENT NOT NULL, app_username VARCHAR(255) NOT NULL COLLATE utf8_general_ci, app_tenant VARCHAR(255) NOT NULL COLLATE utf8_general_ci, oauth_request_token VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, oauth_request_token_secret VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, oauth_access_token VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, oauth_access_token_secret VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, qb_realm VARCHAR(32) DEFAULT NULL COLLATE utf8_general_ci, qb_flavor VARCHAR(12) DEFAULT NULL COLLATE utf8_general_ci, qb_user VARCHAR(64) DEFAULT NULL COLLATE utf8_general_ci, request_datetime DATETIME NOT NULL, access_datetime DATETIME DEFAULT NULL, touch_datetime DATETIME DEFAULT NULL, PRIMARY KEY(quickbooks_oauthv1_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quickbooks_oauthv2 (quickbooks_oauthv2_id INT UNSIGNED AUTO_INCREMENT NOT NULL, app_tenant VARCHAR(255) NOT NULL COLLATE utf8_general_ci, oauth_state VARCHAR(255) NOT NULL COLLATE utf8_general_ci, oauth_access_token TEXT NOT NULL COLLATE utf8_general_ci, oauth_refresh_token TEXT NOT NULL COLLATE utf8_general_ci, oauth_access_expiry DATETIME DEFAULT NULL, oauth_refresh_expiry DATETIME DEFAULT NULL, qb_realm VARCHAR(32) DEFAULT NULL COLLATE utf8_general_ci, request_datetime DATETIME NOT NULL, access_datetime DATETIME DEFAULT NULL, last_access_datetime DATETIME DEFAULT NULL, last_refresh_datetime DATETIME DEFAULT NULL, touch_datetime DATETIME DEFAULT NULL, PRIMARY KEY(quickbooks_oauthv2_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quickbooks_recur (quickbooks_recur_id INT UNSIGNED AUTO_INCREMENT NOT NULL, qb_username VARCHAR(40) NOT NULL COLLATE utf8_general_ci, qb_action VARCHAR(32) NOT NULL COLLATE utf8_general_ci, ident VARCHAR(40) NOT NULL COLLATE utf8_general_ci, extra TEXT DEFAULT NULL COLLATE utf8_general_ci, qbxml TEXT DEFAULT NULL COLLATE utf8_general_ci, priority INT UNSIGNED DEFAULT 0, run_every INT UNSIGNED NOT NULL, recur_lasttime INT UNSIGNED NOT NULL, enqueue_datetime DATETIME NOT NULL, INDEX priority (priority), INDEX qb_username (qb_username, qb_action, ident), PRIMARY KEY(quickbooks_recur_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quickbooks_ticket (quickbooks_ticket_id INT UNSIGNED AUTO_INCREMENT NOT NULL, qb_username VARCHAR(40) NOT NULL COLLATE utf8_general_ci, ticket CHAR(36) NOT NULL COLLATE utf8_general_ci, processed INT UNSIGNED DEFAULT 0, lasterror_num VARCHAR(32) DEFAULT NULL COLLATE utf8_general_ci, lasterror_msg VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci, ipaddr CHAR(15) NOT NULL COLLATE utf8_general_ci, write_datetime DATETIME NOT NULL, touch_datetime DATETIME NOT NULL, INDEX ticket (ticket), PRIMARY KEY(quickbooks_ticket_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE quickbooks_config');
        $this->addSql('DROP TABLE quickbooks_log');
        $this->addSql('DROP TABLE quickbooks_oauthv1');
        $this->addSql('DROP TABLE quickbooks_oauthv2');
        $this->addSql('DROP TABLE quickbooks_recur');
        $this->addSql('DROP TABLE quickbooks_ticket');
    }
}
