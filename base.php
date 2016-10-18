<?php
/**
 * @package  Releases
 * @author   Alan Hardman <alan@phpizza.com>
 * @version  0.1.0
 * @requires 1.4.1
 */

namespace Plugin\Releases;

class Base extends \Plugin {

	/**
	 * Initialize the plugin
	 * @todo load configuration and initialize socket connection
	 */
	public function _load() {
		$f3 = \Base::instance();

		// Set up routes
		$f3->route("GET /releases", "Plugin\Releases\Controller->index");
		$f3->route("GET /releases/new", "Plugin\Releases\Controller->new");
		$f3->route("GET /releases/new", "Plugin\Releases\Controller->add");
		$f3->route("GET /releases/@id", "Plugin\Releases\Controller->single");
		$f3->route("GET /releases/@id/export", "Plugin\Releases\Controller->export");
		$f3->route("GET /releases/@id/edit", "Plugin\Releases\Controller->edit");
		$f3->route("GET /releases/@id/close", "Plugin\Releases\Controller->close");
		$f3->route("GET /releases/@id/reopen", "Plugin\Releases\Controller->reopen");
		$f3->route("GET /releases/@id/delete", "Plugin\Releases\Controller->delete");
		$f3->route("POST /releases/new", "Plugin\Releases\Controller->addPost");
		$f3->route("POST /releases/@id/edit", "Plugin\Releases\Controller->editPost");
		$f3->route("POST /releases/tie", "Plugin\Releases\Controller->tie");

		// Add navigation
		$this->_addNav("releases", "Releases", "/^\\/releases/", "browse");

		// Render release field and box on issue pages
		$this->_hook("render.issue_edit.after_fields", array($this, "issueField"));
		$this->_hook("render.issue_single.before_description", array($this, "issueBox"));

		// Handle issue saving
		$this->_hook("model/issue.before_save", array($this, "issueSave"));
	}

	/**
	 * Install plugin (add database tables)
	 */
	public function _install() {
		$f3 = \Base::instance();
		$db = $f3->get("db.instance");
		$install_db = file_get_contents(__DIR__ . "/db/database.sql");
		$db->exec(explode(";", $install_db));
	}

	/**
	 * Check if plugin is installed
	 * @return bool
	 */
	public function _installed() {
		$f3 = \Base::instance();
		$db = $f3->get("db.instance");
		$q = $db->exec("SHOW TABLES LIKE 'release'");
		return !!$db->count();
	}

	/**
	 * Render the release field on the issue edit form
	 * @param \Model\Issue $issue
	 */
	public function issueField(\Model\Issue $issue) {
		$f3 = \Base::instance();
		$release = new Model\Release;
		$release->loadByIssueId($issue->id);
		\Base::instance()->set("release", $release);
		if($f3->get("user.rank") >= \Model\User::RANK_MANAGER) {
			\Base::instance()->set("releases", $release->find());
			echo \Template::instance()->render("releases/view/issue-field.html");
		}
	}

	/**
	 * Render the release details box on the single issue page
	 * @param \Model\Issue $issue
	 */
	public function issueBox(\Model\Issue $issue) {
		echo \Template::instance()->render("releases/view/issue-box.html");
	}

	/**
	 * Save the release when the issue is saved
	 * @param \Model $issue
	 */
	public function issueSave(\Model $issue) {
		$f3 = \Base::instance();
		if(isset($_POST["release_id"])) {
			$ri = new Model\Release_Issue;
			$ri->load(array("issue_id = ?", $issue->id));
			if($release_id = $f3->get("POST.release_id")) {
				$ri->issue_id = $issue->id;
				$ri->release_id = $release_id;
				$ri->save();
			} elseif($ri->id) {
				$ri->delete();
			}
		}
	}

}
