<?php
/**
 * -- Validator --
 * Contains various different validation methods for different input types
 * All validators take input for strings, and after sanitising, they will validate for the correct data type
 */

namespace Fyp;

class Validator
{
    public function __construct(){}

    /**
     * Takes input for a string and sanitises using filter_var method
     * @param $tainted_string
     * @return mixed|string
     */
    public function sanitiseString($tainted_string){
        $cleaned_string = false;
        if(!empty(filter_var(trim($tainted_string), FILTER_SANITIZE_STRING))){
            $cleaned_string = filter_var(trim($tainted_string), FILTER_SANITIZE_STRING);
        }
        return $cleaned_string;
    }

    /**
     * Takes input for a string and sanitises using filter_var method
     * Then validates the cleaned string contains is an int.
     * @param $tainted_input
     * @return mixed|string
     */
    public function validateInt($tainted_input){
        $validated_int = false;
        if(!empty(filter_var(trim($tainted_input), FILTER_SANITIZE_STRING))){
            $cleaned_string = filter_var(trim($tainted_input), FILTER_SANITIZE_STRING);
            $validated_int = filter_var($cleaned_string, FILTER_VALIDATE_INT);
        }
        return $validated_int;
    }

    /**
     * Takes input for a string and sanitises using filter_var method
     * Then validates the cleaned string contains is an email.
     * @param $tainted_input
     * @return mixed|string
     */
    public function validateEmail($tainted_input){
        $validated_email = false;
        if(!empty(filter_var(trim($tainted_input), FILTER_SANITIZE_STRING))){
            $cleaned_string = filter_var(trim($tainted_input), FILTER_SANITIZE_STRING);
            $validated_email = filter_var($cleaned_string, FILTER_VALIDATE_EMAIL);
        }
        return $validated_email;
    }

}