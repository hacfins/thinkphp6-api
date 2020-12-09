<?php
/**
 * Created by IntelliJ IDEA.
 * User: hk
 * Date: 2018/11/29
 * Time: 15:32
 */
function myHash($key)
{
    $md5  = substr(md5($key), 0, 8);
    $seed = 31;
    $hash = 0;

    for ($i = 0; $i < 8; $i++)
    {
        $hash = $hash * $seed + ord($md5{$i});
        $i++;
    }

    return $hash & 0x7FFFFFFF;
}

/**
 * 增加或移除服务器，只会影响该服务器逆时针到下一台服务器之间的数据
 *
 * Class FlexHash
 */
class FlexHash
{
    private $_serverList = [];
    private $_isSorted = false;

    function addServer($server)
    {
        $hash = myHash($server);

        if(!isset($this->_serverList[$hash]))
        {
            $this->_serverList[$hash] = $server;
        }

        $this->_isSorted = false;
        return true;
    }

    function removeServer($server)
    {
        $hash = myHash($server);

        if(isset($this->_serverList[$hash]))
        {
            unset($this->_serverList[$hash]);
        }

        $this->_isSorted = false;
        return true;
    }

    function lookupServer($key)
    {
        $hash = myHash($key);

        if (!$this->_isSorted)
        {
            krsort($this->_serverList, SORT_NUMERIC);
            $this->_isSorted = true;
        }

        foreach ($this->_serverList as $pos => $server)
        {
            if ($hash > $pos)
            {
                return $server;
            }
        }

        return $this->_serverList[count($this->_serverList)-1];
    }
}

$hserver = new FlexHash();
$hserver->addServer('192.168.1.1');
$hserver->addServer('192.168.1.2');
$hserver->addServer('192.168.1.3');
$hserver->addServer('192.168.1.4');
$hserver->addServer('192.168.1.5');

echo "save key1 in server:" , $hserver->lookupServer('key1'), "<br>";
echo "save key2 in server:" , $hserver->lookupServer('key2'), "<br>";

$hserver->removeServer('192.168.1.4');
echo "save key1 in server:" , $hserver->lookupServer('key1'), "<br>";
echo "save key2 in server:" , $hserver->lookupServer('key2'), "<br>";

$hserver->addServer('192.168.1.6');
echo "save key1 in server:" , $hserver->lookupServer('key1'), "<br>";
echo "save key2 in server:" , $hserver->lookupServer('key2'), "<br>";