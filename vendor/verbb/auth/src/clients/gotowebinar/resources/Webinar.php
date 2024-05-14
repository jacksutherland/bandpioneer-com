<?php

namespace verbb\auth\clients\gotowebinar\resources;

use verbb\auth\clients\gotowebinar\resultset\PageResultSet;
use verbb\auth\clients\gotowebinar\resultset\SimpleResultSet;
use verbb\auth\clients\gotowebinar\helper\DateUtcHelper;
use DateTime;

class Webinar extends \DalPraS\OAuth2\Client\Resources\AuthenticatedResourceAbstract
{
    /**
     * Get webinars by Account.
     *
     * https://api.getgo.com/G2W/rest/v2/accounts/{accountKey}/webinars
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/getAllAccountWebinars
     */
    public function getWebinarsByAccount(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        $query      = [
            'fromTime' => DateUtcHelper::date2utc($from ?? new DateTime('-3 years')),
            'toTime'   => DateUtcHelper::date2utc($to ?? new DateTime('+3 years')),
            'page'     => $page,
            'size'     => $size
        ];
        $url = $this->getRequestUrl('/accounts/{accountKey}/webinars', [], $query);
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new PageResultSet($this->provider->getParsedResponse($request), 'webinars');
    }
    
    /**
     * Get webinars by Organizer.
     *
     * https://api.getgo.com/G2W/rest/v2/organizers/{organizerKey}/webinars
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/getWebinars
     */
    public function getWebinarsByOrganizer(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        $query      = [
            'fromTime' => DateUtcHelper::date2utc($from ?? new DateTime('-3 years')),
            'toTime'   => DateUtcHelper::date2utc($to ?? new DateTime('+3 years')),
            'page'     => $page,
            'size'     => $size
        ];
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars', [], $query);
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new PageResultSet($this->provider->getParsedResponse($request), 'webinars');
    }

    /**
     * Get webinars by Organizer.
     *
     * https://api.getgo.com/G2W/rest/v2/organizers/{organizerKey}/webinars
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/getWebinars
     */
    public function getWebinars(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        return $this->getWebinarsByOrganizer($from, $to, $page, $size);
    }

    /**
     * Get upcoming webinars.
     *
     * @deprecated use getWebinarsByOrganizer
     *
     * https://api.getgo.com/G2W/rest/v2/account/{accountKey}/webinars?page=0&size=20
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getWebinars
     */
    public function getUpcoming(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        return $this->getWebinarsByOrganizer($from ?? new DateTime('now'), $to ?? new DateTime('+3 years'), $page, $size);
    }

    /**
     * Get webinars in date range.
     *
     * @deprecated use getWebinarsByOrganizer
     *
     * https://api.getgo.com/G2W/rest/v2/account/{accountKey}/webinars?page=0&size=20
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getWebinars

     */
    public function getPast(?DateTime $from = null, ?DateTime $to = null, int $page = 0, int $size = 100): PageResultSet
    {
        return $this->getWebinarsByOrganizer($from ?? new DateTime('-3 years'), $to ?? new DateTime('now'), $page, $size);
    }

    /**
     * Get info for a single webinar by passing the webinar id or
     * in GotoWebinar's terms webinarKey.
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getWebinar
     *
     */
    public function getWebinar(string $webinarKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}', ['webinarKey' => $webinarKey]);
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Retrieves the meeting times for a webinar.
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/getWebinarMeetingTimes
     *
     */
    public function getWebinarMeetingTimes(string $webinarKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/meetingtimes', ['webinarKey' => $webinarKey]);
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Retrieves the audio/conferencing information for a specific webinar.
     *
     * https://api.getgo.com/G2W/rest/v2/organizers/{organizerKey}/webinars/{webinarKey}/audio
     * 
     * @link https://developer.goto.com/GoToWebinarV2#operation/getAudioInformation
     *
     */
    public function getAudioInformation(string $webinarKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/audio', ['webinarKey' => $webinarKey]);
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Returns webinars scheduled for the future for the specified organizer and webinars of other organizers 
     * where the specified organizer is a co-organizer.
     *
     * https://api.getgo.com/G2W/rest/v2/organizers/{organizerKey}/insessionWebinars
     * 
     * @link https://developer.goto.com/GoToWebinarV2#operation/getInSessionWebinars
     *
     */
    public function getInSessionWebinars(): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/insessionWebinars');
        $request  = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Create a new webinar.
     * Return the the WebinarKey.
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/createWebinar
     */
    public function createWebinar(array $body = []): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars');
        $request  = $this->provider->getAuthenticatedRequest('POST', $url, $this->accessToken, [
            'body' => json_encode($body)
        ]);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Update an existing webinar.
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/updateWebinar
     *
     */
    public function updateWebinar(string $webinarKey, array $body = []): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}', ['webinarKey' => $webinarKey]);
        $request  = $this->provider->getAuthenticatedRequest('PUT', $url, $this->accessToken, [
            'body' => json_encode($body)
        ]);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Delete a webinar.
     * https://api.getgo.com/G2W/rest/v2/organizers/{organizerKey}/webinars/{webinarKey}?sendCancellationEmails=false
     *
     * @link https://developer.goto.com/GoToWebinarV2#operation/cancelWebinar
     *
     * @param string $webinarKey
     * @param bool $sendEmail
     * @return SimpleResultSet
     */
    public function deleteWebinar(string $webinarKey, bool $sendEmail = false): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}', ['webinarKey' => $webinarKey], ['sendCancellationEmails' => $sendEmail]);
        $request  = $this->provider->getAuthenticatedRequest('DELETE', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }
}
