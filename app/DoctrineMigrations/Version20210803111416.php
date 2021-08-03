<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210803111416 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE alm_adoption_pets (id INT AUTO_INCREMENT NOT NULL, bleeder_id INT NOT NULL, pet_kind SMALLINT NOT NULL, breeds_type INT NOT NULL, pet_sex SMALLINT NOT NULL, pet_birthday DATE NOT NULL, coat_color INT NOT NULL, future_wait SMALLINT NOT NULL, dna_check_result INT NOT NULL, pr_comment LONGTEXT NOT NULL, description LONGTEXT NOT NULL, delivery_time LONGTEXT NOT NULL, delivery_way LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alm_bleeder_pets (id INT AUTO_INCREMENT NOT NULL, bleeder_id INT NOT NULL, pet_kind SMALLINT NOT NULL, breeds_type INT NOT NULL, pet_sex SMALLINT NOT NULL, pet_birthday DATE NOT NULL, coat_color INT NOT NULL, future_wait SMALLINT NOT NULL, dna_check_result INT NOT NULL, pr_comment LONGTEXT NOT NULL, description LONGTEXT NOT NULL, is_breeding SMALLINT NOT NULL, is_selling SMALLINT NOT NULL, guarantee LONGTEXT NOT NULL, is_pedigree SMALLINT NOT NULL, include_vaccine_fee SMALLINT NOT NULL, delivery_time LONGTEXT NOT NULL, delivery_way LONGTEXT NOT NULL, payment_method LONGTEXT NOT NULL, reservation_fee INT NOT NULL, price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alm_bleeders (id INT AUTO_INCREMENT NOT NULL, breeder_house_name VARCHAR(255) DEFAULT NULL, owner_name VARCHAR(255) DEFAULT NULL, owner_kana VARCHAR(255) DEFAULT NULL, breeder_house_tel VARCHAR(10) DEFAULT NULL, breeder_house_fax VARCHAR(10) DEFAULT NULL, breeder_house_zip VARCHAR(7) DEFAULT NULL, breeder_house_pref VARCHAR(10) DEFAULT NULL, breeder_house_city VARCHAR(10) DEFAULT NULL, breeder_house_address VARCHAR(255) DEFAULT NULL, breeder_house_building VARCHAR(255) DEFAULT NULL, responsible_name VARCHAR(255) DEFAULT NULL, responsible_kana VARCHAR(255) DEFAULT NULL, responsible_zip VARCHAR(7) DEFAULT NULL, responsible_pref VARCHAR(10) DEFAULT NULL, responsible_city VARCHAR(10) DEFAULT NULL, responsible_address VARCHAR(255) DEFAULT NULL, office_name VARCHAR(255) DEFAULT NULL, authorization_type SMALLINT DEFAULT NULL, pet_parent_count SMALLINT DEFAULT NULL, staff_count_1 SMALLINT DEFAULT NULL, staff_count_2 SMALLINT DEFAULT NULL, staff_count_3 SMALLINT DEFAULT NULL, staff_count_4 SMALLINT DEFAULT NULL, breed_exp_year SMALLINT DEFAULT NULL, is_participation_show SMALLINT DEFAULT NULL, cage_size SMALLINT DEFAULT NULL, pet_exercise_env SMALLINT DEFAULT NULL, can_publish_count SMALLINT DEFAULT NULL, self_breed_exp_year SMALLINT DEFAULT NULL, direct_sell_exp SMALLINT DEFAULT NULL, is_pet_trade SMALLINT DEFAULT NULL, sell_route VARCHAR(255) DEFAULT NULL, is_full_time SMALLINT DEFAULT NULL, homepage_url VARCHAR(255) DEFAULT NULL, sns_url VARCHAR(255) DEFAULT NULL, regist_reason LONGTEXT DEFAULT NULL, free_comment LONGTEXT DEFAULT NULL, introducer_name VARCHAR(255) DEFAULT NULL, examination_status SMALLINT DEFAULT NULL, is_active SMALLINT DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alm_breeds (id INT AUTO_INCREMENT NOT NULL, pet_kind SMALLINT NOT NULL, breeds_name VARCHAR(255) NOT NULL, sort_oder INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alm_coat_colors (id INT AUTO_INCREMENT NOT NULL, pet_kind SMALLINT NOT NULL, coat_color_name VARCHAR(255) NOT NULL, sort_oder INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alm_adoptions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, adoption_house_name VARCHAR(255) DEFAULT NULL, owner_name VARCHAR(255) DEFAULT NULL, owner_kana VARCHAR(255) DEFAULT NULL, adoption_house_zip VARCHAR(7) DEFAULT NULL, adoption_house_pref VARCHAR(10) DEFAULT NULL, adoption_house_city VARCHAR(10) DEFAULT NULL, adoption_house_address VARCHAR(255) DEFAULT NULL, adoption_house_building VARCHAR(255) DEFAULT NULL, adoption_house_tel VARCHAR(10) DEFAULT NULL, adoption_house_fax VARCHAR(10) DEFAULT NULL, homepage_url VARCHAR(255) DEFAULT NULL, sns_url VARCHAR(255) DEFAULT NULL, is_active SMALLINT DEFAULT NULL, examination_status SMALLINT DEFAULT NULL, regist_reason LONGTEXT DEFAULT NULL, free_comment LONGTEXT DEFAULT NULL, can_publish_count SMALLINT DEFAULT NULL, pet_exercise_env SMALLINT DEFAULT NULL, cage_size SMALLINT DEFAULT NULL, adoption_exp_year SMALLINT DEFAULT NULL, staff_count_1 SMALLINT DEFAULT NULL, staff_count_2 SMALLINT DEFAULT NULL, staff_count_3 SMALLINT DEFAULT NULL, staff_count_4 SMALLINT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE alm_adoption_pets');
        $this->addSql('DROP TABLE alm_bleeder_pets');
        $this->addSql('DROP TABLE alm_bleeders');
        $this->addSql('DROP TABLE alm_breeds');
        $this->addSql('DROP TABLE alm_coat_colors');
        $this->addSql('DROP TABLE alm_adoptions');
    }
}
