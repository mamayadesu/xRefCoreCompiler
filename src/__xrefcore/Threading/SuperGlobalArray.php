<?php

namespace Threading;

use Threading\Exceptions\SuperGlobalArray\InvalidOperatorException;
use Threading\Exceptions\SuperGlobalArray\InvalidValueTypeException;
use Threading\Exceptions\SuperGlobalArray\ItemIsNotArrayException;
use Threading\Exceptions\SuperGlobalArray\KeyNotFoundException;
use Threading\Exceptions\SuperGlobalArray\UnknownErrorException;
use Threading\Exceptions\SystemMethodCallException;

/**
 * Provides access to super global threaded array. You can get this array from any thread
 *
 * @package Threading
 */

final class SuperGlobalArray
{
    /**
     * @ignore
     */
    private ?Threaded $sga = null;

    /**
     * @ignore
     */
    private static ?SuperGlobalArray $instance = null;

    /**
     * @ignore
     */
    public function __construct()
    {
        if (self::$instance != null)
        {
            $e = new SystemMethodCallException("This class cannot be initialized");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        self::$instance = $this;
    }

    /**
     * @ignore
     */
    public function __setSga(Threaded $sga) : void
    {
        if ($this->sga != null || $sga->GetClassName() != "\\Threading\\__SuperGlobalArrayThread")
        {
            $e = new SystemMethodCallException("This class cannot be initialized");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->sga = $sga;
    }

    /**
     * Returns instance of super global array
     *
     * @return SuperGlobalArray
     */
    public static function GetInstance() : ?SuperGlobalArray
    {
        if (self::$instance == null && !defined("SHUTTINGDOWN"))
        {
            if (DEV_MODE) echo "Initializing super global array thread...\n";
            $superglobalarraythreaded = __SuperGlobalArrayThread::Run([], new \stdClass());

            if (DEV_MODE) echo "Initializing super global array...\n";
            $superglobalarray = new SuperGlobalArray();
            $superglobalarray->__setSga($superglobalarraythreaded);
        }
        return self::$instance;
    }

    /**
     * @ignore
     */
    public function GetPid() : int
    {
        return $this->sga->GetChildPid();
    }

    /**
     * @ignore
     */
    public function GetPort() : int
    {
        return $this->sga->GetChildPort();
    }

    /**
     * @ignore
     */
    public function ____getthread() : ?Threaded
    {
        return $this->sga;
    }

    /**
     * Returns value of array key
     *
     * @param array $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"]["foo"]["bar"], use this: ["hello", "world", "foo", "bar"]
     * @return mixed
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Get(array $keys)
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Get($keys, "arr");
        if ($result["t"] != "r")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "r")
        {
            return $result["r"];
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * Sets new value for key
     *
     * @param array $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"]["foo"]["bar"], use this: ["hello", "world", "foo", "bar"]
     * @param $value mixed Value
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Set(array $keys, $value) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Set($keys, $value, "arr");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * Addes value to array with numeric index
     *
     * @param array $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"][] = $value, use this: Add(["hello", "world"], $value)
     * @param $value mixed Value
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Add(array $keys, $value) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Add($keys, $value, "arr");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * Returns TRUE if item with same key exists
     *
     * @param array $keys Path to value
     * @return bool
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function IsSet(array $keys) : bool
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->IsSet($keys, "arr");
        if ($result["t"] != "r")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "r")
        {
            return $result["r"];
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * Deletes item in array, like native PHP `unset()` function
     *
     * @param array $keys Path to value
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Unset(array $keys) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Unset($keys, "arr");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * Executes operator with array item
     *
     * @param array $keys Path to value
     * @param string $operator Required operator. Available operators: ".=", "+=", "-=", "*=", "/=", "++", "--"
     * @param mixed $value Value for operator. Isn't using in "++" and "--" operators
     * @throws InvalidOperatorException
     * @throws InvalidValueTypeException
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     */
    public function Operator(array $keys, string $operator, $value = "")
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Operand($keys, $operator, $value, "arr");
        if ($result["t"] != "r" && $result["t"] != "ivt" && $result["t"] != "incop")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }
        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }
        if ($result["t"] == "ivt")
        {
            $ivt = new InvalidValueTypeException("Invalid type of value. Expected: " . implode(", ", $result["et"]) . ". Given " . $result["gt"]);
            $ivt->ExpectedTypes = $result["et"];
            $ivt->GivenType = $result["gt"];
            $ivt->Operator = $result["o"];
            $ivt->Operators = $result["ops"];
            $ivt->Value = $result["v"];
            $ivt->__xrefcoreexception = true;
            throw $ivt;
        }
        if ($result["t"] == "incop")
        {
            $incop = new InvalidOperatorException("Invalid operator '" . $result["o"] . "'. Available operators: " . implode(", ", $result["ops"]));
            $incop->Value = $result["v"];
            $incop->Operator = $result["o"];
            $incop->Operators = $result["ops"];
            $incop->__xrefcoreexception = true;
            throw $incop;
        }
    }

    // ACCESS TO SYSTEM ARRAY, DO NOT USE THIS!

    /**
     * @ignore
     */
    public function __sysGet(array $keys)
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Get($keys, "sys");
        if ($result["t"] != "r")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "r")
        {
            return $result["r"];
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * @ignore
     */
    public function __sysSet(array $keys, $value) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Set($keys, $value, "sys");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * @ignore
     */
    public function __sysAdd(array $keys, $value) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Add($keys, $value, "sys");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * @ignore
     */
    public function __sysIsSet(array $keys) : bool
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->IsSet($keys, "sys");
        if ($result["t"] != "r")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "r")
        {
            return $result["r"];
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * @ignore
     */
    public function __sysUnset(array $keys) : void
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Unset($keys, "sys");
        if ($result["t"] != "s")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }

        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }

        if ($result["t"] == "s")
        {
            return;
        }
        $e = new UnknownErrorException("An unknown error occurred");
        $e->__xrefcoreexception = true;
        throw $e;
    }

    /**
     * @ignore
     */
    public function __sysOperator(array $keys, string $operator, $value = "")
    {
        /** @var __SuperGlobalArrayThread $sgac */ $sgac = $this->sga->GetChildThreadedObject();
        $result = $sgac->Operand($keys, $operator, $value, "sys");
        if ($result["t"] != "r" && $result["t"] != "ivt" && $result["t"] != "incop")
        {
            $arrayPath = "";
            foreach ($result["pk"] as $passed_key)
            {
                $arrayPath .= "[" . (is_int($passed_key) ? $passed_key : "\"" . str_replace("\"", "\\\"", $passed_key) . "\"") . "]";
            }
        }
        if ($result["t"] == "iina")
        {
            $iina = new ItemIsNotArrayException("Item '" . $result["k"] . "' (" . gettype($result["k"]) . ") in Array" . $arrayPath . " is " . $result["cot"]);
            $iina->Key = $result["k"];
            $iina->PassedKeys = $result["pk"];
            $iina->Type = $result["cot"];
            $iina->__xrefcoreexception = true;
            throw $iina;
        }
        if ($result["t"] == "kne")
        {
            $kne = new KeyNotFoundException("Key '" . $result["k"] . "' (" . gettype($result["k"]) . ") not found in " . $arrayPath);
            $kne->Key = $result["k"];
            $kne->PassedKeys = $result["pk"];
            $kne->__xrefcoreexception = true;
            throw $kne;
        }
        if ($result["t"] == "ivt")
        {
            $ivt = new InvalidValueTypeException("Invalid type of value. Expected: " . implode(", ", $result["et"]) . ". Given " . $result["gt"]);
            $ivt->ExpectedTypes = $result["et"];
            $ivt->GivenType = $result["gt"];
            $ivt->Operator = $result["o"];
            $ivt->Operators = $result["ops"];
            $ivt->Value = $result["v"];
            $ivt->__xrefcoreexception = true;
            throw $ivt;
        }
        if ($result["t"] == "incop")
        {
            $incop = new InvalidOperatorException("Invalid operator '" . $result["o"] . "'. Available operators: " . implode(", ", $result["ops"]));
            $incop->Value = $result["v"];
            $incop->Operator = $result["o"];
            $incop->Operators = $result["ops"];
            $incop->__xrefcoreexception = true;
            throw $incop;
        }
    }
}