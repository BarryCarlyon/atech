<?php
class Codebase {

	/* Version 1.1 */

	public function __construct($username,$password,$hostname) {
		$this->username = $username;
		$this->password = $password;
		$this->hostname = $hostname;
		$this->api_user = $username;
		$this->api_key = $password;
		$this->url = 'https://api3.codebasehq.com';
	}

	/**
	Activity Feeds
	*/

	/**
	Get general acitvity
	returns a set of events
	*/
	public function activity() {
		return $this->object2array(simplexml_load_string($this->get('/activity'),'SimpleXMLElement',LIBXML_NOCDATA));
	}
	/**
	Get a specific project activity feed by permalink
	returns a set of evnents for that project
	*/
	public function projectActivity($permalink) {
		return $this->object2array(simplexml_load_string($this->get('/' . $permalink . '/activity'),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	Project(s)
	*/

	/**
	Get all projects
	Returns
		array o project objects
	*/
	public function projects() {
		$projects = $this->get('/projects');
		if($projects===false) return false;
		return $this->object2array(simplexml_load_string($projects,'SimpleXMLElement',LIBXML_NOCDATA), 'project');
	}

	/**
	Fetch a Project by Permalink
	Returns
		project object
	*/
	public function project($permalink) {
		return $this->object2array(simplexml_load_string($this->get('/'.$permalink),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	Create a Project of the given name
	Returns
		project object
	*/
	public function createProject($name) {
		$xml = '<project><name>' . $name . '</name></project>';
		return $this->object2array(simplexml_load_string($this->post('/create_project', $xml)));
	}

	/**
	Delete a Project by permalink
	Returns 200 OK
	Or array ['error'] on error (generally cannot find by permalink)
	*/
	public function deleteProject($permalink) {
		return $this->object2array(simplexml_load_string($this->delete('/'.$permalink,'SimpleXMLElement',LIBXML_NOCDATA)));
	}

	/**
	Get project Groups
	*/
	public function projectGroups() {
		return $this->object2array(simplexml_load_string($this->get('/project_groups'),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	Get/set Project User Assignments

	Pass an array of user IDs to assign to the project
		This will overwrite any current assignments that are currently in place.

	Returns a set of user(s)
	*/
	public function projectAssignments($permalink, $users = array()) {
		if (count($users)) {
			$xml = '<users>';
			foreach ($users as $user) {
				$xml .= '<user><id>' . $user . '</id></user>';
			}
			$xml = '</users>';
			return $this->object2array(simplexml_load_string($this->post('/' . $permalink . '/assignments', $xml),'SimpleXMLElement',LIBXML_NOCDATA));
		}
		return $this->object2array(simplexml_load_string($this->get('/' . $permalink . '/assignments'),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	Get project repositories
	Returns a set of Repository objects
	*/
	public function projectRepositories($permalink) {
		return $this->object2array(simplexml_load_string($this->get('/' . $permalink . '/repositories'),'SimpleXMLElement',LIBXML_NOCDATA),'repository');
	}

	/**
	create a repository
	Pass project permalink
	repo name
	scm type
	*/
	public function createRepository($permalink, $repo_name, $repo_type = 'git') {
		$xml = '<repository><name>' . $repo_name . '</name><scm>' . $repo_type . '</scm></repository>';
		echo $xml . "\n";
		return $this->object2array(simplexml_load_string($this->post('/' . $permalink . '/repositories', $xml),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	Get a sepcific project repository (by its permalink) for a given project (by its permalink)
	Returns a Repository object
	*/
	public function projectRepository($permalink, $repository) {
		return $this->object2array(simplexml_load_string($this->get('/' . $permalink . '/' . $repository),'SimpleXMLElement',LIBXML_NOCDATA));
	}

	/**
	to sort
	*/

	public function tickets($permalink) {
		$url = '/'.$permalink.'/tickets?query=sort:status';
		$xml = $this->object2array(simplexml_load_string($this->get($url),'SimpleXMLElement',LIBXML_NOCDATA));
		return $xml['ticket'];
	}


	public function notes($ticketId,$project) {
		$xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/'.$ticketId.'/notes'),'SimpleXMLElement',LIBXML_NOCDATA));
		return $xml['ticket-note'];
	}

	public function statuses($project) {
		$xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/statuses'),'SimpleXMLElement',LIBXML_NOCDATA));
		return $xml['ticketing-status'];
	}

	public function categories($project) {
		$xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/categories'),'SimpleXMLElement',LIBXML_NOCDATA));
		return $xml['ticketing-category'];
	}

	public function priorities($project) {
		$xml = $this->object2array(simplexml_load_string($this->get('/'.$project.'/tickets/priorities'),'SimpleXMLElement',LIBXML_NOCDATA));
		return $xml['ticketing-priority'];
	}

	public function addTimeEntry($project,$params) {
		$xml = '<time-session>';
		foreach($params as $key=>$value) {
			if($key=='minutes') {
				$attributes = ' type=\'integer\'';
			} elseif($key=='session-date') {
				$attributes = ' type=\'date\'';
			} else {
				$attributes = null;
			}
			$xml .= '<'.$key.$attributes.'>'.$value.'</'.$key.'>';
		}
		$xml .= '</time-session>';

		$result = $this->post('/'.$project.'/time_sessions',$xml);

		$result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
		return $result;
	}

	public function addTicket($project,$params,$files) {
		$xml = '<ticket>';
		   foreach($params as $key=>$value) {
				  $xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
		   }
		$xml .= '</ticket>';

		$result = $this->post('/'.$project.'/tickets',$xml);
		$result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
		return $result;
	}

	public function addAttachments($project,$files,$ticketId) {
		$result = null;
		foreach($files as $file) {
			$post_array['ticket_attachment[attachment]'] = '@'.$file['tmp_name'].';type='.$file['type'];
			$post_array['ticket_attachment[description]'] = $file['name'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url.'/'.$project.'/tickets/'.$ticketId.'/attachments.xml');
			curl_setopt($ch, CURLOPT_USERPWD, $this->hostname . '/'.$this->username . ':' . $this->password);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			$result .= curl_exec($ch);
		}
		return $result;
	}

	public function note($project,$note,$ticketId,$changes=array(),$minutes=null) {
		$xml = '<ticket-note>';
		   $xml .= '<content><![CDATA['.$note.']]></content>';
		   if($minutes!=null) {
			   $xml .= '<time-added><![CDATA['.$minutes.']]></time-added>';
		   }
		   if(!empty($changes)) {
			   $xml .= '<changes>';
				foreach($changes as $key=>$value) {
					  $xml .= '<'.$key.'><![CDATA['.$value.']]></'.$key.'>';
			   }
			   $xml .= '</changes>';
		   }
		$xml .= '</ticket-note>';

		$result = $this->post('/'.$project.'/tickets/'.$ticketId.'/notes',$xml);
		$result = $this->object2array(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA));
		return $result;
	}

	private function request($url=null,$xml=null,$post) {
		$ch = curl_init($this->url.$url);
		if($post == 1) {
			curl_setopt($ch, CURLOPT_POST, $post);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		} else if ($post == 2) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		$headers = array(
			'Content-Type: application/xml',
			'Accept: application/xml'
		);

		$headers[] = 'Authorization: Basic ' . base64_encode($this->api_user . ':'. $this->api_key);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);

		if(!$output || strlen($output)==1) {
			//echo "Error. ".curl_error($ch);
			return false;
		} else {
			return $output;
		}
		curl_close($ch);
	}

	private function delete($url=null) {
		return $this->request($url,null,2);
	}

	private function post($url=null,$xml=null) {
		return $this->request($url,$xml,1);
	}

	private function get($url=null) {
		return $this->request($url,null,0);
	}

	private function object2array($object, $multiple = FALSE) {
		$data = @json_decode(@json_encode($object),1);
		if ($multiple) {
			if (isset($data[$multiple]) && is_array($data[$multiple])) {
				if (!is_int(key($data[$multiple]))) {
					return array($data[$multiple]);
				} else {
					return $data[$multiple];
				}
			}
		}
		return $data;
	}
}