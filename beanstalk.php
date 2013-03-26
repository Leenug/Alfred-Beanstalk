<?php

date_default_timezone_set('Europe/London');

include('workflows.php');
include('beanstalkapi.class.php');

class Beanstalk {

	var $workflow;
	var $repos;
	var $api;

	function __construct($creds) {
		
		$this->workflow = new Workflows();
		$this->api = new BeanstalkAPI(
			$creds['company'],
			$creds['username'],
			$creds['password']
		);
		
		$this->repos = self::cache();
	}

	function cache() {

		if(!file_exists('repos.json')){
			touch('repos.json');
		} else {
			if(filemtime('repos.json') < strtotime('- 2 hours')) {
				self::empty_cache();
			}
		}

		if(!$cache = $this->workflow->read('repos.json')) {
			$repos = $this->api->find_all_repositories(1, 1000);

			$cache = array();
			foreach($repos as $r) {
				$cache[$r->repository->id] = array(
					'title' => $r->repository->title,
					'name' => $r->repository->name,
					'color_label' => $r->repository->color_label,
					'type' => $r->repository->type,
					'repository_url' => $r->repository->repository_url
				);
			}

			$this->workflow->write(json_encode($cache), 'repos.json');
		}

		return $cache;
	}

	function empty_cache() {
		unlink('repos.json');
		self::cache();
	}

	function query($args) {
		
		$args = explode(' ', $args);

		$action = $args[0];

		switch($action){
			
			case 'list':
				self::repo_list();
			break;

			case 'search':
				self::repo_search($args);
			break;

			case 'log':
				self::changeset($args);
			break;

			case 'create':
				self::repo_create($args);
			break;

			case 'recache' :
				self::empty_cache();
			break;
		}
	}

	function repo_list() {

		foreach($this->repos as $id=>$repo) {

			$this->workflow->result(
				$id, 
				'git clone ' . $repo->repository_url . ' -o Beanstalk', 
				$repo->name, 
				$repo->repository_url, 
				$repo->color_label .'.png'
			);
		}
	
		echo $this->workflow->toxml();
	}

	function repo_search($args) {

		$search_phrase = $args[1];

		$found = 0;
		foreach($this->repos as $id=>$repo) {

			$pos = strpos(strtolower($repo->name), strtolower($search_phrase));

			if($pos !== false) {
				$this->workflow->result(
					$id, 
					'git clone ' . $repo->repository_url . ' -o Beanstalk', 
					$repo->name, 
					$repo->repository_url, 
					$repo->color_label .'.png'
				);
				$found++;		
			}
		}

		if($found == 0) {
			$this->workflow->result(
				'1', 
				'', 
				'Sorry no repos matching "'.$search_phrase.'" were found.', 
				'', 
				'icon.png', 
				''
			);
		}

		echo $this->workflow->toxml();
	}

	function changeset() {

		$changes = $this->api->find_all_changesets();		
		
		foreach($changes as $change) {

			$repo = $this->repos->{$change->revision_cache->repository_id};


			if($repo->type == 'SubversionRepository') {
				
				$this->workflow->result(
					$change->revision_cache->id, 
					$change->revision_cache->revision, 
					$repo->name .': ' . $change->revision_cache->message ?: 'No Message :(', 
					'Rev#' . $change->revision_cache->revision . ' - ' . $change->revision_cache->author .' - '. self::ago($change->revision_cache->time),
					'icon.png'
				);

			} else {

				$this->workflow->result(
					$change->revision_cache->id, 
					$change->revision_cache->hash_id, 
					$repo->name .': ' . $change->revision_cache->message ?: 'No Message :(', 
					substr($change->revision_cache->hash_id, -8) . ' - ' . $change->revision_cache->author .' - '. self::ago($change->revision_cache->time),
					'icon.png'
				);

			}
		}


		echo $this->workflow->toxml();
	}

	private function ago($time)
	{
	   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	   $lengths = array("60","60","24","7","4.35","12","10");

	   $now = time();

	       $difference     = $now - strtotime($time);
	       $tense         = "ago";

	   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	       $difference /= $lengths[$j];
	   }

	   $difference = round($difference);

	   if($difference != 1) {
	       $periods[$j].= "s";
	   }

	   return "$difference $periods[$j] ago ";
	}

}