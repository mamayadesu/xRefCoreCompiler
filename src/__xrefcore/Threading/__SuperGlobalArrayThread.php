<?php

namespace Threading;

/**
 * Class __SuperGlobalArrayThread
 * @package Threading
 * @ignore
 */

class __SuperGlobalArrayThread extends Thread
{
    private array $arr = array();
    private array $sys = array();

    public function Threaded(array $args) : void
    {
        while ($this->IsParentStillRunning())
        {
            try
            {
                $this->WaitForParentAccess();
            }
            catch (Exceptions\InvalidResultReceivedException $e)
            {

            }
        }
    }

    private  function _Get(array $keys, string $arr) : array
    {
        $data = array(
            "t" => "r" // type: result
        );

        $currentObj = $this->$arr;
        $i = 0;
        $count = count($keys);
        $passed_keys = [];
        foreach ($keys as $k)
        {
            $i++;
            if (isset($currentObj[$k]))
            {
                $currentObj = $currentObj[$k];
                if (!is_array($currentObj))
                {
                    if ($i != $count)
                    {
                        return array("t" => "iina", "k" => $k, "pk" => $passed_keys, "cot" => gettype($currentObj)); // item is not array
                    }
                    break;
                }
            }
            else
            {
                return array("t" => "kne", "k" => $k, "pk" => $passed_keys); // key not exist
            }
            $passed_keys[] = $k;
        }
        $data["r"] = $currentObj;
        return $data;
    }

    private function _Set(array $keys, $value, string $arr, bool $add) : array
    {
        $data = array(
            "t" => "s" // type: success
        );

        $currentObj = $this->$arr;
        $i = 0;
        $count = count($keys);
        $passed_keys = [];
        foreach ($keys as $k)
        {
            $i++;
            if (isset($currentObj[$k]))
            {
                if (!is_array($currentObj[$k]) && $i != $count)
                {
                    return array("t" => "iina", "k" => $k, "pk" => $passed_keys, "cot" => gettype($currentObj[$k])); // item is not array
                }
                if ($i == $count)
                {
                    $arrayPath = "";
                    $passed_keys[] = $k;
                    foreach ($passed_keys as $passed_key)
                    {
                        $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
                    }
                    $execute = "\$this->" . $arr . $arrayPath . ($add ? "[]" : "") . " = \$value;";
                    eval($execute);
                    break;
                }
                $currentObj = $currentObj[$k];
            }
            else
            {
                if ($i == $count)
                {
                    $arrayPath = "";
                    $passed_keys[] = $k;
                    foreach ($passed_keys as $passed_key)
                    {
                        $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
                    }
                    $execute = "\$this->" . $arr . $arrayPath . ($add ? "[]" : "") . " = \$value;";
                    eval($execute);
                    break;
                }
                else
                {
                    return array("t" => "kne", "k" => $k, "pk" => $passed_keys); // key not exist
                }
            }
            $passed_keys[] = $k;
        }
        return $data;
    }

    private function _IsSet(array $keys, string $arr) : array
    {
        $data = array(
            "t" => "r" // type: result
        );

        $currentObj = $this->$arr;
        $i = 0;
        $count = count($keys);
        $passed_keys = [];
        foreach ($keys as $k)
        {
            $i++;
            if (isset($currentObj[$k]))
            {
                $currentObj = $currentObj[$k];
                $data["r"] = true;
                if (!is_array($currentObj))
                {
                    if ($i != $count)
                    {
                        return array("t" => "iina", "k" => $k, "pk" => $passed_keys, "cot" => gettype($currentObj)); // item is not array
                    }
                    break;
                }
            }
            else
            {
                $data["r"] = false;
                break;
            }
            $passed_keys[] = $k;
        }
        return $data;
    }

    private function _Unset(array $keys, string $arr) : array
    {
        $data = array(
            "t" => "s" // type: success
        );

        $currentObj = $this->$arr;
        $i = 0;
        $count = count($keys);
        $passed_keys = [];
        foreach ($keys as $k)
        {
            $i++;
            if (isset($currentObj[$k]))
            {
                if (!is_array($currentObj[$k]) && $i != $count)
                {
                    return array("t" => "iina", "k" => $k, "pk" => $passed_keys); // item is not array
                }
                if ($i == $count)
                {
                    $arrayPath = "";
                    $passed_keys[] = $k;
                    foreach ($passed_keys as $passed_key)
                    {
                        $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
                    }
                    $execute = "\$unset(this->" . $arr . $arrayPath . ");";
                    eval($execute);
                    break;
                }
                $currentObj = $currentObj[$k];
            }
            else
            {
                return array("t" => "kne", "k" => $k, "pk" => $passed_keys, "cot" => gettype($currentObj[$k]));
            }
            $passed_keys[] = $k;
        }
        return $data;
    }

    public function Get(array $keys, string $arr) : array
    {
        return $this->_Get($keys, $arr);
    }

    public function Set(array $keys, $value, string $arr) : array
    {
        return $this->_Set($keys, $value, $arr, false);
    }

    public function Add(array $keys, $value, string $arr) : array
    {
        return $this->_Set($keys, $value, $arr, true);
    }

    public function IsSet(array $keys, string $arr) : array
    {
        return $this->_IsSet($keys, $arr);
    }

    public function Unset(array $keys, string $arr) : array
    {
        return $this->_Unset($keys, $arr);
    }

    public function Operand(array $keys, string $operand, $value, string $arr) : array
    {
        $operands = [".=", "+=", "-=", "*=", "/=", "++", "--"];
        $valtype = gettype($value);
        $numbercheck = ($valtype == "int" || $valtype == "float" || $valtype == "double");
        switch ($operand)
        {
            case ".=":
                if ($valtype != "string")
                {
                    return array("t" => "ivt", "o" => $operand, "ops" => $operands, "v" => $value, "et" => ["string"], "gt" => $valtype);
                }
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] . $value;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "+=":
                if ($numbercheck)
                {
                    return array("t" => "ivt", "o" => $operand, "ops" => $operands, "v" => $value, "et" => ["int", "float", "double"], "gt" => $valtype);
                }
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] + $value;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "-=":
                if ($numbercheck)
                {
                    return array("t" => "ivt", "o" => $operand, "ops" => $operands, "v" => $value, "et" => ["int", "float", "double"], "gt" => $valtype);
                }
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] - $value;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "*=":
                if ($numbercheck)
                {
                    return array("t" => "ivt", "o" => $operand, "ops" => $operands, "v" => $value, "et" => ["int", "float", "double"], "gt" => $valtype);
                }
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] * $value;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "/=":
                if ($numbercheck)
                {
                    return array("t" => "ivt", "o" => $operand, "ops" => $operands, "v" => $value, "et" => ["int", "float", "double"], "gt" => $valtype);
                }
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] / $value;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "++":
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] + 1;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            case "--":
                $data = $this->_Get($keys, $arr);
                if ($data["t"] != "r")
                {
                    return $data;
                }
                $newValue = $data["r"] - 1;
                $this->_Set($keys, $newValue, $arr, false);
                return array("t" => "r", "r" => $newValue);

            default:
                return array("t" => "incop", "o" => $operand, "ops" => $operands, "v" => $value);
        }
    }
}