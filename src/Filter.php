<?php
/**
 * Created by lobtao.
 */

namespace workermvc;


class Filter {
    /**
     * Filter the String with default functions
     *
     * @param string $body
     * @return string
     */
    public static function filt($body) {
        $filters = config("think.default_filter");
        $filters = explode(",", $filters);
        foreach ($filters as $filter) {
            $filter = trim($filter);
            !function_exists($filter) or $body = $filter($body);
        }
        return $body;
    }
}