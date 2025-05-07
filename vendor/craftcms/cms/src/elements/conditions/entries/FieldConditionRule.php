<?php

namespace craft\elements\conditions\entries;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementContainerFieldInterface;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use Illuminate\Support\Collection;

/**
 * Field condition rule.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 5.6.0
 */
class FieldConditionRule extends BaseMultiSelectConditionRule implements ElementConditionRuleInterface
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
        return Craft::t('app', 'Field');
    }

    /**
     * @inheritdoc
     */
    public function getExclusiveQueryParams(): array
    {
        return ['field', 'fieldId'];
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
        return $this->nestedEntryFields()
            ->keyBy(fn(ElementContainerFieldInterface $field) => $field->uid)
            ->map(fn(ElementContainerFieldInterface $field) => $field->getUiLabel())
            ->all();
    }

    /**
     * @inheritdoc
     */
    public function modifyQuery(ElementQueryInterface $query): void
    {
        /** @var EntryQuery $query */
        if ($this->operator === self::OPERATOR_NOT_EMPTY) {
            $query->field($this->nestedEntryFields()->all());
        } else {
            $fieldsService = Craft::$app->getFields();
            $query->fieldId($this->paramValue(fn($uid) => $fieldsService->getFieldByUid($uid)->id ?? null));
        }
    }

    /**
     * @inheritdoc
     */
    public function matchElement(ElementInterface $element): bool
    {
        /** @var Entry $element */
        if ($this->operator === self::OPERATOR_NOT_EMPTY) {
            return $element->getField() !== null;
        }

        return $this->matchValue($element->getField()?->uid);
    }

    /**
     * @return Collection<ElementContainerFieldInterface>
     */
    private function nestedEntryFields(): Collection
    {
        $fieldsService = Craft::$app->getFields();
        return Collection::make($fieldsService->getNestedEntryFieldTypes())
            ->map(fn(string $class) => $fieldsService->getFieldsByType($class))
            ->flatten(1);
    }
}
