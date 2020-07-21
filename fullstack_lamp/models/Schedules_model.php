<?
class Schedules_model extends MY_Model
{
    const TYPE_PLAN = 1;
    const TYPE_ACHIEVEMENT = 2;

    public function __db($args = null, $table = null)
    {
        return parent::__db($args, $table);
    }

    public function count($cond = null, $sort = null, $order = null, $limit = null, $offset = null)
    {
        $db = $this->db($cond, $sort, $order, $limit, $offset);
        return $db->get()->num_rows();
    }

    public function results($cond = null, $sort = null, $order = null, $limit = null, $offset = null)
    {
        $db = $this->db($cond, $sort, $order, $limit, $offset);
        return $db->get()->result_array();
    }

    public function db($cond = null, $sort = null, $order = null, $limit = null, $offset = null)
    {
        $columns = "s.*,
            wt.title AS title,
            wt.id AS task_id";

        $query = $this->db->select($columns)
            ->from($this->_table . " s")
            ->join("work_tasks AS wt", "wt.id = s.work_task_id", "left")
            ->where("s.client_id", $this->client_id);

        if ($cond) {

            if($cond['start'] && $cond['end']){
                $query->where("s.date >=", $cond['start']);
                $query->where("s.date <=", $cond['end']);
            }elseif($cond['start']){
                $query->where("s.date >=", $cond['start']);
            }elseif($cond['end']){
                $query->where("s.date <=", $cond['end']);
            }
            unset($cond['start']);
            unset($cond['end']);

            foreach ($cond as $key => $value) {
                $query->where($key, $value);
            }
        }

        if ($sort) {
            if (is_array($sort)) {
                foreach ($sort as $s => $o) {
                    $query->order_by($s, $o, false);
                }
            } else {
                $query->order_by($sort, $order);
            }
        }

        if ($limit || $offset) $query->limit($limit, $offset);

        return $query;
    }

    public function last_schedule($targets){
        $this->db->select("schedules.date");
        $this->db->join("work_tasks AS wt", "wt.id = schedules.work_task_id", "left");
        $this->db->where("schedules.type_id", 2);
        if ($targets) {
            foreach ($targets as $key => $target) {
                if ($key == 0) {
                    $this->db->group_start();
                    $this->db->where("wt.target_id", $target['id']);
                } else {
                    $this->db->or_where("wt.target_id", $target['id']);
                }
            }
            $this->db->group_end();
        }
        $this->db->order_by("schedules.date", "desc");
        $query = $this->db->get("schedules", 1, 0);

        return $query ? $query->row_array() : null;
    }

    // 指定期間のタスクごとの合計実績時間取得
    public function getUserAchievements($user_id, $date_start = null, $date_end = null)
    {
        $cond = [
            "schedules.user_id" => $user_id,
            "schedules.type_id" => self::TYPE_ACHIEVEMENT, 
            "schedules.status" => 1,
        ];
        if ($date_start) {
            $cond["schedules.date >="] = $date_start;
        }
        if ($date_end) {
            $cond["schedules.date <="] = $date_end;
        }

        $this->db->select("schedules.*, targets.title, work_tasks.target_id")
            ->select("SUM(TIME_TO_SEC(TIMEDIFF(schedules.end_time, schedules.start_time))) / 60 AS total_minutes")
            ->join("work_tasks", "work_tasks.id = schedules.work_task_id", "INNER")
            ->join("targets", "targets.id = work_tasks.target_id", "INNER")
            ->group_by("targets.id");
            
        $results = $this->_results($cond, null, null, null, null, "schedules");

        foreach ($results as $key => $rc) {
            $results[$key]["total_hours"] = $rc["total_minutes"] / 60;
            $results[$key]["total_times"]["hour"] = floor($results[$key]["total_hours"]);
            $results[$key]["total_times"]["minute"] = $results[$key]["total_minutes"] % 60;
        }

        return $results;
    }

}
