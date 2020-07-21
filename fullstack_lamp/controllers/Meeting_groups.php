<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . "controllers/extends/home_controller.php");

class Meeting_groups extends Home_controller
{

    public function index($type = null)
    {
        
    }

    public function detail($m_group_id){
        if (!$args['meeting_group'] = $this->_m("meeting_groups")->get_meeting_group_details($m_group_id)) {
            show_404();
            exit();
        }

        // グループのミーティング一覧
        $args['meetings'] = $this->_m("meetings")->results(['meeting_group_id' => $m_group_id]);

        $this->_render($args);
    }

    public function create($id = null)
    {
        $get = $this->input->get();

        if ($id) {
            if (!$args['meeting_group'] = $this->_m('meeting_groups')->get_meeting_group_details($id)) {
                show_404();
                exit();
            }
            $args['member_ids'] = array_column($args['meeting_group']['member'], 'user_id');
            $args['admins'] = $this->_m("meeting_group_users")->group_admin_users($id);
        } else {
            $args['member_ids'] = array($this->current_user->id);
            $args['admins'] = array($this->current_user->id);
        }
        $args['user_list'] = $this->group_users;

        $this->_render($args);
    }

    public function commit(){
        $args = $this->input->post();
        // メンバー権限
        $admins = array_values(array_filter($args['user_admins']));

        // ミーティンググループ登録
        if($args['meeting_group']['id']){
            $this->_m("meeting_groups")->update($args['meeting_group']['id'], $args['meeting_group']);
            // メンバー変更
            $b_members = $this->_m("meeting_groups")->get_members($args['meeting_group']['id']);
            foreach($b_members as $bm){
                if(in_array($bm['user_id'], $args['user_ids'])){
                    $p = array_search($bm['user_id'], $args['user_ids']);
                    // 権限変更
                    $admin = in_array($args['user_ids'][$p], $admins) ? 1 : null;
                    $this->_m("meeting_group_users")->update(["meeting_group_id" => $args['meeting_group']['id'], "user_id" => $args['user_ids'][$p]], ["admin" => $admin]);
                    unset($args['user_ids'][$p]); // 既に登録されているので重複しないようunset
                }else{
                    // 削除されたメンバー
                    $this->_m("meeting_group_users")->remove_user($args['meeting_group']['id'], $bm['user_id']);
                }
            }
            // 新規で追加されたメンバー
            if($args['user_ids']){
                foreach($args['user_ids'] as $user){
                    $admin = in_array($user, $admins) ? 1 : null;
                    $this->_m("meeting_group_users")->insert(["meeting_group_id" => $args['meeting_group']['id'], "user_id" => $user, "admin" => $admin]);
                }
            }
        }else{
            $args['meeting_group']['id'] = $this->_m("meeting_groups")->insert($args['meeting_group']);
            // メンバー登録
            foreach($args['user_ids'] as $user){
                $admin = in_array($user, $admins) ? 1 : null;
                $this->_m("meeting_group_users")->insert(["meeting_group_id" => $args['meeting_group']['id'], "user_id" => $user, "admin" => $admin]);
            }
        }
        $this->_redirect("/mypage/meeting_groups/".$args['meeting_group']['id']);
    }

}
