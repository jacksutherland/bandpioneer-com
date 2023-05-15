<?php
namespace bandpioneer\rockstar\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\Site;
use craft\records\User;
use yii\db\ActiveQueryInterface;

class KeywordRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%rockstar_keywords}}';
    }

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}