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

        $board = [];

        // Load features
        $featureType = $f3->get('site.plugins.releases.type.feature');
        $features = $issue->find([
            'type_id = ? AND parent_id = ?',
            $featureType,
            $issue->id,
        ]);
        $featureIds = [];
        foreach ($features as $feature) {
            $board[$feature->id] = $feature->cast();
            $featureIds[] = $feature->id;
        }
        $featureIdStr = implode(',', $featureIds);

        // Load epics
        $epicType = $f3->get('site.plugins.releases.type.epic');
        if ($featureIds) {
            $epics = $issue->find([
                "type_id = ? AND (parent_id = ? OR parent_id in ($featureIdStr))",
                $epicType,
                $issue->id,
            ]);
        } else {
            // TODO: actually support this
            $epics = $issue->find([
                "type_id = ? AND (parent_id = ?)",
                $epicType,
                $issue->id,
            ]);
        }
        $epicIds = [];
        foreach ($epics as $epic) {
            if (isset($board[$epic->parent_id])) {
                $board[$epic->parent_id]['epics'][] = $epic->cast();
                $epicIds[] = $epic->id;
            }
        }
        $storyParentStr = implode(',', array_merge($featureIds, $epicIds));

        $f3->set('board', $board);

        // Load stories
        $storyMap = [];
        $storyIds = [];
        if ($featureIds || $epicIds) {
            $storyType = $f3->get('site.plugins.releases.type.story');
            $stories = $issue->find([
                "type_id = ? AND parent_id IN ($storyParentStr)",
                $storyType,
            ]);
            foreach ($stories as $story) {
                $storyMap[$story->parent_id][] = $story->cast();
                $storyIds[] = $story->id;
            }
            $storyIdStr = implode(',', $storyIds);
        }
        $f3->set('storyMap', $storyMap);

        // Build release-story map
        $releaseMap = [];
        $releaseMappedIssues = [];
        if ($storyIds) {
            $releaseIssue = new Model\Release_Issue;
            $releaseIssues = $releaseIssue->find(["issue_id IN ($storyIdStr)"]);
            foreach ($releaseIssues as $ri) {
                $releaseMappedIssues[] = $ri->issue_id;
                $releaseMap[$ri->release_id][] = $ri->issue_id;
            }
        }
        $f3->set('releaseMap', $releaseMap);
        $f3->set('releaseMappedIssues', $releaseMappedIssues);

        // Load releases
        $release = new Model\Release;
        if ($releaseMap) {
            $releaseStr = implode(',', array_keys($releaseMap));
            $where = "id IN ($releaseStr)";
        } else {
            $where = "closed_date IS NULL";
        }
        $releases = $release->find($where, [
            'order' => 'target_date,id'
        ]);
        $f3->set('releases', $releases);

        // Render board
        $f3->set('title', $issue->name . ' Product Overview');
        $this->_render('releases/view/project-plan.html');
    }
}
