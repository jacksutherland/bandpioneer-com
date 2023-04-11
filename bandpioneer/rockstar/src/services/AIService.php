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

use bandpioneer\rockstar\Rockstar;

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
    public function chatQuery($question)
    {
        $auth = new Authentication(getenv('OPENAI_API_KEY'));
        $httpClient = new Psr18Client();
        $client = new Client($httpClient, $auth, Manager::BASE_URI);

        $response = $client->chatCompletions()->create(
            new \Tectalic\OpenAi\Models\ChatCompletions\CreateRequest([
                'model' => 'gpt-3.5-turbo',
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
}
