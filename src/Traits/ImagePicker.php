<?php namespace Joanvt\ImagePicker\Traits;



trait ImagePicker {


    public function __construct($options=array()){

        parent::__construct($options);
    }

    public function load(){

        echo "HI111";
    }

}