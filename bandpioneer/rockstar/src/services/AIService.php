<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\services;

use Craft;
use craft\gql\base\ObjectType;
use yii\base\Component;

use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\records\KeywordRecord as KeywordRecord;

use Symfony\Component\HttpClient\Psr18Client;
use Tectalic\OpenAi\Authentication;
use Tectalic\OpenAi\Client;
use Tectalic\OpenAi\Manager;
// use Tectalic\OpenAi\Models\ChatCompletions\CreateRequest;

/**
 * @author    Band Pioneer
 * @package   AIService
 */
class AIService extends Component
{
    const AI_MODEL = 'gpt-3.5-turbo'; // gpt-3.5-turbo text-davinci-003 text-davinci-002

    public function chatQuery($question)
    {
        $auth = new Authentication(getenv('OPENAI_API_KEY'));
        $httpClient = new Psr18Client();
        $client = new Client($httpClient, $auth, Manager::BASE_URI);

        $response = $client->chatCompletions()->create(
            new \Tectalic\OpenAi\Models\ChatCompletions\CreateRequest([
                'model' => self::AI_MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $question
                    ],
                ],
            ])
        )->toModel();

        return $response->choices[0]->message->content;

        // return 'This will be a Chat GPT response';
    }

    public function getKeywordList()
    {
        $keywords = KeywordRecord::find()->select(['path', 'title', 'description'])->where(['enabled' => 1])->asArray()->all();

        $groupedRecords = [];
        foreach ($keywords as $record)
        {
            $pathGroup = explode('/', $record['path'])[0];
            $groupedRecords[$pathGroup]['group'] = $pathGroup;
            $groupedRecords[$pathGroup]['keywords'][] = $record;
        }

        foreach ($groupedRecords as &$group)
        {
            uasort($group['keywords'], function ($a, $b)
            {
                return strcmp($a['path'], $b['path']);
            });
        }

        uasort($groupedRecords, function ($a, $b)
        {
            return strcmp($a['group'], $b['group']);
        });

        return array_values($groupedRecords);
    }

    public function getKeywordTitle($keywordPath)
    {
        $title = KeywordRecord::findOne(['path' => $keywordPath])->title ?? '';
        
        return $title;
    }

    public function getKeywordBody($keywordPath)
    {
        $body = KeywordRecord::findOne(['path' => $keywordPath])->body ?? '';
        
        return $body;
    }

    public function getKeywordDescription($keywordPath)
    {
        $body = KeywordRecord::findOne(['path' => $keywordPath])->description ?? '';
        
        return $body;
    }
    
    public function saveKeyword($keywordPath, $keywordTitle, $keywordBody, $keywordDescription)
    {
        if(strlen($keywordPath) > 0 && strlen($keywordTitle) > 0 && strlen($keywordBody) > 0 && !$keywordRecord = KeywordRecord::findOne(['path' => $keywordPath]))
        {
            $transaction = Craft::$app->getDb()->beginTransaction();

            try 
            {
                $now = Db::prepareDateForDb(DateTimeHelper::now());
                $keywordRecord = new KeywordRecord();

                $keywordRecord->dateCreated = $now;
                $keywordRecord->dateUpdated = $now;
                $keywordRecord->path = $keywordPath;
                $keywordRecord->title = $keywordTitle;
                $keywordRecord->body = $keywordBody;
                $keywordRecord->description = $keywordDescription;
                $keywordRecord->save();

                $transaction->commit();
            }
            catch (Throwable $e)
            {
                $transaction->rollBack();
                throw $e;
                return $e->message;
            }
        }

        return true;
    }
}
