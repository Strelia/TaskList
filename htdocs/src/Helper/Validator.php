<?php

namespace App\Helper;


use Symfony\Component\HttpFoundation\Response;

class Validator
{

    static function checkNum($num, $field) {
        if (!is_numeric($num)) {
            throw new ApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                "",
                [
                    "field" => "{$field} type is not int"
                ]
            );
        }
    }

    static function checkEmptyData($str, $field) {
        if (empty($str)) {
            throw new ApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                "",
                [
                    "field" => "${field} is empty"
                ]
            );
        }
    }

    static function checkEmptyObject($str, $field) {
        if (empty($str)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, "${field} is empty");
        }
    }

    static function checkIssetData(&$data, $field) {
        if (!isset($data)) {
            throw new ApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                "",
                [
                    "field" => "${field} does not exist"
                ]
            );
        }
    }

    static function checkCondition($condition, $message = "", $errors = []) {
        if ($condition) {
            throw new ApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $message,
                $errors
            );
        }
    }

    static function checkTime($time1, $time2) {
        if (($time1 - $time2) > 0) {
            throw new ApiException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                "{$time1} < $time2"
            );
        }
    }
}