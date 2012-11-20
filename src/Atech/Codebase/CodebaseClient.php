<?php
/**
 * Copyright 2012 Barry Carlyon. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Atech\Codebase;

use Atech\Common\Client\AbstractClient;

/**
 * Client to interact with CodeBase
 *
 */
class CodebaseClient extends AbstractClient
{
    static $url = 'https://api3.codebasehq.com/';
    static $dataType = 'application/xml';
    private $hostname = '';

    /**
    * Spawn
    *
    * @param string $apiuser API Username
    * @param string $apikey  API Key
    *
    * @return class object
    */
    public function __construct($apiuser, $apikey)
    {
        list($this->hostname, $user) = explode('/', $apiuser);
        return parent::build(CodebaseClient::$dataType, CodebaseClient::$url, $apiuser, $apikey);
    }

    /**
    Activity
    */

    /**
    * Get General Activity/Dashboard Feed
    *
    * @return an array of events
    */
    public function listActivity()
    {
        return $this->activityRepair($this->get('activity', 'event'));
    }

    /**
    * Get Project Activity/Dashboard Feed
    *
    * @param string $permalink or shortname of a project
    *
    * @return array of events
    */
    public function listProjectActivity($permalink)
    {
        return $this->activityRepair($this->get($permalink . '/activity', 'event'));
    }

    /**
    * Repairs the URL's in the activity stream
    * as they are local instead of permalink
    *
    * @param array $data array of activity events
    *
    * @return array an array of fixed acitivity events
    */
    private function activityRepair($data) {
        // repair
        foreach ($data->event as &$entry) {
            $entry = str_replace('<a href="', '<a href="https://' . $this->hostname . '.codebasehq.com', $entry);
        }
        return $data;
    }

    /**
    Projects
    */

    /**
    * Get Projects
    *
    * @return an array of projects
    */
    public function listProjects()
    {
        return $this->get('projects', 'project');
    }

    /**
    * Get a project
    *
    * @param string $permalink shortname of a project
    *
    * @return a single project
    */
    public function getProject($permalink)
    {
        return $this->get($permalink);
    }

    /**
    * Create a Project
    *
    * @param string $project_name project name, 
    *                helps to form the short name too, which is returned
    *
    * @return a project object
    */
    public function createProject($project_name)
    {
        $xml = '<project><name>' . $project_name . '</name></project>';
        return $this->post('create_project', $xml, 'project');
    }

    /**
    * Delete a project
    *
    * @param string $permalink permalink/shortname
    *
    * @return true on success, excpetion on error
    */
    public function deleteProject($permalink)
    {
        return $this->delete($permalink);
    }

    /**
    * Project Groups
    *
    * @return array of group objects
    *               (containinng a dynamic ID (can change) and the name)
    */
    public function listProjectGroups()
    {
        return $this->get('project_groups', 'project-group');
    }

    /**
    * Project User Assignments
    *
    * @param string $permalink permalink to project
    *
    * @return user objects
    */
    public function listProjectAssignments($permalink)
    {
        return $this->get($permalink . '/assignments', 'user');
    }

