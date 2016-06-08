<?php

namespace Plugin\Releases;

class Controller extends \Controller {

	public function index(\Base $f3) {
		$this->_requireLogin();
		// TODO: Load releases
		$release = new Model\Release_Detail;
		$open = $release->find("closed_date IS NULL");
		$closed = $release->find("closed_date IS NOT NULL");
		$f3->set("open", $open);
		$f3->set("closed", $closed);
		$this->_render("releases/view/index.html");
	}

	public function add(\Base $f3) {
		$this->_requireAdmin();
		$this->_render("releases/view/new.html");
	}

	public function single(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		if(!$release->id) {
			$f3->error(404);
		}
		$f3->set("release", $release);
		$rid = new Model\Release_Issue_Detail;
		$issues = $rid->paginate(array("release_id = ?", $release->id), 999);
		$f3->set("issues", $issues);
		$this->_render("releases/view/single.html");
	}

	public function edit(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		if(!$release->id) {
			$f3->error(404);
		}
		$f3->set("release", $release);
		$this->_render("releases/view/edit.html");
	}

	public function close(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		$release->closed_date = date("Y-m-d H:i:s");
		$release->save();
		$f3->reroute("/releases");
	}

	public function reopen(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		$release->closed_date = null;
		$release->save();
		$f3->reroute("/releases");
	}

	public function delete(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		$release->delete();
		$f3->reroute("/releases");
	}

	public function addPost(\Base $f3) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->name = $f3->get("POST.name");
		$release->description = $f3->get("POST.description");
		if($f3->get("POST.target_date")) {
			$release->target_date = date("Y-m-d", strtotime($f3->get("POST.target_date")));
		}
		$release->save();
		$f3->reroute("/releases");
	}

	public function editPost(\Base $f3, array $params) {
		$this->_requireAdmin();
		$release = new Model\Release;
		$release->load($params["id"]);
		$release->name = $f3->get("POST.name");
		$release->description = $f3->get("POST.description");
		if($f3->get("POST.target_date")) {
			$release->target_date = date("Y-m-d", strtotime($f3->get("POST.target_date")));
		} else {
			$release->target_date = null;
		}
		$release->save();
		$f3->reroute("/releases");
	}

	public function tie(\Base $f3) {
		// TODO: tie issue to release
	}

}
