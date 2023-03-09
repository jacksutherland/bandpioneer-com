<?php
namespace bandpioneer\rockstar\migrations;

// use bandpioneer\rockstar\elements\Comment;

use Craft;
use craft\db\Migration;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\MigrationHelper;
use craft\records\FieldLayout;

class Install extends Migration
{
	public function safeUp(): bool
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    public function safeDown(): bool
    {
        // THIS WILL WIPE OUT ALL BAND DATA!

        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    public function createTables(): void
    {
        $this->archiveTableIfExists('{{%rockstar_bands}}');
        $this->createTable('{{%rockstar_bands}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'name' => $this->string(),
            'phone' => $this->string(),
            'email' => $this->string(),
            'websiteUrl' => $this->string(),
            'description' => $this->text(),
            'logoId' => $this->integer(),
            'genres' => $this->string(),
            'members' => $this->text(),
            'setList' => $this->text(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%rockstar_epks}}');
        $this->createTable('{{%rockstar_epks}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'bandId' => $this->integer(),
            'priceRange' => $this->string(),
            'gigLength' => $this->string(),
            'insurance' => $this->string(),
            'cta' => $this->string(),
            'slug' => $this->string(),
            'bio' => $this->text(),
            'requirements' => $this->text(),
            'socialMedia' => $this->text(),
            'videos' => $this->text(),
            'images' => $this->text(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ]);
    }

    public function createIndexes(): void
    {
        $this->createIndex(null, '{{%rockstar_bands}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_bands}}', 'userId', false);

        $this->createIndex(null, '{{%rockstar_epks}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_epks}}', 'userId', false);
        $this->createIndex(null, '{{%rockstar_epks}}', 'bandId', false);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%rockstar_bands}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_bands}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);
        $this->addForeignKey(null, '{{%rockstar_bands}}', 'logoId', Table::ASSETS, 'id', 'SET NULL', null);

        $this->addForeignKey(null, '{{%rockstar_epks}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_epks}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);
        $this->addForeignKey(null, '{{%rockstar_epks}}', 'bandId', '{{%rockstar_bands}}', 'id', 'SET NULL', null);
    }

    public function dropTables(): void
    {
        $this->dropTableIfExists('{{%rockstar_bands}}');

        $this->dropTableIfExists('{{%rockstar_epks}}');
    }

    public function dropForeignKeys(): void
    {
        if ($this->db->tableExists('{{%rockstar_bands}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_bands}}', $this);
        }

        if ($this->db->tableExists('{{%rockstar_epks}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_epks}}', $this);
        }
    }
}