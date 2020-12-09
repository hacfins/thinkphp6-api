<?php

/**
 * Created by IntelliJ IDEA.
 * User: hk
 * Date: 2018/11/21
 * Time: 18:20
 */
class SmoothWarmingUp
{
    private $timestamp;
    public  $capacity; //桶的总容量
    public  $rate;//token 流出的速度
    public  $token;//当前容量

    public function __construct()
    {
        $this->timestamp = time();
        $this->capacity  = 30;
        $this->rate      = 5;
    }

    public function grant()
    {
        $now             = time();
        $this->token     = max(0, $this->token - ($now - $this->timestamp) * $this->rate);
        $this->timestamp = $now;

        if ($this->token + 1 < $this->capacity)
        {
            $this->token++;

            return true;
        }

        return false;
    }
}

$bucket = new SmoothWarmingUp();
for ($i = 0; $i < 50; $i++)
{
    echo $i . "\t" . var_dump($bucket->grant());
}

for ($i = 0; $i < 50; $i++)
{
    echo $i . "\t" . var_dump($bucket->grant());
    sleep(1);
}