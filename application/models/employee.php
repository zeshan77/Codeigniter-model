<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends MY_Model
{
    function __construct()
    {
        parent::__construct();

        //Table name
        $this->table = 'employees';

        //Columns
        $this->columns = array(
            'id',
            'name',
            'department',
            'nationality',
            'created_at',
            'deleted_at'
        );

        //By default SELECT these columns in SELECT queries
        $this->default_select = array(
            'id',
            'name',
            'nationality'
        );

    }

}