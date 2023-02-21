<?php
namespace bandpioneer\rockstar\migrations;

// use bandpioneer\rockstar\elements\Comment;

use Craft;
use craft\db\Migration;
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

        // $this->dropForeignKeys();
        // $this->dropTables();

        return true;
    }

    public function createTables(): void
    {
        $this->archiveTableIfExists('{{%bands_band}}');
        $this->createTable('{{%bands_band}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'name' => $this->string(),
            'websiteUrl' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    public function createIndexes(): void
    {
        $this->createIndex(null, '{{%bands_band}}', 'id', false);
        $this->createIndex(null, '{{%bands_band}}', 'userId', false);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%bands_band}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%bands_band}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);
    }

    public function dropTables(): void
    {
        $this->dropTableIfExists('{{%bands_band}}');
    }

    public function dropForeignKeys(): void
    {
        if ($this->db->tableExists('{{%bands_band}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%bands_band}}', $this);
        }
    }
}