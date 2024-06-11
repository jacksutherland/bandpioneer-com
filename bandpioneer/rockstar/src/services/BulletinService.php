<?php
/**
 * Band Pioneer Craft CMS 5.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2024 Band Pioneer
 */

namespace bandpioneer\rockstar\services;

use Craft;
use craft\gql\base\ObjectType;
use yii\base\Component;

use craft\helpers\ElementHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use craft\helpers\Db;

use craft\elements\Entry;
use craft\elements\User;
use craft\errors\ImageException;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\records\BulletinPostRecord as BulletinPostRecord;
use bandpioneer\rockstar\records\BulletinReplyRecord as BulletinReplyRecord;

/**
 * @author    Band Pioneer
 * @package   Rockstar
 */
class BulletinService extends Component
{
    const VIDEO_DOMAINS = [
        'youtube.com',
        'youtu.be'
    ];

    const AUDIO_DOMAINS = [
        'soundcloud.com',
        'spotify.com',
        'youtube.com',
        'bandcamp.com',
        'music.apple.com',
        'tidal.com',
        'audiomack.com',
        'mixcloud.com',
        'reverbnation.com',
        'deezer.com',
        'music.amazon.com'
    ];

    private function getDaysAgo($dateVal)
    {
        $date = new \DateTime($dateVal);
        $now = new \DateTime();
        $interval = $date->diff($now);

        if ($interval->y >= 1)
        {
            $daysAgo = $interval->y . ' years ago';
        }
        elseif ($interval->m >= 1)
        {
            $daysAgo = $interval->m . ' months ago';
        }
        elseif ($interval->d >= 7)
        {
            $weeks = floor($interval->d / 7);
            $daysAgo = $weeks . ' weeks ago';
        }
        elseif ($interval->d >= 1)
        {
            $daysAgo = $interval->d . ' days ago';
        }
        else
        {
            $hours = max($interval->h, 1);
            $daysAgo = $hours . ' hours ago';
        }

        return $daysAgo;
    }

    public function isValidVideoUrl($url):bool
    {
        if(!filter_var($url, FILTER_VALIDATE_URL))
        {
            return false;
        }

        $isValid = false;
        $parsed_url = parse_url($url);
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

        foreach (self::VIDEO_DOMAINS as $domain)
        {
            if (stripos($host, $domain) !== false)
            {
                $isValid = true;
            }
        }

        return $isValid;
    }

    public function isValidAudioUrl($url):bool
    {
        if(!filter_var($url, FILTER_VALIDATE_URL))
        {
            return false;
        }

        $isValid = false;
        $parsed_url = parse_url($url);
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

        foreach (self::AUDIO_DOMAINS as $domain)
        {
            if (stripos($host, $domain) !== false)
            {
                $isValid = true;
            }
        }

        return $isValid;
    }

    public function getBulletinBoardPosts($limit, $liveOnly)
    {
        $posts = [];
        $where = $liveOnly ? ['status' => 'live', 'enabled' => 1] : ['enabled' => 1];
        $select = ['slug', 'userId', 'title', 'type', 'genre', 'medium', 'location', 'audioUrl', 'videoUrl', 'description', 'details', 'replyCount', 'dateCreated'];
        $query = BulletinPostRecord::find()->where($where)->orderBy(['dateCreated' => SORT_DESC])->select($select);

        $postRecords = $limit == null ? $query->all() : $query->limit($limit)->all();

        foreach ($postRecords as $postRecord)
        {
            array_push($posts, [
                'slug' => $postRecord->slug,
                'title' => $postRecord->title,
                'type' => $postRecord->type,
                'genre' => $postRecord->genre,
                'medium' => $postRecord->medium,
                'location' => $postRecord->location,
                'audioUrl' => $postRecord->audioUrl,
                'videoUrl' => $postRecord->videoUrl,
                'description' => $postRecord->description,
                'details' => $postRecord->details,
                'userId' => $postRecord->userId,
                'replyCount' => $postRecord->replyCount == null ? 0 : $postRecord->replyCount,
                'daysAgo' => $this->getDaysAgo($postRecord->dateCreated)
            ]);
        }

        return $posts;
    }

    public function currentUserBulletinPostExists()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $postExists = BulletinPostRecord::find()->where(['userId' => $currentUser->id, 'enabled' => 1])->count() > 0;

