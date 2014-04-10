<?php

require( 'workflows.php' ); // by David Ferguson
$wf = new Workflows();

$query    = explode(' ', trim(@$argv[1]));
$settings = $wf->read('settings');

$config['base_url']      = isset($settings->base_url) ? $settings->base_url : FALSE;
$config['api_key']       = isset($settings->api_key) ? $settings->api_key: FALSE;
$config['icon']          = "icon.ico";
$config['project_icon']  = "project.png";
$config['filter_icon']   = "filter.png";
$config['user_icon']     = "user.png";
$config['open_icon']     = "status-open.png";
$config['progress_icon'] = "status-in-progress.png";
$config['resolved_icon'] = "status-resolved.png";
$config['closed_icon']   = "status-closed.png";
$config['reopened_icon'] = "status-reopened.png";

$connected = (isset($config['base_url']) AND isset($config['api_key']));

if ( ! $connected)
{
	$wf->result(
		"needauth", 
		"", 
		"Credentials Needed", 
		"Set your Bugify URL and API key with `setbugifyurl` and `setbugifykey`", 
		$config['icon'],
		'no'
	);
}
else
{
	if ($query[0] == "filters")
	{
		list_filters();
	}
	else if ($query[0] == "projects")
	{
		list_projects();
	}
	else if ($query[0] == "users")
	{
		list_users();
	}
	else if ($query[0] == "project" AND isset($query[1]))
	{
		project_issues($query[1]);
	}
	else if ($query[0] == "user" AND isset($query[1]))
	{
		user($query[1]);
	}
	else if ($query[0] == "search" AND isset($query[1]))
	{
		search($query[1]);
	}
	else if ($query[0] == "filter" AND isset($query[1]))
	{
		filter($query[1]);
	}
	else
	{
		search($query[0]);
	}
}

function filter($filter_id)
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/filters/'.$filter_id.'/issues.json', array('limit' => 10));

	if (isset($response->issues))
	{
		$issues = $response->issues;
		
		foreach ($issues as $issue)
		{
			$wf->result(
				$issue->id, 
				$config['base_url'].'issues/'.$issue->id, 
				$issue->subject, 
				$issue->project->name, 
				get_icon_by_status($issue->state_name),
				'yes',
				null,
				'url'
			);
		}
	}
}

function list_filters()
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/filters.json', array('limit' => 9));

	if (isset($response->filters))
	{
		$filters = $response->filters;
		
		foreach ($filters as $filter)
		{
			$wf->result(
				$filter->id, 
				$config['base_url'].'issues/?filter_id='.$filter->id, 
				$filter->name, 
				"", 
				$config['filter_icon'],
				'no',
				'filter '.$filter->id
			);
		}
	}
}

function search($keyword)
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/issues/search.json', array('q' => $keyword));

	if (isset($response->issues))
	{
		$issues = $response->issues;
		
		foreach ($issues as $issue)
		{
			$wf->result(
				$issue->id, 
				$config['base_url'].'issues/'.$issue->id, 
				$issue->subject, 
				$issue->project->name, 
				get_icon_by_status($issue->state_name),
				'yes',
				null,
				'url'
			);
		}
	}

}

function list_projects()
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/projects.json', array());

	if (isset($response->projects))
	{
		$projects = $response->projects;
		
		foreach ($projects as $project)
		{
			$wf->result(
				$project->id, 
				$config['base_url'].'projects/'.$project->slug, 
				$project->name, 
				$project->issue_count." issues", 
				$config['project_icon'],
				'no',
				'project '.$project->slug
			);
		}
	}
}

function project_issues($project)
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/projects/'.$project.'/issues.json', array());

	if (isset($response->issues))
	{
		$issues = $response->issues;
		
		foreach ($issues as $issue)
		{
			$wf->result(
				$issue->id, 
				$config['base_url'].'issues/'.$issue->id, 
				$issue->subject, 
				$issue->project->name, 
				get_icon_by_status($issue->state_name),
				'yes',
				null,
				'url'
			);
		}
	}
}

function list_users()
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/users.json');

	if (isset($response->users))
	{
		$users = $response->users;
		
		foreach ($users as $user)
		{
			$wf->result(
				$user->id, 
				$config['base_url'].'users/'.$user->username.'/edit', 
				$user->name, 
				$user->email.' / '.$user->username. ' / '.$user->issue_count. ' issues', 
				$config['user_icon'],
				'no',
				'user '.$user->username
			);
		}
	}
}


function user($username)
{
	global $config, $wf;

	$response = bugify_api_call($config['api_key'], '/users/'.$username.'/issues.json');

	if (isset($response->issues))
	{
		$issues = $response->issues;
		
		foreach ($issues as $issue)
		{
			$wf->result(
				$issue->id, 
				$config['base_url'].'issues/'.$issue->id, 
				$issue->subject, 
				$issue->project->name, 
				get_icon_by_status($issue->state_name),
				'yes',
				null,
				'url'
			);
		}
	}
}

function get_icon_by_status($status)
{
	global $config;

	switch ($status) {
		case 'Open':
			return $config['open_icon'];		
		case 'Closed':
			return $config['closed_icon'];		
		case 'Resolved':
			return $config['resolved_icon'];
		case 'Reopened':
			return $config['reopened_icon'];
		case 'In Progress':
			return $config['progress_icon'];
	}
}

function bugify_api_call($api_key, $url, $data = array())
{
	global $config, $wf;

	$data['api_key'] = $api_key;

	$url       = $config['base_url'].'api'.$url;
	$curl_data = http_build_query($data);
	$response  = shell_exec("curl --silent --get --data '$curl_data' $url");

	return json_decode($response);	
}

echo $wf->toxml();

?>