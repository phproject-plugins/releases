<?php

namespace Plugin\Releases;

class Projectcontroller extends \Controller
{
	/**
	 * View the planning board for a project
	 * @param  \Base  $f3
	 * @param  array  $params
	 * @return void
	 */
    public function projectPlan(\Base $f3, array $params)
    {
        $this->_requireLogin();
        $issue = new \Model\Issue\Detail;
        $issue->load($params['id']);
        if (!$issue->id) {
            $f3->error(404);
        }
        $f3->set('issue', $issue);

        $release = new Model\Release;
        $releases = $release->find(['closed_date IS NULL'], [
        	'order' => 'target_date,id'
        ]);
        $f3->set('releases', $releases);

        $f3->set('title', $issue->name . ' Product Overview');
        $this->_render('releases/view/project-plan.html');
    }
}
