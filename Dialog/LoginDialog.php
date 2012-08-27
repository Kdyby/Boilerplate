<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook\Dialog;

use Nette;
use Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LoginDialog extends Facebook\Dialog\AbstractDialog
{

	const USER_EMAIL = 'email';
	const USER_PUBLISH_ACTIONS = "publish_actions";
	const USER_ABOUT_ME = "user_about_me";
	const USER_ACTIVITIES = "user_activities";
	const USER_BIRTHDAY = "user_birthday";
	const USER_EDUCATION_HISTORY = "user_education_history";
	const USER_EVENTS = "user_events";
	const USER_GAMES_ACTIVITY = "user_games_activity";
	const USER_GROUPS = "user_groups";
	const USER_HOMETOWN = "user_hometown";
	const USER_INTERESTS = "user_interests";
	const USER_LIKES = "user_likes";
	const USER_LOCATION = "user_location";
	const USER_NOTES = "user_notes";
	const USER_PHOTOS = "user_photos";
	const USER_QUESTIONS = "user_questions";
	const USER_RELATIONSHIP_DETAILS = "user_relationship_details";
	const USER_RELATIONSHIPS = "user_relationships";
	const USER_RELIGION_POLITICS = "user_religion_politics";
	const USER_STATUS = "user_status";
	const USER_SUBSCRIPTIONS = "user_subscriptions";
	const USER_VIDEOS = "user_videos";
	const USER_WEBSITE = "user_website";
	const USER_WORK_HISTORY = "user_work_history";

	const FRIENDS_ABOUT_ME = "friends_about_me";
	const FRIENDS_ACTIVITIES = "friends_activities";
	const FRIENDS_BIRTHDAY = "friends_birthday";
	const FRIENDS_EDUCATION_HISTORY = "friends_education_history";
	const FRIENDS_EVENTS = "friends_events";
	const FRIENDS_GAMES_ACTIVITY = "friends_games_activity";
	const FRIENDS_GROUPS = "friends_groups";
	const FRIENDS_HOMETOWN = "friends_hometown";
	const FRIENDS_INTERESTS = "friends_interests";
	const FRIENDS_LIKES = "friends_likes";
	const FRIENDS_LOCATION = "friends_location";
	const FRIENDS_NOTES = "friends_notes";
	const FRIENDS_PHOTOS = "friends_photos";
	const FRIENDS_QUESTIONS = "friends_questions";
	const FRIENDS_RELATIONSHIP_DETAILS = "friends_relationship_details";
	const FRIENDS_RELATIONSHIPS = "friends_relationships";
	const FRIENDS_RELIGION_POLITICS = "friends_religion_politics";
	const FRIENDS_STATUS = "friends_status";
	const FRIENDS_SUBSCRIPTIONS = "friends_subscriptions";
	const FRIENDS_VIDEOS = "friends_videos";
	const FRIENDS_WEBSITE = "friends_website";
	const FRIENDS_WORK_HISTORY = "friends_work_history";

	const EXTENDED_ADS_MANAGEMENT = "ads_management";
	const EXTENDED_CREATE_EVENT = "create_event";
	const EXTENDED_CREATE_NOTE = "create_note";
	const EXTENDED_EXPORT_STREAM = "export_stream";
	const EXTENDED_FRIENDS_ONLINE_PRESENCE = "friends_online_presence";
	const EXTENDED_MANAGE_FRIENDLISTS = "manage_friendlists";
	const EXTENDED_MANAGE_NOTIFICATIONS = "manage_notifications";
	const EXTENDED_MANAGE_PAGES = "manage_pages";
	const EXTENDED_OFFLINE_ACCESS = "offline_access";
	const EXTENDED_PHOTO_UPLOAD = "photo_upload";
	const EXTENDED_PUBLISH_CHECKINS = "publish_checkins";
	const EXTENDED_PUBLISH_STREAM = "publish_stream";
	const EXTENDED_READ_FRIENDLISTS = "read_friendlists";
	const EXTENDED_READ_INSIGHTS = "read_insights";
	const EXTENDED_READ_MAILBOX = "read_mailbox";
	const EXTENDED_READ_PAGE_MAILBOXES = "read_page_mailboxes";
	const EXTENDED_READ_REQUESTS = "read_requests";
	const EXTENDED_READ_STREAM = "read_stream";
	const EXTENDED_RSVP_EVENT = "rsvp_event";
	const EXTENDED_SHARE_ITEM = "share_item";
	const EXTENDED_SMS = "sms";
	const EXTENDED_STATUS_UPDATE = "status_update";
	const EXTENDED_USER_ONLINE_PRESENCE = "user_online_presence";
	const EXTENDED_VIDEO_UPLOAD = "video_upload";
	const EXTENDED_XMPP_LOGIN = "xmpp_login";



	/**
	 * @var string
	 */
	private $scope;



	/**
	 * @param string|array $scope
	 */
	public function setScope($scope)
	{
		$this->scope = implode(',', (array)$scope);
	}



	/**
	 * @return array
	 */
	public function getQueryParams()
	{
		// CSRF
		$this->facebook->session->establishCSRFTokenState();

		// basic params
		$params = array(
			'state' => $this->facebook->session->state,
			'client_id' => $this->facebook->config->appId,
			'redirect_uri' => (string)$this->currentUrl
		);

		// scope of rights
		if ($this->scope) {
			$params['scope'] = $this->scope;
		}

		return $params;
	}



	/**
	 * @param string $display
	 * @param bool $showError
	 * @return string
	 */
	public function getUrl($display = NULL, $showError = FALSE)
	{
		return (string)$this->facebook->config->createUrl(
			'www',
			'dialog/oauth',
			$this->getQueryParams()
		);
	}

}
