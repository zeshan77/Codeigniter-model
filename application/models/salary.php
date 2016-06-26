<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Salary extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        //Table name
        $this->table = 'salaries';

        //Columns
        $this->columns = array(
            'id',
            'employee_id',
            'amount',
            'currency',
            'isCurrent'
        );

        //By default SELECT these columns in SELECT queries
        $this->default_select = array(
            'id',
            'amount',
            'isCurrent'
        );
    }

}