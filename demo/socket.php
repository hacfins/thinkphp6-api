<?php
/**
 * Created by IntelliJ IDEA.
 * User: hk
 * Date: 2018/11/22
 * Time: 11:01
 */
$fp = fsockopen('ssl://www.baidu.com', 443, $errno, $errstr, 3);  //tcp时，移除ssl://

$out = "GET https://www.baidu.com/s?ie=utf-8&mod=1&isbd=1&isid=b2ea37ea0001c0a6&ie=utf-8&f=8&rsv_bp=1&srcqid=2596091829309085728&tn=50000021_hao_pg&wd=jjj&oq=jjj&rsv_pq=b2ea37ea0001c0a6&rsv_t=8f96FmxJHyLg6xCU8HTe7BepJ0PKbw5tCNmwoqvQKejrw1YOIVH%2BI%2Fql%2BQ6GlP5NvXgvwvHt&rqlang=cn&rsv_enter=0&bs=jjj&rsv_sid=undefined&_ss=1&clist=d92e410c5d7d7d0a%09f89021cb2685a096&hsug=&f4s=1&csor=3&_cr1=28507 HTTP/1.1
Host: www.baidu.com
Connection: keep-alive
Accept: */*
is_xhr: 1
X-Requested-With: XMLHttpRequest
is_referer: https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&srcqid=2596091829309085728&tn=50000021_hao_pg&wd=jjj&oq=php%2520socket%2520%25E7%2599%25BE%25E5%25BA%25A6%25E6%25A3%2580%25E7%25B4%25A2&rsv_pq=fd5ef9dc00022ad8&rsv_t=83aerDH9k5dTp1vsJCRiZPhw0fmmqtrGEcfhSgjx21p4mPG2XZuEKXG2ZKM%2FrPhhkbpTRV05&rqlang=cn&rsv_enter=0&rsv_sug3=4&rsv_sug1=4&rsv_sug7=100&inputT=7273&rsv_sug4=10013&bs=php%20socket%20%E7%99%BE%E5%BA%A6%E6%A3%80%E7%B4%A2
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36
is_pbs: jjj
Referer: https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&srcqid=2596091829309085728&tn=50000021_hao_pg&wd=jjj&oq=jjj&rsv_pq=b2ea37ea0001c0a6&rsv_t=8f96FmxJHyLg6xCU8HTe7BepJ0PKbw5tCNmwoqvQKejrw1YOIVH%2BI%2Fql%2BQ6GlP5NvXgvwvHt&rqlang=cn&rsv_enter=0
Accept-Encoding: gzip, deflate, br
Accept-Language: zh-CN,zh;q=0.9,en;q=0.8
Cookie: BIDUPSID=EC1AEC04D9DDCC9937E057BB5B10190F; PSTM=1542683045; BD_UPN=12314753; BAIDUID=7C72A0B13B3213E9C865CCE4162B0E59:FG=1; H_PS_PSSID=; H_PS_645EC=8f96FmxJHyLg6xCU8HTe7BepJ0PKbw5tCNmwoqvQKejrw1YOIVH%2BI%2Fql%2BQ6GlP5NvXgvwvHt; delPer=0; BD_CK_SAM=1; PSINO=2; BDSVRTM=117; WWW_ST=1542858875183

";
socket_set_blocking($fp, false);

fwrite($fp, $out);
while (!feof($fp))
{
    echo fread($fp, 1024);
    flush();
    ob_flush();
}
fclose($fp);

