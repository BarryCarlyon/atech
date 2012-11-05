<?php

require_once('atechapi.php');

class Codebase extends ATechAPI {
	var $url = 'https://api3.codebasehq.com/';

	public function __construct($apiuser, $apikey) {
		list($domain, $user) = explode('/', $apiuser);
		$this->apiuser = $user;
		$this->apikey = $apikey;
		$this->hostname = $domain;
	}

	/**
	Activity
	*/

	/**
	* Get General Activity/Dashboard Feed
	* @return an array of events
	*/
	public function activity() {
		return $this->get('activity', 'activity');
	}

	/**
	* Get Project Activity/Dashboard Feed
	* @param permalink/shortname of a project
	* @return an array of events
	*/
	public function projectActivity($permalink) {
		return $this->get($permalink . '/activity', 'activity');
	}

	/**
	Projects
	*/

	/**
	* Get Projects
	* @return an array of projects
	*/
	public function projects() {
		return $this->get('projects', 'project');
	}

	/**
	* Get a project
	* @param permalint/shortname of a project
	* @return a single project
	*/
	public function project($permalink) {
		return $this->get($permalink);
	}

	/**
	* Create a Project
	* @param project name, helps to form the short name too, which is returned
	* @return a project object
	*/
	public function createProject($project_name) {
		$xml = '<project><name>' . $project_name . '</name></project>';
		return $this->post('create_project', $xml, 'project');
	}

	/**
	* Delete a project
	* @param permalink/shortname
	* @return TRUE on success, excpetion on error
	*/
	public function deleteProject($permalink) {
		return $this->delete($permalink);
	}

	/**
	* Project Groups
	* @return array of group objects (containg and dynamic ID (can change) and the name)
	*/
	public function projectGroups() {
		return $this->get('project_groups', 'project-group');
	}

	/**
	* Project User Assignments
	* @param permalink to project
	* @param array of user ID's to assign to project (overrides existing), leave blank to get current assignments
	* @return user objects
	*/
	public function projectAssignments($permalink, $users = FALSE) {
		if ($users === FALSE) {
			return $this->get($permalink . '/assignments', 'user');
		}
		$xml = '<users>';
		foreach ($users as $user) {
			$xml .= '<user><id>' . $user . '</id></user>';
		}
		$xml .= '</users>';
		return $this->post($permalink . '/assignments', $xml, 'user');
	}

	/**
	Repositories
	*/

	/**
	* Get Project Repositories
	* @param permalink/shortname of project
	* @return an array of repository objects
	*/
	public function projectRepositories($permalink) {
		return $this->get($permalink . '/repositories', 'repository');
	}

	/**
	* Create a repository for a project
	* @param project shortname/permalink
	* @param repo name to use
	* @param repo type, git/svn/hg/bzr
	* @return a single repositrory object (specifc keys to the repo_type)
	*/
	public function createRepository($permalink, $repo_name, $repo_type = 'git') {
		$xml = '<repository><name>' . $repo_name . '</name><scm>' . $repo_type . '</scm></repository>';
		return $this->post($permalink . '/repositories', $xml, 'repository');
	}

	/**
	* Return a specific project repoistory
	* @param project shortname/permalink
	* @param repo shortname/permalink
	* @return a single repo object
	*/
	public function projectRepository($permalink, $repo) {
		return $this->get($permalink . '/' . $repo);//, 'repository');
	}

	/**
	* no WORK
	* Delete a repository fo a projet
	* @param project shortname/permalink
	* @param repo shortname/permalink
	*/
	public function deleteRepository($permalink, $repo_name) {
		return $this->delete($permalink . '/' . $repo_name);
	}

	/**
	* Repository Commits
	* @param project shortname/permalink
	* @param repo shortname/permalink
	* @param refernce or revision to fetch
	* 			ref in this context can be a branch name, tag name or commit reference.
	*			If specified it will show the commits from that point in your history.
	* @param optional file to get specific history for
	*/
	public function commits($permalink, $repo, $ref, $file = '') {
		if ($file)
			$file = '/' . $file;
		return $this->get($permalink . '/' . $repo . '/commits/'. $ref . $file, 'commit');
	}
}
