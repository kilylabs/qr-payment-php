<?php

namespace Kily\Payment\QR;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Gost implements \ArrayAccess
{
    public const PREFIX = 'ST0001';
    public const ENC = '2';
    public const SEP = '|';

    public const REQUIRED = [
        'Name'=>'.{1,160}',
        'PersonalAcc'=>'^[0-9]{1,20}$',
        'BankName'=>'.{1,34}',
        'BIC'=>'^[0-9]{1,9}$',
        'CorrespAcc'=>'^[0-9]{1,20}$',
    ];

    public const ADDITIONAL = [
        'Sum'=>'^[0-9]{0,18}$',
        'Purpose'=>'.{0,210}',
        'PayeeINN'=>'^[0-9]{0,12}$',
        'PayerINN'=>'^[0-9]{0,12}$',
        'DrawerStatus'=>'.{0,2}',
        'KPP'=>'^[0-9]{0,9}$',
        'CBC'=>'^[0-9]{0,20}$',
        'OKTMO'=>'.{0,11}',
        'PaytReason'=>'.{0,2}',
        'TaxPeriod'=>'.{0,10}',
        'DocNo'=>'.{0,15}',
        'DocDate'=>'.{0,10}',//FIXME: need to check date format
        'TaxPaytKind'=>'.{0,2}',
    ];

    public const OTHER = [
        'LastName',
        'FirstName',
        'MiddleName',
        'PayerAddress',
        'PersonalAccount',
        'DocIdx',
        'PensAcc',
        'Contract',
        'PersAcc',
        'Flat',
        'Phone',
        'PayerIdType',
        'PayerIdNum',
        'ChildFio',
        'BirthDate',
        'PaymTerm',
        'PaymPeriod',
        'Category',
        'ServiceName',
        'CounterId',
        'CounterVal',
        'QuittId',
        'QuittDate',
        'InstNum',
        'ClassNum',
        'SpecFio',
        'AddAmount',
        'RuleId',
        'ExecId',
        'RegType',
        'UIN',
        'TechCode'=>[
            '01','02','03','04','05','06','07','08','09','10','11','12','13','14','15'
        ],
    ];

    protected $_attrs = [];
    protected $_throwExceptions = true;
    protected $_validateOnSet = false;

    public function __construct($name = null, $pacc = null, $bankname = null, $bic = null, $cacc = null)
    {
        if (null !== $name) {
            $this->Name = $name;
        }
        if (null !== $pacc) {
            $this->PersonalAcc = $pacc;
        }
        if (null !== $bankname) {
            $this->BankName = $bankname;
        }
        if (null !== $bic) {
            $this->BIC = $bic;
        }
        if (null !== $cacc) {
            $this->CorrespAcc = $cacc;
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetExists($offset)
    {
        return in_array($offset, array_keys($this->_attrs));
    }

    public function offsetUnset($offset)
    {
        unset($this->_attrs[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function __get($name)
    {
        if (!$this->isValidKey($name)) {
            return null;
        }
        return $this->_attrs[$name] ?? null;
    }

    public function __set($name, $value)
    {
        if ($this->isValidKey($name)) {
            $this->isValid($name, $value);
            $this->_attrs[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return isset($this->_attrs[$name]);
    }


    public function render($to_file = false, $options = null)
    {
        if ($options) {
            $options = new QROptions($options);
        } else {
            $options = new QROptions([
                'imageBase64'=>false,
                'imageTransparent'=>false,
            ]);
        }
        $qrcode = new QRCode($options);
        if (false !== $to_file) {
            return $qrcode->render($this->generate(), $to_file);
        }
        return $qrcode->render($this->generate());
    }

    public function generate($validate=true)
    {
        if ($validate && !$this->validate()) {
            return false;
        }
        $astr = [static::PREFIX.static::ENC];
        foreach ([static::REQUIRED,static::ADDITIONAL,static::OTHER] as $set) {
            foreach ($set as $k=>$v) {
                $real_k = is_numeric($k) ? $v : $k;
                if (isset($this->_attrs[$real_k])) {
                    $astr[] = $real_k.'='.$this->_attrs[$real_k];
                }
            }
        }
        return implode(static::SEP, $astr);
    }

    protected function validateInternal($name = null, $value = false)
    {
        $found = false;
        foreach (['REQUIRED'=>static::REQUIRED,'ADDITIONAL'=>static::ADDITIONAL,'OTHER'=>static::OTHER] as $settype=>$set) {
            foreach ($set as $k=>$v) {
                $real_k = is_numeric($k) ? $v : $k;
                if ($name) {
                    if ($real_k !== $name) {
                        continue;
                    }
                    $found = true;
                    if (false === $value) {
                        // we don't need to check value
                        break 2;
                    }
                }
                if (!isset($this->_attrs[$real_k])) {
                    if ($value !== false) {
                        // skip this check on var validation
                    } else {
                        if ($settype === 'REQUIRED') {
                            if ($this->_throwExceptions) {
                                throw new Exception($settype.' attr "'.$real_k.'" does not set');
                            }
                            return false;
                        } else {
                            // oh, it is ok
                            continue;
                        }
                    }
                }
                if ($value !== false) {
                    $aval = $value;
                } else {
                    $aval = $this->_attrs[$real_k];
                }

                if (is_numeric($k)) {
                    // we don't need to check this value
                } else {
                    if (is_array($v)) {
                        if (!in_array($aval, $v)) {
                            if ($this->_throwExceptions) {
                                if ($name && (false !== $value) && !$this->_validateOnSet) {
                                    return false;
                                }
                                throw new Exception($settype.' attr "'.$real_k.'" is not in list: '.implode(',', $v));
                            }
                            return false;
                        }
                    } else {
                        if (!preg_match('/'.$v.'/ui', $aval)) {
                            if ($this->_throwExceptions) {
                                if ($name && (false !== $value) && !$this->_validateOnSet) {
                                    return false;
                                }
                                throw new Exception($settype.' attr "'.$real_k.'" does not match RegExp: '.$v);
                            }
                            return false;
                        }
                    }
                }
            }
        }
        if ($name && !$found) {
            if ($this->_throwExceptions) {
                throw new Exception('Attr "'.$name.'" is not allowed');
            }
            return false;
        }
        return true;
    }

    public function validate()
    {
        return $this->validateInternal();
    }

    public function isValid($name, $value)
    {
        return $this->validateInternal($name, $value);
    }

    public function isValidKey($name)
    {
        return $this->validateInternal($name);
    }

    public function setThrowExceptions($value)
    {
        $this->_throwExceptions = $value;
    }

    public function setValidateOnSet($value)
    {
        $this->_validateOnSet = $value;
    }

    public function listRequired()
    {
        return $this->listAttrs(static::REQUIRED);
    }

    public function listAdditional()
    {
        return $this->listAttrs(static::ADDITIONAL);
    }

    public function listOther()
    {
        return $this->listAttrs(static::OTHER);
    }

    protected function listAttrs($set)
    {
        $tmp = [];
        foreach ($set as $k=>$v) {
            $real_k = is_numeric($k) ? $v : $k;
            $tmp[] = $real_k;
        }
        return $tmp;
    }
}
