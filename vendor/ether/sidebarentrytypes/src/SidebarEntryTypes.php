<?php
/**
 * Entry Types plugin for Craft CMS 4.x
 *
 * Easily switch between entry types in entries section
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\sidebarentrytypes;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementSourcesEvent;
use craft\models\EntryType;
use yii\base\Event;

/**
 * Class SidebarEntryTypes
 *
 * @author    Ether Creative
 * @package   SidebarEntryTypes
 * @since     1.0.0
 *
 */
class SidebarEntryTypes extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var SidebarEntryTypes
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = "2.0.0";

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(Entry::class, Element::EVENT_REGISTER_SOURCES, function (
            RegisterElementSourcesEvent $event
        ) {
            $children = [];

            foreach ($event->sources as $i => $source) {
                if (!isset($source["data"])) {
                    continue;
                }

                /** @var EntryType[] */
                $entryTypes = Craft::$app->sections->getEntryTypesBySectionId(
                    $source["criteria"]["sectionId"]
                );

                if (count($entryTypes) < 2) {
                    continue;
                }

                $children[$i] = [];

                foreach ($entryTypes as $entryType) {
                    $section = $entryType->getSection();

                    $children[$i][] = [
                        "key" => "section:" . $entryType->uid,
                        "label" => $entryType->name,
                        "data" => [
                            "type" => "structure",
                            "handle" => $section->handle,
                        ],
                        "criteria" => [
                            "sectionId" => $entryType->sectionId,
                            "typeId" => $entryType->id,
                            "editable" => true,
                        ],
                        "defaultSort" => ["structure", "asc"],
                    ];
                }
            }

            foreach ($children as $key => $child) {
                $event->sources[$key]["nested"] = $child;
            }
        });
    }
}
