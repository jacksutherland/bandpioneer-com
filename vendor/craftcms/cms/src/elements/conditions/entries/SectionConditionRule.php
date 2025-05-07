<?php

namespace craft\elements\conditions\entries;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;

/**
 * Entry section condition rule.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 4.0.0
 */
class SectionConditionRule extends BaseMultiSelectConditionRule implements ElementConditionRuleInterface
{
    /**
     * @inheritdoc
     */
    protected bool $reloadOnOperatorChange = true;

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return Craft::t('app', 'Section');
    }

    /**
     * @inheritdoc
     */
    public function getExclusiveQueryParams(): array
    {
        return ['section', 'sectionId'];
    }

    /**
     * @inheritdoc
     */
    protected function operators(): array
    {
        return [
            ...parent::operators(),
            self::OPERATOR_NOT_EMPTY,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function options(): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        return ArrayHelper::map($sections, 'uid', 'name');
    }

    /**
     * @inheritdoc
     */
    public function modifyQuery(ElementQueryInterface $query): void
    {
        /** @var EntryQuery $query */
        if ($this->operator === self::OPERATOR_NOT_EMPTY) {
            $query->section('*');
        } else {
            $sections = Craft::$app->getEntries();
            $query->sectionId($this->paramValue(fn($uid) => $sections->getSectionByUid($uid)->id ?? null));
        }
    }

    /**
     * @inheritdoc
     */
    public function matchElement(ElementInterface $element): bool
    {
        /** @var Entry $element */
        if ($this->operator === self::OPERATOR_NOT_EMPTY) {
            return $element->getSection() !== null;
        }

        return $this->matchValue($element->getSection()?->uid);
    }
}
