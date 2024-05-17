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
        $this->archiveTableIfExists('{{%rockstar_ranking_items}}');
        $this->createTable('{{%rockstar_ranking_items}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'entryId' => $this->integer(),
            'key' => $this->string(),
            'value' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'liked' => $this->boolean(),
            'sort' => $this->integer(),
            'uid' => $this->uid(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ]);

        // $this->createIndex(null, '{{%rockstar_ranking_items}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_ranking_items}}', 'userId', false);
        $this->createIndex(null, '{{%rockstar_ranking_items}}', 'entryId', false);

        // $this->addForeignKey(null, '{{%rockstar_ranking_items}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_ranking_items}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);
        $this->addForeignKey(null, '{{%rockstar_ranking_items}}', 'entryId', '{{%entries}}', 'id', 'SET NULL', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->db->tableExists('{{%rockstar_ranking_items}}'))
        {
            MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_ranking_items}}', $this);
        }

        $this->dropTableIfExists('{{%rockstar_ranking_items}}');

        return true;
    }
}
