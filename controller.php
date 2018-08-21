<?php

namespace Plugin\Releases;

class Controller extends \Controller
{
    public function index(\Base $f3)
    {
        $this->_requireLogin();
        $release = new Model\Release_Detail;
        $open = $release->find("closed_date IS NULL", array("order" => "target_date ASC, created_date ASC"));
        $closed = $release->find("closed_date IS NOT NULL", array("order" => "target_date DESC, created_date DESC"));
        $f3->set("open", $open);
        $f3->set("closed", $closed);
        $this->_render("releases/view/index.html");
    }

    public function add(\Base $f3)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $this->_render("releases/view/new.html");
    }

    public function single(\Base $f3, array $params)
    {
        $this->_requireLogin();
        $release = new Model\Release;
        $release->load($params["id"]);
        if (!$release->id) {
            $f3->error(404);
        }
        $f3->set("release", $release);
        $rid = new Model\Release_Issue_Detail;
        $issues = $rid->paginate(0, 999, array("release_id = ?", $release->id));
        $f3->set("issues", $issues['subset']);
        $this->_render("releases/view/single.html");
    }

    public function export(\Base $f3, array $params)
    {
        $this->_requireLogin();

        $release = new Model\Release;
        $release->load($params["id"]);
        if (!$release->id) {
            $f3->error(404);
        }

        $issue = new Model\Release_Issue_Detail;
        $issues = $issue->find(array("release_id = ?", $release->id));

        // Configure visible fields
        $fields = array(
            "id" => $f3->get("dict.cols.id"),
            "name" => $f3->get("dict.cols.title"),
            "type_name" => $f3->get("dict.cols.type"),
            "priority_name" => $f3->get("dict.cols.priority"),
            "status_name" => $f3->get("dict.cols.status"),
            "author_name" => $f3->get("dict.cols.author"),
            "owner_name" => $f3->get("dict.cols.assignee"),
            "sprint_name" => $f3->get("dict.cols.sprint"),
            "created_date" => $f3->get("dict.cols.created"),
            "due_date" => $f3->get("dict.cols.due_date"),
        );

        // Notify browser that file is a CSV, send as attachment (force download)
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=release_" . \Web::instance()->slug($release->name) . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Output data directly
        $fh = fopen("php://output", "w");

        // Add column headings
        fwrite($fh, '"' . implode('","', array_values($fields)) . "\"\n");

        // Add rows
        foreach ($issues as $row) {
            $cols = array();
            foreach (array_keys($fields) as $field) {
                $cols[] = $row->get($field);
            }
            fputcsv($fh, $cols);
        }

        fclose($fh);
    }

    public function edit(\Base $f3, array $params)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->load($params["id"]);
        if (!$release->id) {
            $f3->error(404);
        }
        $f3->set("release", $release);
        $this->_render("releases/view/edit.html");
    }

    public function close(\Base $f3, array $params)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->load($params["id"]);
        $release->closed_date = date("Y-m-d H:i:s");
        $release->save();
        $f3->reroute("/releases");
    }

    public function reopen(\Base $f3, array $params)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->load($params["id"]);
        $release->closed_date = null;
        $release->save();
        $f3->reroute("/releases");
    }

    public function delete(\Base $f3, array $params)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->load($params["id"]);
        $release->delete();
        $f3->reroute("/releases");
    }

    public function addPost(\Base $f3)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->name = $f3->get("POST.name");
        $release->description = $f3->get("POST.description");
        if ($f3->get("POST.target_date")) {
            $release->target_date = date("Y-m-d", strtotime($f3->get("POST.target_date")));
        }
        $release->save();
        $f3->reroute("/releases");
    }

    public function editPost(\Base $f3, array $params)
    {
        $this->_requireLogin(\Model\User::RANK_MANAGER);
        $release = new Model\Release;
        $release->load($params["id"]);
        $release->name = $f3->get("POST.name");
        $release->description = $f3->get("POST.description");
        if ($f3->get("POST.target_date")) {
            $release->target_date = date("Y-m-d", strtotime($f3->get("POST.target_date")));
        } else {
            $release->target_date = null;
        }
        $release->save();
        $f3->reroute("/releases");
    }
}
