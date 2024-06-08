<?php

namespace bandpioneer\rockstar\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240606_232059_add_bulletin_posts_table migration.
 */
class m240606_232059_add_bulletin_posts_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->archiveTableIfExists('{{%rockstar_bulletin_posts}}');
        $this->createTable('{{%rockstar_bulletin_posts}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'slug' => $this->string(),
            'title' => $this->string(),
            'type' => $this->string(),
            'genre' => $this->string(),
            'medium' => $this->string(),
            'location' => $this->string(),
            'status' => $this->string(),
            'audioUrl' => $this->string(),
            'videoUrl' => $this->string(),
            'description' => $this->text(),
            'details' => $this->text(),
            'replyCount' => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ]);

        $this->archiveTableIfExists('{{%rockstar_bulletin_replies}}');
        $this->createTable('{{%rockstar_bulletin_replies}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'bulletinPostId' => $this->integer(),
            'status' => $this->string(),
            'role' => $this->string(),
            'email' => $this->string(),
            'phone' => $this->string(),
            'message' => $this->text(),
            'audioUrl' => $this->string(),
            'videoUrl' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ]);

        $this->createIndex(null, '{{%rockstar_bulletin_posts}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_bulletin_posts}}', 'userId', false);

        $this->createIndex(null, '{{%rockstar_bulletin_replies}}', 'id', false);
        $this->createIndex(null, '{{%rockstar_bulletin_replies}}', 'userId', false);

        // $this->addForeignKey(null, '{{%rockstar_bulletin_posts}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_bulletin_posts}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);

        // $this->addForeignKey(null, '{{%rockstar_bulletin_replies}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, '{{%rockstar_bulletin_replies}}', 'userId', '{{%users}}', 'id', 'SET NULL', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // if ($this->db->tableExists('{{%rockstar_bulletin_replies}}'))
        // {
        //     MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_bulletin_replies}}', $this);
        // }

        // $this->dropTableIfExists('{{%rockstar_bulletin_replies}}');

        // if ($this->db->tableExists('{{%rockstar_bulletin_posts}}'))
        // {
        //     MigrationHelper::dropAllForeignKeysOnTable('{{%rockstar_bulletin_posts}}', $this);
        // }

        // $this->dropTableIfExists('{{%rockstar_bulletin_posts}}');

        return true;
    }
}
