<?php
/**
 * Created by lobtao.
 */

namespace workermvc;


class Debug
{
    /**
     * Check syntax error in a PHP file
     *
     * @param string $filename
     * @param string &$error_msg
     * @return bool
     */
    public static function checkPHPSyntax($filename, &$error_msg){
        $file_content = file_get_contents($filename);

        $check_code = "return true; ?>";
        $file_content = $check_code . $file_content . "<?php ";

        try{
            if(!eval($file_content)) {
                $error_msg = "Parse error in ".$filename;
                return false;
            }
        }catch (\Error $e){
            $error_msg = $e->getMessage();
            return false;
        }
        return true;
    }
}