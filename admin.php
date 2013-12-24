<?php

$week_start = get_option('start_of_week'); // Get Wordpress option
$now = current_time('mysql', 1); // in UTC

if ($week_start == 1) $week_mode = 1;
else $week_mode = 0;


function get_delta_visits($delta) {
	
	if ($delta > 100) {
		$class = 'rises';
		$percent = abs($delta - 100);
		$image = '<img src="'. plugins_url('wp-power-stats/images/delta_arrow_up.png') .'" title="'.$percent.'">';
	} else if ($delta == 0) {
		$class = 'no-change';
		$image = '';
		$percent = 0;
	} else {
		$class = 'falls';
		$percent = abs($delta - 100);
		$image = '<img src="'. plugins_url('wp-power-stats/images/delta_arrow_down.png') .'" title="'.$percent.'">';
	}

	echo '<td class="versus-last '.$class.'"><span>'. $image .' '. $percent .'<span>%</span></span></td>';
}


$today_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE DATE(`date`) = DATE('". $now ."')", ARRAY_N);
$this_week_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE WEEK(`date`, $week_mode) = WEEK('". $now ."', $week_mode)", ARRAY_N);
$this_month_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE MONTH(`date`) = MONTH('". $now ."')", ARRAY_N);

$today_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE DATE(`date`) = DATE('". $now ."')", ARRAY_N);
$this_week_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE WEEK(`date`, $week_mode) = WEEK('". $now ."', $week_mode)", ARRAY_N);
$this_month_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE MONTH(`date`) = MONTH('". $now ."')", ARRAY_N);

$yesterday_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE DATE(`date`) = DATE(DATE_SUB('". $now ."', INTERVAL 1 DAY))", ARRAY_N);
$last_week_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE WEEK(`date`, $week_mode) = WEEK(DATE_SUB('". $now ."', INTERVAL 1 WEEK), $week_mode)", ARRAY_N);
$last_month_visits = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE MONTH(`date`) = MONTH(DATE_SUB('". $now ."', INTERVAL 1 MONTH))", ARRAY_N);

$yesterday_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE DATE(`date`) = DATE(DATE_SUB('". $now ."', INTERVAL 1 DAY))", ARRAY_N);
$last_week_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE WEEK(`date`, $week_mode) = WEEK(DATE_SUB('". $now ."', INTERVAL 1 WEEK), $week_mode)", ARRAY_N);
$last_month_pageviews = $wpdb->get_row("SELECT SUM(`hits`) FROM `{$wpdb->prefix}power_stats_pageviews` WHERE MONTH(`date`) = MONTH(DATE_SUB('". $now ."', INTERVAL 1 MONTH))", ARRAY_N);

$day_delta = ($yesterday_visits[0] == 0) ? 0 : round($today_visits[0] / $yesterday_visits[0], 2) * 100;
$week_delta = ($last_week_visits[0] == 0) ? 0 : round($this_week_visits[0] / $last_week_visits[0], 2) * 100;
$month_delta = ($last_month_visits[0] == 0) ? 0 : round($this_month_visits[0] / $last_month_visits[0], 2) * 100;

$day_pageviews_delta = ($yesterday_pageviews[0] == 0) ? 0 : round($today_pageviews[0] / $yesterday_pageviews[0], 2) * 100;
$week_pageviews_delta = ($last_week_pageviews[0] == 0) ? 0 : round($this_week_pageviews[0] / $last_week_pageviews[0], 2) * 100;
$month_pageviews_delta = ($last_month_pageviews[0] == 0) ? 0 : round($this_month_pageviews[0] / $last_month_pageviews[0], 2) * 100;

$total_visits = $wpdb->get_row("SELECT COUNT(`id`) FROM `{$wpdb->prefix}power_stats_visits`", ARRAY_N);
$desktop_hits = $wpdb->get_row("SELECT COUNT(`id`) FROM `{$wpdb->prefix}power_stats_visits` WHERE `device`='desktop'", ARRAY_N);
$tablet_hits = $wpdb->get_row("SELECT COUNT(`id`) FROM `{$wpdb->prefix}power_stats_visits` WHERE `device`='tablet'", ARRAY_N);
$mobile_hits = $wpdb->get_row("SELECT COUNT(`id`) FROM `{$wpdb->prefix}power_stats_visits` WHERE `device`='mobile'", ARRAY_N);

