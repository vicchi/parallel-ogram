<?php

	loadlib("instagram_api");
	loadlib("instagram_photos_import");

	#################################################################

	# http://instagram.com/developer/endpoints/users/#get_users_liked_feed

	function instagram_likes_import_for_user($user, $more=array()){

		$defaults = array(
			'force' => 0,
		);

		$more = array_merge($defaults, $more);

		$insta_user = instagram_users_get_by_user_id($user['id']);

		$method = 'users/self/media/liked';

		$args = array(
			'access_token' => $insta_user['oauth_token'],
		);

		# TO DO: max id stuff

		$count_imported = 0;
		$count_skipped = 0;
		$count_failed = 0;

		$ok = 1;

		while ($ok){

			$rsp = instagram_api_call($method, $args);

			if (! $rsp['ok']){
				return $rsp;
			}

			$pg = $rsp['rsp']['pagination'];

			$to_fetch = array();

			foreach ($rsp['rsp']['data'] as $d){

				$_more = array('force' => 1);
				$import_rsp = instagram_photos_import_api_photo($d, $_more);

				echo "{$import_rsp['ok']} : {$import_rsp['photo']['id']}, {$import_rsp['fullpath']}\n";
				# TO DO: add to InstagramLikes table

				$count_imported ++;
			}

			$ok = ($pg['next_max_like_id']) ? 1 : 0;

			$args['max_like_id'] = $pg['next_max_like_id'];
		}

		$rsp = array(
			'count_imported' => $count_imported,
			'count_skipped' => $count_skipped,
			'count_failed' => $count_failed,
		);

		return ($count_failed) ? not_okay($rsp) : okay($rsp);
	}

	#################################################################
?>
