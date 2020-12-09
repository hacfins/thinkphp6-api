<?php
/**
 * Created by IntelliJ IDEA.
 * User: hk
 * Date: 2018/11/19
 * Time: 17:33
 */
class Person
{
    public $_name;
    public $_gender;

    public function Say()
    {
        echo $this->_name, "\tis", $this->_gender,"\r\n";
    }

    public function __set($name, $value)
    {
        echo "Setting $name to $value \r\n";
        $this->_name = $value;
    }

    public function __get($name)
    {
        if(!isset($name))
            echo '未设置';

        return $this->_name;
    }
}

$student = new Person();
$student->_name = 'Tom';
$student->_gender = 'male';
$student->age = 24;

$reflect = new ReflectionClass(get_class($student));
$props = $reflect->getProperties();
foreach ($props as $prop)
{
    print $prop->getName() . "\n";
}

$methods = $reflect->getMethods();
foreach ($methods as $method)
{
    print $method->getName() . "\n";
}