<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Read/SELECT operations
     * @return  array   Array of records
     */
    public function read($options = array())
    {
        $result = array();
        if(!count($options))
        {
            return $result;
        }

        //SELECT clause
        if(array_key_exists('select', $options) && count($options['select']))
        {
            $selectcolumns = $options['select'];
        }
        else
        {
            $selectcolumns = $this->default_select;
        }
        if(count($selectcolumns))
        {
            $select = '';
            $i = 1;
            $total_cols = count($selectcolumns);
            foreach($selectcolumns as $field)
            {
                $select .= "$this->table.$field";
                if($i < $total_cols)
                    $select .= ", ";
                $i++;
            }
        }

        // Sum clause
        if(array_key_exists('sum', $options) AND is_array($options['sum']))
        {
            if(count($options['sum']))
            {
                foreach ($options['sum'] as $field)
                {
                    if(in_array($field, $this->columns))
                    {
                        $this->db->select_sum($this->table . '.' . $field, $field);
                    }
                }

            }
        }

        //MAX clause
        if(array_key_exists('max', $options) AND is_array($options['max']))
        {
            if(count($options['max']))
            {
                foreach ($options['max'] as $field)
                {
                    if(in_array($field, $this->columns))
                    {
                        $this->db->select_max($this->table . '.' . $field, $field);
                    }
                }

            }
        }
        $this->db->select($select);

        //Join clause
        if(array_key_exists('join', $options) AND is_array($options['join']))
        {
            $join = $options['join'];
            // If there are multiple joins
            if(isset($join[0]))
            {
                //Multiple joins
                foreach($join as $k => $v)
                {
                    $type = (isset($v['type']) ? $v['type'] : 'INNER');
                    if(isset($v['select']) && !empty($v['select']))
                        $this->db->select($v['select']);
                    $this->db->join($v['on'], $v['condition'], $type);
                }
            }
            else
            {
                //Single join
                $type = (isset($join['type']) ? $join['type'] : 'INNER');
                if(isset($join['select']) && !empty($join['select']))
                    $this->db->select($join['select']);
                $this->db->join($join['on'], $join['condition'], $type);
            }

        }

        // prepare where condition
        // Ignore default condition if necessary
        if((array_key_exists('default_condition', $options) && $options['default_condition'] === TRUE) || !array_key_exists('default_condition', $options))
        {
            $this->default_condition();
        }

        //WHERE clause
        if(array_key_exists('where', $options) AND is_array($options['where']))
        {
            if(count($options['where']))
            {
                foreach($options['where'] as $field => $v)
                {
                    switch($field)
                    {
                        default:
                            $this->db->where($this->table."." . $field, $v);
                            break;
                    }
                }
            }
        }

        // WHERE_IN clause
        if(array_key_exists('where_in', $options) && is_array($options['where_in']))
        {
            if (count($options['where_in']))
            {
                foreach ($options['where_in'] as $field => $v)
                {
                    if (in_array($field, $this->columns))
                    {
                        $this->db->where_in($this->table. '.' . $field, $v);
                    }
                }
            }
        }

        // WHERE NOT IN clause
        if(array_key_exists('where_not_in', $options) && is_array($options['where_not_in']))
        {
            if (count($options['where_not_in']))
            {
                foreach ($options['where_not_in'] as $field => $v)
                {
                    if (in_array($field, $this->columns))
                    {
                        $this->db->where_not_in($this->table. '.' . $field, $v);
                    }
                }
            }
        }

        // Custom WHERE clause
        if(array_key_exists('where_custom', $options) && is_array($options['where_custom']))
        {
            if(count($options['where_custom']))
            {
                foreach ($options['where_custom'] as $k => $v)
                {
                    $this->db->where($v, null, false);
                }

            }
        }

        // prepare group clause
        if(array_key_exists('group_by', $options))
        {
            if(is_array($options['group_by']))
            {
                foreach ($options['group_by'] as $k => $v)
                {
                    if(in_array($v, $this->columns))
                    {
                        $this->db->group_by($this->table . '.' . $v);
                    }
                }
            }
            else
            {
                if(in_array($options['group_by'], $this->columns))
                {
                    $this->db->group_by($this->table . '.' . $options['group_by']);
                }
            }
        }

        //Query sorting clause
        $sort = "asc";
        if(array_key_exists('sort', $options))
        {
            if($options['sort'] === 'desc')
            {
                $sort = 'desc';
            }
        }

        // Orderby clause
        if(array_key_exists('order_by', $options))
        {
            if(is_array($options['order_by']))
            {
                if(count($options['order_by']))
                {
                    foreach ($options['order_by'] as $field)
                    {
                        if(in_array($field, $this->columns))
                        {
                            $this->db->order_by("$this->table.$field", $sort);
                        }
                    }
                }
            }
            else
            {
                if(in_array($options['order_by'], $this->columns))
                {
                    $this->db->order_by($this->table . '.' . $options['order_by'], $sort);
                }

            }
        }

        // Multiple tables orderby clause
        if(array_key_exists('order_by_custom', $options))
        {
            if(is_array($options['order_by_custom']))
            {
                if(count($options['order_by_custom']))
                {
                    foreach ($options['order_by_custom'] as $field)
                    {
                        $this->db->order_by("$field", $sort);
                    }
                }
            }
            else
            {
                $this->db->order_by($options['order_by_custom'], $sort);
            }
        }

        //LIMIT clause
        if(array_key_exists('limit', $options))
        {
            if(is_array($options['limit']))
            {
                $limit = $options['limit'][0];
                $offset = $options['limit'][1];
                $this->db->limit($offset, $limit);
            }
            else
            {
                $this->db->limit($options['limit']);
            }
        }

        $r = $this->db->get($this->table);
        $result = array();
        if($r->num_rows)
        {
            if(array_key_exists('limit', $options) && $options['limit'] == 1)
            {
                $result = $r->row();
            }
            else
            {
                $result = $r->result();
            }
        }

        //Return the result
        return $result;
    }

    /*
     * Update records
     * @return      integer     no of effected rows
     */
    public function update($data = array(), $where = array())
    {
        if(count($data) < 1)
            return false;

        $is_condition_set = FALSE;
        $return = false;
        foreach ($where as $k => $v)
        {
            if (in_array($k, $this->columns))
            {
                $this->db->where($k, $v);
                $is_condition_set = TRUE;
            }
        }
        if ($is_condition_set)
        {
            $this->db->update($this->table, $data);
            $return = $this->db->affected_rows();
        }
        return $return;
    }

    /*
     * DELETE records
     * @return    integer     No of effected rows
     */
    public function delete($where = array())
    {
        if(count($where) < 1)
            return false;

        $response = false;
        $is_condition_set = false;
        foreach($where as $k => $v)
        {
            if(in_array($k, $this->columns))
            {
                $is_condition_set = true;
                $this->db->where("$this->table.$k", $v);
            }
        }
        if($is_condition_set)
        {
            if($this->db->delete($this->table))
                $response = $this->db->affected_rows();
        }
        return $response;
    }


    /*
     * Add single record to the database
     * @return  integer     Inserted ID
     */
    public function add($data = array())
    {
        $response = false;
        if(count($data) < 1)
            return false;

        $r = $this->db->insert($this->table, $data);
        if($r)
            $response = $this->db->insert_id();
        return $response;
    }

    /*
     * Add multiple records to the database
     * @return      integer     No of effected rows
     */
    public function add_batch($data = array())
    {
        if(count($data) < 1)
            return false;

        $this->db->insert_batch($this->table, $data);
        return $this->db->affected_rows();
    }

    /*
     * Set default condition used in SELECT queries
     * @return  void
     */
    protected function default_condition()
    {
        $this->db->where($this->table . '.deleted_at !=', NULL);
    }

    /*
     * Run a custom query
     * @return  integer     no of affected row
     */
    public function runQuery($query = '')
    {
        if(!$query)
        {
            return false;
        }
        $this->db->query($query);
        if($this->db->affected_rows())
            return $this->db->affected_rows();
        else
            return 0;
    }

    /*
     * Produces insert query statement which can be used to run later
     * @return  string  the query statement
     */
    public function insert_string($data = array())
    {
        if(!$data)
        {
            return false;
        }
        return $this->db->insert_string($this->table, $data);
    }

    /*
     * @return  integer last insert id
     */
    public function lastInsertId()
    {
        return $this->db->insert_id();
    }

    /*
     * Permanently delete data by using `IN` clause
     * $param   array   assoc array with two indexes 'key' and 'values'
     * $return  int     No of effected records in db
     */
    public function delete_in($where = array())
    {
        if(count($where) < 1)
            return false;

        $response = false;
        $is_condition_set = false;
        $key = (array_key_exists('key', $where) && isset($where['key']) ? $where['key'] : '');
        $values = (array_key_exists('values', $where) && isset($where['values']) ? $where['values'] : '');
        if(in_array($key, $this->columns) && isset($values))
        {
            $this->db->where_in("$this->table.$key", $values);
            $is_condition_set = true;
        }
        if($is_condition_set)
        {
            if($this->db->delete($this->table))
                $response = $this->db->affected_rows();
        }
        return $response;
    }
    
}
