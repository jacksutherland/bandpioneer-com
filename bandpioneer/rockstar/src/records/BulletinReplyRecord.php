<?php
namespace bandpioneer\rockstar\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\Site;
use craft\records\User;
use craft\records\Entry;
use yii\db\ActiveQueryInterface;

class BulletinReplyRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%rockstar_bulletin_replies}}';
    }

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    public function getUser(): ActiveQueryInterface
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }
}