    /**
    * Project User Assignments
    *
    * @param string $permalink permalink to project
    * @param array  $users     user ID's to assign to project
    *                          (overrides existing),
    *                          leave blank to get current assignments
    *
    * @return user objects
    */
    public function setProjectAssignments($permalink, $users = false)
    {
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
    *
    * @param string $permalink permalink/shortname of project
    *
    * @return an array of repository objects
    */
    public function listProjectRepositories($permalink)
    {
        return $this->get($permalink . '/repositories', 'repository');
    }

    /**
    * Create a repository for a project
    *
    * @param string $permalink project shortname/permalink
    * @param string $repo_name repo name to use
    * @param string $repo_type repo type, git/svn/hg/bzr
    *
    * @return a single repositrory object (specifc keys to the repo_type)
    */
    public function createProjectRepository($permalink, $repo_name, $repo_type = 'git')
    {
        $xml = '<repository><name>' . $repo_name . '</name>
            <scm>' . $repo_type . '</scm>
        </repository>';
        return $this->post($permalink . '/repositories', $xml, 'repository');
    }

    /**
    * Return a specific project repoistory
    *
    * @param string $permalink project shortname/permalink
    * @param string $repo      repo shortname/permalink
    *
    * @return a single repo object
    */
    public function getProjectRepository($permalink, $repo)
    {
        return $this->get($permalink . '/' . $repo);//, 'repository');
    }

    /**
    * no WORK
    * Delete a repository fo a projet
    *
    * @param string $permalink project shortname/permalink
    * @param string $repo_name repo shortname/permalink
    *
    * @return bool|excpetion true or thrown error
    */
    public function deleteProjectRepository($permalink, $repo_name)
    {
        return $this->delete($permalink . '/' . $repo_name);
    }

    /**
    Commits
    */

    /**
    * Repository Commits
    *
    * @param string $permalink project shortname/permalink
    * @param string $repo      repo shortname/permalink
    * @param string $ref       refernce or revision to fetch
    *                          ref in this context can be a branch name,
    *                          tag name or commit reference.
    *                          If specified it will show the commits from
    *                          that point in your history.
    * @param string $file      optional file to get specific history for
    *
    * @return array a set of commits
    */
    public function listProjectRepositoryCommits($permalink, $repo, $ref, $file = '')
    {
        if ($file) {
            $file = '/' . $file;
        }
        return $this->get($permalink . '/' . $repo . '/commits/'. $ref . $file, 'commit');
    }

    /**
    Deployments
    */

    /**
    * Create a deployment for a repository of a project
    *
    * @param string       $permalink   project shortname/permalink *required
    * @param string       $repository  repository shortname/permalink *required
    * @param string       $branch      branch to deploy from *required
    * @param string       $revision    revision to deploy to *required
    * @param string|array $servers     array of server urls to send to, or a single server url to deployto *required
    * @param string       $environment optional name to use as a reference
    *
    * @return a single deployment object
    */
    public function createProjectRespositoryDeployment($permalink, $repository, $branch, $revision, $servers, $environment = 'production')
    {
        $xml = '<deployment>
    <branch>' . $branch . '</branch>
    <revision>' . $revision . '</revision>
    <servers>' . (is_array($servers) ? implode(',', $servers) : $servers) . '</servers>
    <environment>' . $environment . '</environment>
</deployment>
';
        return $this->post($permalink . '/' . $repository . '/deployments', $xml, 'deployment');
    }

    /**
    Files
    http://support.codebasehq.com/kb/api-documentation/repositories/files
    */

    /**
    Hooks
    */

    /**
    * Get all hooks for a repository
    *
    * @param string $permalink  project shortname/permalink *required
    * @param string $repository repository shortname/permalink *required
    *
    * @return a hook object
    */
    public function getProjectRepositoryHooks($permalink, $repository) {
        return $this->get($permalink . '/' . $repository . '/hooks', 'repository-hook');
    }

    /**
    * Create a hook for a repository
    *
    * @param string $permalink  project shortname/permalink *required
    * @param string $repository repository shortname/permalink *required
    * @param string $url        url to post to *required
    * @param string $username   username for basic auth if needed
    * @param string $password   password for basic auth if needed
    */
    public function createProjectRepositoryHook($permalink, $repository, $url, $username = false, $password = false)
    {
        $xml = '<repository-hook>
    <url>' . $url . '</url>
    ' . ($username ? '<username>' . $username . '</username>' : '') . '
    ' . ($password ? '<password>' . $password . '</password>' : '') . '
</repository-hook>';
        return $this->post($permalink . '/' . $repository . '/hooks', $xml, 'repository-hook');
    }

    /**
    Tickets
    */

    /**
    * Get all ticket for a project
    *
    * @param string $permalink project shortname/permalink *required
    * @param string $search    optional search parameters
    * @param string $page      page number to retrieve, its paginated in groups of 20
    *
    * @return array a set of tickets
    */
    public function listProjectTickets($permalink, $search = '', $page = 0)
    {
        if ($search) {
            $search = '?query=' . $search;
        }
        if ($page > 1) {
            $num = $page;
            if ($search) {
                $page = '&';
            } else {
                $page = '?';
            }
            $page .= 'page=' . $num;
        }
        return $this->get($permalink . '/tickets' . $search . $page, 'ticket');
    }

    /**
    * Create a ticket on a project
    *
    * Valid Keys
    * summary
    * description
    * ticket-type normally one of bug, enhancement or task (defaults to bug)
    * reporter-id ID of the user who reports the ticket
    * assignee-id ID of the user to assign the ticket to (optional)
    * category-id ID of the category to assign (use listProjectTicketCategories) (optional)
    * priority-id ID of the priority to assign (use listProjectTicketPriorities) (optional defailts to middle state)
    * status-id ID of the status to assign (use listProjectTicketStatuses) (optional defaults to new state)
    * milestone-id ID of the milestone to assign (use listProjectTicketMilestones) (optional)
    *
    * @param string $permalink project shortname/permalink *required
    * @param array  $data      data packet for ticket
    *
    * @return mixed the created ticket or false on error
    */
    public function createProjectTicket($permalink, $data)
    {
        $xml = '<ticket>';
        foreach ($data as $item => $value) {
            $xml .= '<' . $item . '><![CDATA[' . $value . ']]></' . $item . '>';
        }
        $xml .= '</ticket>';

        return $this->post($permalink . '/tickets', $xml);
    }

    /**
    * List project ticket available states
    *
    * @param string $permalink project shortname/permalink *required
    *
    * @return mixed a set of ticketing-status of false on error
    */
    public function listProjectTicketStatuses($permalink)
    {
        return $this->get($permalink . '/tickets/statuses', 'ticketing-status');
    }

    /**
    * List project ticket available priorities
    *
    * @param string $permalink project shortname/permalink *required
    *
    * @return mixed a set of ticketing-status of false on error
    */
    public function listProjectTicketPriorities($permalink)
    {
        return $this->get($permalink . '/tickets/priorities', 'ticketing-priority');
    }

    /**
    * List project ticket available categories
    *
    * @param string $permalink project shortname/permalink *required
    *
    * @return mixed a set of ticketing-status of false on error
    */
    public function listProjectTicketCategories($permalink)
    {
        return $this->get($permalink . '/tickets/categories', 'ticketing-category');
    }

    /**
    * Get notes on a ticket
    *
    * @param string $permalink  project shortname/permalink *required
    * @param integer $ticket_id ticket ID
    *
    * @return mixed the updated ticket or false on error
    */
    public function listProjectTicketNotes($permalink, $ticket_id)
    {
        return $this->get($permalink . '/tickets/' . $ticket_id . '/notes', 'ticket-note');
    }

    /**
    * Get a specific note on a ticket
    *
    * @param string $permalink  project shortname/permalink *required
    * @param integer $ticket_id ticket ID
    * @param integer $note_id   note ID
    *
    * @return mixed the updated ticket or false on error
    */
    public function getProjectTicketNote($permalink, $ticket_id, $note_id)
    {
        return $this->get($permalink . '/tickets/' . $ticket_id . '/notes/' . $note_id, 'ticket-note');
    }

    /**
    * Create a note for a ticket on a project
    * this will also update the primary ticket of the note stream
    *
    * @param string  $permalink   project shortname/permalink *required
    * @param integer $ticket_id   ticket ID
    * @param array   $note_data   data packet for note
    * @param array   $ticket_data data packet to update the primary ticket *optional for no changes to make
    *                               status-id
    *                               priority-id
    *                               category-id
    *                               assignee-id
    *                               milestone-id
    *                               summary
    *
    * @return mixed the ticket? or false
    */
    public function createProjectTicketNote($permalink, $ticket_id, $note_data, $ticket_data = array())
    {
        $xml = '<ticket-note>';
        if (isset($note_data['content']) && $note_data['content']) {
            $xml .= '<content><![CDATA[' . $note_data['content'] . ']]></content>'; 
        }
        if (isset($note_data['time-added']) && $note_data['time-added']) {
            $xml .= '<time-added>' . $note_data['time-added'] . '</time-added>'; 
        }
        if (count($ticket_data)) {
            $xml .= '<changes>';
            foreach ($ticket_data as $item => $value) {
                $xml .= '<' . $item . '>';
                if ($item == 'summary') {
                    $xml .= '<![CDATA[' . $value . ']]>';
                } else {
                    $xml .= $value;
                }
                $xml .= '</' . $item . '>';
            }
            $xml .= '</changes>';
        }
        $xml .= '</ticket-note>';

        return $this->post($permalink . '/tickets/' . $ticket_id . '/notes', $xml);
    }

    /**
    * Get all Milestones for a project
    *
    * @param string  $permalink   project shortname/permalink *required
    *
    * @return a set of milestones
    */
    public function listProjectMilestones($permalink)
    {
        return $this->get($permalink . '/milestones', 'ticketing-milestone');
    }

    /**
    * Who is watching this ticket?
    *
    * @param string  $permalink   project shortname/permalink *required
    * @param integer $ticket_id   ticket ID
    *
    * @return a set of watchers (a user ID/username per item)
    */
    public function listProjectTicketWatchers($permalink, $ticket_id)
    {
        return $this->get($permalink . '/tickets/' . $ticket_id . '/watchers', 'watcher');
    }

    /**
    Ticket attachements
    http://support.codebasehq.com/kb/api-documentation/tickets-and-milestones/attachments
    */

    /**
    * Get all time sessions for a project
    * 
    * @param string $permalink   project shortname/permalink *required
    *
    * @return mixed a set of time sessions or false
    */
    public function listProjectTimeSessions($permalink)
    {

    }
}
