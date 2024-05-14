<?php

namespace verbb\auth\clients\gotowebinar\resources;

use verbb\auth\clients\gotowebinar\resultset\PageResultSet;
use verbb\auth\clients\gotowebinar\resultset\SimpleResultSet;
use verbb\auth\clients\gotowebinar\helper\DateUtcHelper;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use DateTime;

class Session extends AuthenticatedResourceAbstract
{
    /**
     * Get organizer sessions
     *
     * @param DateTime|null $from
     * @param DateTime|null $to
     * @param int $page
     * @param int $size
     * @return PageResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getOrganizerSessions
     */
    public function getSessions(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        $query = [
            'fromTime' => DateUtcHelper::date2utc($from ?? new DateTime('-3 years')),
            'toTime'   => DateUtcHelper::date2utc($to ?? new DateTime('+3 years')),
            'page'     => $page,
            'size'     => $size
        ];

        $url = $this->getRequestUrl('/organizers/{organizerKey}/sessions', [], $query);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new PageResultSet($this->provider->getParsedResponse($request), 'sessionInfoResources');
    }

    /**
     * Get webinar sessions
     *
     * @param string $webinarKey
     * @param int $page
     * @param int $size
     * @return PageResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAllSessions
     */
    public function getWebinarSessions(string $webinarKey, int $page = 0, int $size = 100): PageResultSet
    {
        $query = [
            'page' => $page,
            'size' => $size
        ];
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions', ['webinarKey' => $webinarKey], $query);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new PageResultSet($this->provider->getParsedResponse($request), 'sessionInfoResources');
    }

    /**
     * Get webinar session
     *
     * @param string $webinarKey
     * @param string $sessionKey
     * @return SimpleResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getWebinarSession
     */
    public function getWebinarSession(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get session performance
     *
     * @param string $webinarKey
     * @param string $sessionKey
     * @return SimpleResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getPerformance
     */
    public function getSessionPerformance(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/performance', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get session polls
     *
     * @param string $webinarKey
     * @param string $sessionKey
     * @return SimpleResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getPolls
     */
    public function getSessionPolls(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/polls', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get session questions
     *
     * @param string $webinarKey
     * @param string $sessionKey
     * @return SimpleResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getQuestions
     */
    public function getSessionQuestions(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/questions', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get session surveys
     *
     * @param string $webinarKey
     * @param string $sessionKey
     * @return SimpleResultSet
     * @throws IdentityProviderException
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getSurveys
     */
    public function getSessionSurveys(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/surveys', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }
}
