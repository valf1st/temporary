<?
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . "controllers/extends/home_controller.php");

class Roadmap extends Home_controller
{
    public function index()
    {
        // マイルストーンの取得
        $args['milestones'] = (function($cond) {
            $results = $this->_m('milestones')->all($cond);
            foreach ($results as $rc) {
                $return[$rc['id']] = $rc;
            }
            return $return;
        })(array(
            'user_id' => $this->current_user->id,
            'organization' => true,
            'date >=' => date('Y-m-d'),
            'status' => 1,
        ));

        $finished_tasks = 0;
        $unfinished_tasks = 0;
        if($args['milestones']){
            foreach ($args['milestones'] as $milestone) {
                $cond = array("milestone_id" => $milestone['id']);
                $targets = $this->_m("targets")->results($cond);
                if($targets){
                    foreach($targets as $target){
                        $finished_cond = array("target_id" => $target['id'], "user_id" => $this->current_user->id, "progress" => 100);
                        $finished_tasks += $this->_m("work_tasks")->search_count($finished_cond);
                        $unfinished_cond = array("target_id" => $target['id'], "user_id" => $this->current_user->id, "progress !=" => 100);
                        $unfinished_tasks += $this->_m("work_tasks")->search_count($unfinished_cond);
                    }
                }
            }
        }
        $args['finished_tasks'] = $finished_tasks;
        $args['all_tasks'] = $finished_tasks + $unfinished_tasks;
        $percent = ($finished_tasks == 0)? 0 : $finished_tasks * 100 / ($finished_tasks + $unfinished_tasks);
        $args['percent'] = round($percent, 1);

        // 自分がリーダーになっているグループ
        $args['groups'] = $this->admin_groups;

        $this->_render($args);
    }

    public function validation()
    {
        $args = $this->input->post();

        $this->form_validation->set_rules("date", "日付", "required");
        $this->form_validation->set_rules("title", "目標", "required");
        $this->form_validation->set_rules("type_group", "対象", "required");

        $this->form_validation->run();
        $errors = $this->form_validation->error_array();

        $this->_validation_response($errors);
    }

    public function create($id = null)
    {
        $args = $this->input->post();
        $args['status'] = $args['status'] ?: 1;

        switch ($args['type_group']) {
            case "personal":
                $args['type_id'] = 1;
                $args['user_id'] = $this->current_user->id;
                break;
            case "organization":
                $args['type_id'] = 3;
                break;
            default:
                $args['type_id'] = 2;
                $args['group_id'] = $args['type_group'];
                break;
        }
        unset($args['type_group']);

        if ($id) {
            $cond = array(
                'id' => $id,
                'user_id' => $this->current_user->id,
            );
            if ($data = $this->_m('milestones')->once($cond)) {
                $this->_m('milestones')->update($data['id'], $args);
            }
        } else {
            if (!empty($args)) {
                $data['id'] = $this->_m('milestones')->insert($args);
            }
        }

        $this->_redirect('/mypage/roadmap');
    }

    public function delete($id)
    {
        $args = $this->input->post();

        $cond = array(
            'id' => $id,
            'user_id' => $this->current_user->id,
        );

        if ($data = $this->_m('milestones')->once($cond)) {
            $this->_m('milestones')->update($data['id'], array('status' => 2));
            $success = true;
        }

        $this->_redirect('mypage/roadmap');
    }
}
