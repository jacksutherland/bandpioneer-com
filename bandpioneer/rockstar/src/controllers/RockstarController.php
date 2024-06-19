<?php
/**
 * Band Pioneer Craft CMS 5.x
 *
 * Band Pioneer plugin for custom website function.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2024 Band Pioneer
 */

namespace bandpioneer\rockstar\controllers;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\services\RockstarService;

use Craft;
use Exception;

use craft\helpers\Json;
use craft\helpers\UrlHelper;

use craft\web\Controller;

use yii\log\Logger;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * @author    Band Pioneer
 * @package   Bands
 * @since     1.0.0
 */
class RockstarController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    public function actionSaveRankingOrder(): string
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $entryId = trim($request->getParam('eid'));
        $data = trim($request->getParam('data'));

        /*** VALIDATION ***/

        if (is_numeric($entryId) && is_string($data) && !empty($data))
        {
            /*** SAVE ***/

            $service->saveCurrentUserRankingOrder($entryId, $data);

            return "success";
        }

        return "error";
    }

    public function actionRankingLikeIt(): string
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $entryId = trim($request->getParam('eid'));
        $key = trim($request->getParam('key'));
        $val = trim($request->getParam('val'));
        $liked = trim($request->getParam('liked'));

        /*** VALIDATION ***/

        if (is_numeric($entryId) && is_string($key) && !empty($key) && is_string($liked) && !empty($liked))
        {
            /*** SAVE ***/

            $service->likeCurrentUserKey($entryId, $key, $val, $liked);

            return "success";
        }

        return "error";
    }

    public function actionBulletinPost():post
    {
        $template = 'community/bulletin-post';
        $replySentMessage = 'Your request has been sent!';
        $request = Craft::$app->getRequest();
        $bulletinService = Rockstar::$plugin->getBulletinService();
        $segments = $request->getSegments();
        $slug = end($segments);
        $post = $bulletinService->getBulletinPostBySlug($slug);
        $reply = $bulletinService->getCurrentUserBulletinPostReply($post['id']);

        // GET Request

        if (!$this->request->getIsPost())
        {
            if ($post['status'] == 'new')
            {
                throw new NotFoundHttpException('Page not found');
            }  

            if ($reply['status'] != 'new')
            {
                Craft::$app->getSession()->setNotice($replySentMessage);
            }

            return $this->renderTemplate($template, [
                'post' => $post,
                'slug' => $slug,
                'reply' => $reply
            ]);
        }
        
        // POST Request

        $service = Rockstar::$plugin->getService();
        $isValid = true;
        $reply = [
            'role' => trim($request->getParam('role')),
            'email' => trim($request->getParam('email')),
            'audioUrl' => trim($request->getParam('audioUrl')),
            'videoUrl' => trim($request->getParam('videoUrl')),
            'message' => trim($request->getParam('message')),
            'uid' => trim($request->getParam('uid')),
            'status' => $reply['status']
        ];

        if(empty($reply['role']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("An instrument or role is required.");
        }
        elseif(empty($reply['email']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("An email address is required.");
        }
        elseif(!filter_var($reply['email'], FILTER_VALIDATE_EMAIL))
        {
            $isValid = false;
            $reply['email'] = "";
            Craft::$app->getSession()->setError("Email address is invalid.");
        }
        elseif(empty($reply['message']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("A message is required.");
        }
        elseif(!empty($reply['audioUrl']) && !$bulletinService->isValidAudioUrl($reply['audioUrl']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Not a valid Audio URL.");
        }
        elseif(!empty($reply['videoUrl']) && !$bulletinService->isValidVideoUrl($reply['videoUrl']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Not a valid YouTube URL.");
        }
        elseif(!$service->validateText([$reply['role'], $reply['message']], 'Your application was not saved.'))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Invalid role. Application not saved.");
        }

        if($isValid)
        {
            if($bulletinService->saveBulletinReply($reply))
            {
                Craft::$app->getSession()->setNotice($replySentMessage);
            }
            else
            {
                Craft::$app->getSession()->setError("Server error. Your application was not saved.");
            }
        }

        return $this->renderTemplate($template, [
            'post' => $post,
            'slug' => $slug,
            'reply' => $reply
        ]);
    }

    public function actionBulletinReplyStatus()
    {
        $request = Craft::$app->getRequest();
        $bulletinService = Rockstar::$plugin->getBulletinService();
        $replyId = trim($request->getParam('reply'));
        $status = trim($request->getParam('status'));
        $bulletinService->updateReplyStatus($replyId, $status);
        return 'success';
    }

    public function actionDeleteBulletinPost(): ?Response
    {
        $request = Craft::$app->getRequest();
        $bulletinService = Rockstar::$plugin->getBulletinService();
        $postId = trim($request->getParam('post'));

        if (!$this->request->getIsPost())
        {
            throw new NotFoundHttpException('Page not found');
        }

        $bulletinService->deleteBulletinPost($postId);

        Craft::$app->getSession()->setNotice("Your post has been deleted.");

        // return $this->redirect('community/create-bulletin-post?post=' . $postId . '&status=deleted');
        return $this->redirect('account');
    }

    public function actionCreateBulletinPost():post
    {
        $template = 'community/create-bulletin-post';
        $request = Craft::$app->getRequest();
        $bulletinService = Rockstar::$plugin->getBulletinService();
        $postId = trim($request->getParam('post'));

        // GET Request

        if (!$this->request->getIsPost())
        {
            if (empty($postId))
            {
                $post = $bulletinService->getEmptyBulletinPost();
            }
            else
            {
                $post = $bulletinService->getCurrentUserPostById($postId);
            }

            if(!$post)
            {
                throw new NotFoundHttpException('Page not found');
            }

            if($post['status'] == 'new')
            {
                Craft::$app->getSession()->setNotice("Your post is pending admin approval.");
            }

            return $this->renderTemplate($template, [
                'post' => $post,
                'isNew' => empty($post['slug'])
            ]);
        }

        // POST Request

        $isValid = true;
        $service = Rockstar::$plugin->getService();

        $post = [
            'id' => trim($request->getParam('post')),
            'type' => trim($request->getParam('type')),
            'genre' => trim($request->getParam('genre')),
            'title' => trim($request->getParam('title')),
            'audioUrl' => trim($request->getParam('audioUrl')),
            'videoUrl' => trim($request->getParam('videoUrl')),
            'description' => trim($request->getParam('description')),
            'details' => trim($request->getParam('details')),
            'status' => trim($request->getParam('status')),
            'medium' => trim($request->getParam('medium')),
            'location' => trim($request->getParam('location'))
            // 'slug' => $post['slug']
        ];

        /*** VALIDATION ***/

        if(empty($post['type']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Post type is required.");
        }
        elseif(empty($post['genre']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Genre is required.");
        }
        elseif(empty($post['medium']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Location Type is required.");
        }
        elseif(empty($post['title']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Post title is required.");
        }
        elseif(!empty($post['audioUrl']) && !$bulletinService->isValidAudioUrl($post['audioUrl']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Not a valid Audio URL.");
        }
        elseif(!empty($post['videoUrl']) && !$bulletinService->isValidVideoUrl($post['videoUrl']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Not a valid YouTube URL.");
        }
        elseif(empty($post['description']))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("A description is required.");
        }
        elseif(!$service->validateText([$post['type'], $post['genre'], $post['title'], $post['details'], $post['description']], 'Bulletin post not saved.'))
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Invalid text. Bulletin post not saved.");
        }

        if($isValid)
        {
            $savedPost = $bulletinService->saveBulletinPost($postId, $post);

            $post['id'] = $savedPost->id;
            $post['slug'] = $savedPost->slug;
            $post['status'] = $savedPost->status;

            if($savedPost->status == 'new')
            {
                Craft::$app->getSession()->setNotice("Your post has been saved, and is pending admin approval.");
            }
            elseif($savedPost->status == 'pending')
            {
                Craft::$app->getSession()->setNotice("Your post has been saved.<br>Change the status to 'Live' when you're ready to show it on the classifieds board.");
            }
            else
            {
                Craft::$app->getSession()->setNotice("Your post has been saved, and is live on the classifieds board!");
            }
        }

        return $this->renderTemplate($template, [
            'post' => $post,
            'isNew' => false
        ]);
    }
}