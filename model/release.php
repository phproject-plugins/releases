<?php

namespace Plugin\Releases\Model;

class Release extends \Model {

	protected $_table_name = "release";
	protected $_issues = null;

	/**
	 * Load a release by an issue that is tied to it
	 * @param  int $id
	 * @return Release
	 */
	public function loadByIssueId($id) {
		$ri = new Release_Issue;
		$ri->load(array("issue_id = ?", $id));
		if($ri->id) {
			$this->load($ri->release_id);
		}
		return $this;
	}

	/**
	 * Get issues for this release
	 * @return array
	 * @throws Exception
	 */
	public function getIssues() {
		if(!$this->id) {
			throw new Exception("Cannot get issues for unsaved release.");
		}
		if($this->_issues === null) {
			$rid = new Release_Issue_Detail;
			$this->_issues = $rid->find(array("release_id = ?", $this->id));
		}
		return $this->_issues;
	}

}
