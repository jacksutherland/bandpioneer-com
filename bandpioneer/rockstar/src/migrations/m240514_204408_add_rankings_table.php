<?php

namespace bandpioneer\rockstar\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240514_204408_add_rankings_table migration.
 */
class m240514_204408_add_rankings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->archiveTableIfExists('{{%rockstar_rankings}}');
        $this->createTable('{{%rockstar_rankings}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'entryId' => $this->integer(),
            'data' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ]);

        $this->createIndex(null, '{{%rockstar_rankings}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_rankings}}', 'userId', false);
        $this->createIndex(null, '{{%rockstar_rankings}}', 'entryId', false);

        $this->addForeignKey(null, '{{%rockstar_rankings}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_rankings}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);
        $this->addForeignKey(null, '{{%rockstar_rankings}}', 'entryId', '{{%entries}}', 'id', 'SET NULL', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // echo "m240514_204408_add_rankings_table cannot be reverted.\n";

        if ($this->db->tableExists('{{%rockstar_rankings}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_rankings}}', $this);
        }

        $this->dropTableIfExists('{{%rockstar_rankings}}');

        return false;
    }
}
