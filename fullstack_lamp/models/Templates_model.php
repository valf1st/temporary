<?php
class Templates_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __db($args = null, $table = null)
    {
        return parent::__db($args, $table);
    }

    public function results($cond = null, $sort = null, $order = null, $limit = null, $offset = null)
    {
        if ($cond) {
            foreach ($cond as $field => $value) {
                switch ($field) {
                    case "id":
                        if (!$value) {
                            unset($cond[$field]);
                        }
                        break;
                    case "title":
                        if ($value) {
                            $this->db->like($field, $value);
                        }
                        unset($cond[$field]);
                        break;
                    case "description":
                        if ($value) {
                            $this->db->like($field, $value);
                        }
                        unset($cond[$field]);
                        break;
                }
            }
        }
        if (!$sort && !$order) {
            $this->db->order_by("order_no", "ASC");
        }

        $this->db->select("templates.id, title, description");
        return parent::results($cond, $sort, $order, $limit, $offset);
    }

    public function title_list($cond = array("status <" => 2))
    {
        return array_column($this->results($cond), "title", "id");
    }

    // template_tasksに子要素があるテンプレートを取得
    public function getTemplateList()
    {
        $this->db->select("templates.*");
        $this->db->join("template_tasks", "template_tasks.template_id = templates.id", "INNER");
        $this->db->group_by("templates.id");

        $cond = [
            // 条件いまのところなし
        ];
        $template_list = $this->_results($cond, null, null, null, null, "templates");
        $template_list[] = ["id" => 0, "title" => "レシピを選択せずに作成"];
        $template_list = array_column($template_list, null, "id");

        return $template_list;
    }
}
