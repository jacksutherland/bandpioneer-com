<?php

namespace bandpioneer\rockstar\queue\jobs;

use bandpioneer\rockstar\Rockstar;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\helpers\DateTimeHelper;
use craft\helpers\ElementHelper;
use craft\queue\BaseJob;

use DateInterval;
use Exception;

class AIQueries {
    const KEYWORDS = 1;
    const RELATED_ENTRY_TITLE = 2;
    const RELATED_ENTRY_DESC = 3;
    const RELATED_ENTRY_COPY = 4;
}

class PopulateEntries extends BaseJob
{
    public string $keyword;
    public int $category;

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return Craft::t('rockstar', "Creating related entry for {$this->keyword}");
    }

    public static function getAIQuery($aiQueryType, $params = [])
    {
        switch ($aiQueryType)
        {
            case AIQueries::RELATED_ENTRY_TITLE:
                return "Create a compelling title for a blog article. It should have the keyword '{$params['keyword']}' in it verbatim, and must be less than 55 characters.";

            case AIQueries::RELATED_ENTRY_DESC:
                return "Create a compelling description for a blog article titled '{$params['title']}'. It must be less than 160 characters.";

            case AIQueries::RELATED_ENTRY_COPY:
                return "Write long-form copy for an article about '{$params['title']}'. It must be 1000 to 2000 words, with interesting, easy to read language, and practical useful information only. Format it using html so that paragraphs are in <p> tags, and section titles in <h2> or <h3> tags, but do not include an <h1>. Start with an introduction paragraph with no title, and end with a conclusion titled 'Final Thoughts'.";

            default:
                return "";
        }
    }

    /**
     * @inheritDoc
     */
    public function execute($queue): void
    {
        $this->setProgress($queue, 0);

        $aiService = Rockstar::$plugin->getAIService();

        $slug = ElementHelper::generateSlug(ucwords(strtolower($this->keyword)), null, null);

        $entry = new Entry();
        $entry->siteId = 1; // TODO
        $entry->sectionId = 1; // TODO
        $entry->typeId = 1; // TODO
        $entry->authorId = 1; // TODO
        $entry->enabled = false;
        $entry->slug = $slug;
        $entry->expiryDate = null;
        $entry->setScenario(Element::SCENARIO_LIVE);

        // Set Post Date to 3 years ago today, so they're not at the front of the list
        $postDate = DateTimeHelper::currentUTCDateTime();
        $postDate->sub(new DateInterval('P3Y'));
        $entry->postDate = $postDate;

        $entry->dynamicSeo = true;
        $entry->tocDefault = 'expanded';
        $entry->categories = [$this->category];
        $entry->fullName = 'Band Pioneer';
        $entry->writerPicture = [7369];

        try {
            
            $titleQuery = self::getAIQuery(AIQueries::RELATED_ENTRY_TITLE, ['keyword' => $this->keyword]);
            $title = $aiService->chatQuery($titleQuery);
            $title = trim($title, '"');
            $title = str_replace(['!', '?', '.'], '', $title);
            $entry->title = $title;
            $entry->metaTitle = $title;

            $descQuery = self::getAIQuery(AIQueries::RELATED_ENTRY_DESC, ['title' => $title]);
            $desc = $aiService->chatQuery($descQuery);
            $desc = trim($desc, '"');
            $entry->shortDescription = $desc;
            $entry->metaDescription = $desc;

            $htmlQuery = self::getAIQuery(AIQueries::RELATED_ENTRY_COPY, ['title' => $title]);
            $html = $aiService->chatQuery($htmlQuery);
            $title = str_replace(['<h1>', '</h1>', $title], '', $title);
            $entry->longText = $html;

            if(!Craft::$app->getElements()->saveElement($entry, false))
            {
                throw new Exception("Unable to save related entry for '{$this->keyword}'");
            }

            $draft = Craft::$app->getDrafts()->createDraft($entry, 1, 1, 'Dynamic Draft');
            Craft::$app->getDrafts()->applyDraft($draft);
        }
        catch (Exception $e)
        {
            throw new Exception("Unable to create related entry for {$this->keyword}: {$e->getMessage()}");
        }

        $this->setProgress($queue, 1);
    }
}