        return $postExists;
    }

    public function getCurrentUserBulletinReplies()
    {
        $replies = [];
        $currentUser = Craft::$app->getUser()->getIdentity();
        $postRecord = BulletinPostRecord::findOne(['userId' => $currentUser->id, 'enabled' => 1]);

        if($postRecord)
        {
            $replyRecords = BulletinReplyRecord::find()->where(['bulletinPostId' => $postRecord->id, 'enabled' => 1])
                ->orderBy(['dateCreated' => SORT_DESC])
                ->select(['id', 'status', 'role', 'email', 'phone', 'message', 'audioUrl', 'videoUrl', 'dateCreated'])
                ->all();

            foreach ($replyRecords as $replyRecord)
            {
                array_push($replies, [
                    'id' => $replyRecord->id,
                    'status' => $replyRecord->status,
                    'role' => $replyRecord->role,
                    'email' => $replyRecord->email,
                    'phone' => $replyRecord->phone,
                    'message' => $replyRecord->message,
                    'audioUrl' => $replyRecord->audioUrl,
                    'videoUrl' => $replyRecord->videoUrl,
                    'daysAgo' => $this->getDaysAgo($replyRecord->dateCreated)
                ]);
            }
        }

        return $replies;
    }

    public function updateReplyStatus($replyId, $status)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $replyRecord = BulletinReplyRecord::findOne(['id' => $replyId]);

            if($replyRecord)
            {
                $replyRecord->status = $status;

                $replyRecord->save();

                $transaction->commit();
            }

        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function countCurrentUserBulletinReplies()
    {
        $replyCount = 0;
        $currentUser = Craft::$app->getUser()->getIdentity();
        $postRecord = BulletinPostRecord::findOne(['userId' => $currentUser->id, 'enabled' => 1]);

        if($postRecord)
        {
            $replyCount = BulletinReplyRecord::find()->where(['bulletinPostId' => $postRecord->id, 'enabled' => 1])->count();
        }

        return $replyCount;
    }

    public function getBulletinPostBySlug($slug)
    {
        $postRecord = BulletinPostRecord::findOne(['slug' => $slug, 'enabled' => 1]);

        if($postRecord)
        {
            return [
                'type' => $postRecord->type,
                'genre' => $postRecord->genre,
                'title' => $postRecord->title,
                'audioUrl' => $postRecord->audioUrl,
                'videoUrl' => $postRecord->videoUrl,
                'description' => $postRecord->description,
                'details' => $postRecord->details,
                'status' => $postRecord->status,
                'id' => $postRecord->id,
                'uid' => $postRecord->uid,
                'userId' => $postRecord->userId,
                'medium' => $postRecord->medium,
                'location' => $postRecord->location,
                'replyCount' => $postRecord->replyCount == null ? 0 : $postRecord->replyCount,
                'daysAgo' => $this->getDaysAgo($postRecord->dateCreated)
            ];
        }

        return null;
    }

    public function getCurrentUserBulletinPost()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $postRecord = BulletinPostRecord::findOne(['userId' => $currentUser->id, 'enabled' => 1]) ?? new BulletinPostRecord();

        if($postRecord->getIsNewRecord())
        {
            return [
                'type' => '',
                'genre' => '',
                'title' => '',
                'audioUrl' => '',
                'videoUrl' => '',
                'description' => '',
                'details' => '',
                'slug' => '',
                'medium' => '',
                'location' => '',
                'status' => 'pending',
                'replyCount' => 0,
            ];
        }
        else
        {
            return [
                'type' => $postRecord->type,
                'genre' => $postRecord->genre,
                'title' => $postRecord->title,
                'audioUrl' => $postRecord->audioUrl,
                'videoUrl' => $postRecord->videoUrl,
                'description' => $postRecord->description,
                'details' => $postRecord->details,
                'status' => $postRecord->status,
                'medium' => $postRecord->medium,
                'location' => $postRecord->location,
                'replyCount' => $postRecord->replyCount == null ? 0 : $postRecord->replyCount,
                'slug' => $postRecord->slug,
            ];
        }
    }

    public function saveBulletinPost($post)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();
            $postRecord = BulletinPostRecord::findOne(['userId' => $currentUser->id]) ?? new BulletinPostRecord();

            if($postRecord->getIsNewRecord())
            {
                $postRecord->dateCreated = $now;
                $postRecord->userId = $currentUser->id;
                $postRecord->slug = ElementHelper::generateSlug($post['title'], null, 'en');
                $postRecord->replyCount = 0;
                $postRecord->enabled = 1;
                $postRecord->status = 'new';
            }

            $postRecord->dateUpdated = $now;
            $postRecord->type = $post['type'];
            $postRecord->genre = $post['genre'];
            $postRecord->title = $post['title'];
            $postRecord->audioUrl = $post['audioUrl'];
            $postRecord->videoUrl = $post['videoUrl'];
            $postRecord->description = $post['description'];
            $postRecord->details = $post['details'];
            $postRecord->medium = $post['medium'];
            $postRecord->location = $post['location'];

            if($postRecord->status != 'new')
            {
                $postRecord->status = $post['status'];
            }
            
            $postRecord->save();

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return $postRecord;
    }

    public function getCurrentUserBulletinPostReply($postId)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if($currentUser)
        {
            $replyRecord = BulletinReplyRecord::findOne(['userId' => $currentUser->id, 'bulletinPostId' => $postId]) ?? new BulletinReplyRecord();
        }
        else
        {
            $replyRecord = new BulletinReplyRecord();
        }

        if($replyRecord->getIsNewRecord())
        {
            return [
                'role' => '',
                'email' => '',
                'audioUrl' => '',
                'videoUrl' => '',
                'message' => '',
                'status' => 'new'
            ];
        }
        else
        {
            return [
                'role' => $replyRecord->role,
                'email' => $replyRecord->email,
                'audioUrl' => $replyRecord->audioUrl,
                'videoUrl' => $replyRecord->videoUrl,
                'message' => $replyRecord->message,
                'status' => $replyRecord->status
            ];
        }
    }

    public function saveBulletinReply($reply)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();
            $postRecord = BulletinPostRecord::findOne(['uid' => $reply['uid']]);

            if($postRecord == null)
            {
                return false;
            }

            $replyRecord = BulletinReplyRecord::findOne(['userId' => $currentUser->id, 'bulletinPostId' => $postRecord->id]) ?? new BulletinReplyRecord();

            if($replyRecord->getIsNewRecord())
            {
                $replyRecord->dateCreated = $now;
                $replyRecord->userId = $currentUser->id;
                $replyRecord->bulletinPostId = $postRecord->id;
                $replyRecord->status = 'under-review';
                $postRecord->replyCount = $postRecord->replyCount + 1;
                $replyRecord->enabled = 1;
            }

            $replyRecord->dateUpdated = $now;
            $replyRecord->role = $reply['role'];
            $replyRecord->email = $reply['email'];
            $replyRecord->audioUrl = $reply['audioUrl'];
            $replyRecord->videoUrl = $reply['videoUrl'];
            $replyRecord->message = $reply['message'];
            
            $postRecord->save();
            $replyRecord->save();

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }
}