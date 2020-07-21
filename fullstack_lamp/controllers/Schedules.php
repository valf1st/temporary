<?
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . "controllers/extends/home_controller.php");

class Schedules extends Home_controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _render($args = array(), $template_path = NULL, $layout = TRUE)
    {
        $args["page_title"] = $args["page_title"] ?: "スケジュール";

        parent::_render($args, $template_path, $layout);
    }

    public function index()
    {
        $this->_render($args);
    }

    public function tasks()
    {
        $cond = array(
            's.user_id' => $this->current_user->id,
            's.type_id' => $this->_m('schedules')::TYPE_PLAN,
            's.status' => 1,
        );
        $schedules = $this->_m('schedules')->results($cond);
        foreach ($schedules as $rc) {
            $events[] = [
                'id'    => $rc['id'],
                'title' => $rc['title'],
                'start' => "{$rc['date']} {$rc['start_time']}",
                'end'   => "{$rc['date']} {$rc['end_time']}",
                'url'   => "/mypage/work_tasks/" . $rc['task_id'] . "/modify",
            ];
        }
        $this->_json($events);
    }

    public function date_change_ajax(){
        $args = $this->input->post();

        $start = explode('T', $args['start']);
        $date = $start[0];
        $start_time = $start[1];
        $end = explode('T', $args['end']);
        $end_time = $end[1];
        if($args['schedule_id']){
            $change = $this->_m('schedules')->update($args['schedule_id'], ['start_time' => $start_time, 'end_time' => $end_time, 'date' => $date]);
        }

        if($change){
            $res = ['state' => "success"];
        }else{
            $res = ['state' => "failed"];
        }
        $this->_json($res);
    }
}