$desktop = round($desktop_hits[0] / $total_visits[0] * 100);
$tablet = round($tablet_hits[0] / $total_visits[0] * 100);
$mobile = round($mobile_hits[0] / $total_visits[0] * 100);

$visits = $wpdb->get_results("SELECT `v`.`date`, COUNT(`v`.`id`) AS `hits`, `p`.`hits` AS `pageviews` FROM `{$wpdb->prefix}power_stats_visits` AS `v` JOIN `{$wpdb->prefix}power_stats_pageviews` AS `p` ON (`v`.`date` = `p`.`date`) GROUP BY `date` ORDER BY `v`.`date` DESC LIMIT 11", ARRAY_A);

$search_engine_referers = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE `is_search_engine` = '1'", ARRAY_N);
$non_empty_referers = $wpdb->get_row("SELECT COUNT(id) FROM `{$wpdb->prefix}power_stats_visits` WHERE `referer` != '' AND `is_search_engine` != '1'", ARRAY_N);

$search_engines = round($search_engine_referers[0] / $total_visits[0] * 100);
$links = round($non_empty_referers[0] / $total_visits[0] * 100);
$direct = 100 - $search_engines - $links;

$browser_data = $wpdb->get_results("SELECT `browser` AS `name`, `count` AS `hits` FROM `{$wpdb->prefix}power_stats_browsers` ORDER BY `count` DESC LIMIT 3", ARRAY_A);
$browser_total_hits = $wpdb->get_row("SELECT SUM(`count`) AS `total_hits` FROM `{$wpdb->prefix}power_stats_browsers`", ARRAY_N);

$browsers = '';
$translations = array("internet explorer" => "ie");

foreach ($browser_data as $browser) {
	$browsers .= '<td class="browser browser-'.strtr(strtolower($browser['name']), $translations).'">'. round($browser['hits'] / $browser_total_hits[0] * 100) .'<span>%</span><div>'.$browser['name'].'</div></td>';
}


$os_data = $wpdb->get_results("SELECT `os` AS `name`, `count` AS `hits` FROM `{$wpdb->prefix}power_stats_os` ORDER BY `count` DESC LIMIT 3", ARRAY_A);
$os_total_hits = $wpdb->get_row("SELECT SUM(`count`) AS `total_hits` FROM `{$wpdb->prefix}power_stats_os`", ARRAY_N);

$oss = '';

foreach ($os_data as $os) {
	$oss .= '<td class="browser os-'.strtolower($os['name']).'">'. round($os['hits'] / $os_total_hits[0] * 100) .'<span>%</span><div>'.$os['name'].'</div></td>';
}

$top_posts = $wpdb->get_results("SELECT `s`.`post_id`, `wp`.`post_title` AS `title`, SUM(`s`.`hits`) AS `hits` FROM `{$wpdb->prefix}power_stats_posts` AS `s` LEFT JOIN `w5cp_posts` AS `wp` ON (`s`.`post_id` = `wp`.`id`) GROUP BY `s`.`post_id` ORDER BY `hits` DESC LIMIT 10", ARRAY_A);

$top_links = $wpdb->get_results("SELECT `referer`, `count` FROM `{$wpdb->prefix}power_stats_referers` ORDER BY `count` DESC LIMIT 10", ARRAY_A);

$top_searches = $wpdb->get_results("SELECT `terms`, `count` FROM `{$wpdb->prefix}power_stats_searches` ORDER BY `count` DESC LIMIT 10", ARRAY_A);

$country_data = $wpdb->get_results("SELECT `country` AS `name`, COUNT(`id`) AS `count` FROM `{$wpdb->prefix}power_stats_visits` GROUP BY `country` ORDER BY `count` DESC", ARRAY_A);