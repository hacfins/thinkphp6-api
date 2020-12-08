<?php

namespace app\api\model\rbac;

use app\api\model\Base;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use app\common\traits\Instance;
use function GuzzleHttp\Psr7\str;

/*
 * 用户基本信息表
 *
 * 1、缓存:
 *     key: user_name
 */

class User extends Base
{
    protected $_lk = 'user_name';

    use Instance;

    //只读字段
    protected $readonly = ['user_name'];

    //自动时间
    protected $autoWriteTimestamp = 'datetime';

    // +--------------------------------------------------------------------------
    // |  基本操作
    // +--------------------------------------------------------------------------
    /**
     * 添加用户
     *
     * @param string      $user_name   用户名
     * @param string|null $nick_name   昵称
     * @param int         $sex         性别
     * @param string      $avator      头像完整地址
     * @param int         $adcode      区级代码
     * @param string      $birthday    生日
     * @param string      $description 描述信息
     *
     */
    public function Add(
        string $user_name, string $nick_name = null, string $full_name = '', int $sex = USER_SEX_UNKOWN, string $avator = '',
        int $adcode = 0, string $company = '', string $birthday = '1970-01-01 00:00:00', string $description = '')
    {
        if (!isset($nick_name) || empty($nick_name))
        {
            // 昵称
            if ($full_name)
            {
                $nick_name = $full_name;
            }
            else
            {
                $nick_name = $user_name;
            }
        }

        if (!isset($full_name) || empty($full_name))
        {
            $full_name = $nick_name;
        }

        self::create([
            'user_name'   => $user_name,
            'full_name'   => $full_name,
            'nick_name'   => $nick_name,
            'sex'         => $sex,
            'avator'      => $avator,
            'company'     => $company,
            'adcode'      => $adcode,
            'birthday'    => $birthday,
            'description' => $description,

            'status' => USER_STATUS_ENABLED,
            'reg_ip' => ip2long(request()->ip()),
        ]);
    }

    /**
     * 修改用户
     *
     * @param string      $user_name
     * @param string|null $nick_name
     * @param int|null    $sex
     * @param string|null $avator
     * @param int|null    $adcode
     * @param string|null $birthday
     * @param string|null $description
     *
     * @return int|string
     */
    public function Modify(
        string $user_name, string $nick_name = null, string $full_name = null, int $sex = null,
        string $avator = null, int $adcode = null, string $company = null, string $birthday = null, string $description = null)
    {
        $data = [];

        if (isset($nick_name))
        {
            $data['nick_name'] = $nick_name;
        }
        if (isset($full_name))
        {
            $data['full_name'] = $full_name;
        }
        if (isset($sex))
        {
            $data['sex'] = $sex;
        }
        if (isset($avator))
        {
            $data['avator'] = $avator;
        }
        if (isset($adcode))
        {
            $data['adcode'] = $adcode;
        }
        if (isset($company))
        {
            $data['company'] = $company;
        }
        if (isset($birthday))
        {
            $data['birthday'] = $birthday;
        }
        if (isset($description))
        {
            $data['description'] = $description;
        }

        //同步缓存
        $this->Cache_Rm($user_name . CACHE_WITHTRASHED);

        return $this->Db_Update($user_name, ['user_name' => $user_name], $data);
    }

    /**
     * 修改用户图像
     *
     * @param string $user_name
     * @param string $avator
     *
     * @return int|string
     */
    public function ModifyAvator(string $user_name, string $avator)
    {
        return $this->Modify($user_name, null, null, null, $avator);
    }

    /**
     * 软删除
     *
     * @param array $user_names 用户名
     *
     * @return int
     */
    public function Dels(array $user_names)
    {
        //同步缓存
        foreach ($user_names as $user_name)
        {
            $this->Cache_Rm($user_name);
            $this->Cache_Rm($user_name . CACHE_WITHTRASHED);
        }

        return self::destroy(function ($query) use ($user_names) {
            $query->where('user_name', 'IN', $user_names);
        });
    }

    /**
     * 禁用用户
     *
     * @param array $user_names 用户名
     *
     * @return int
     */
    public function Disabled(array $user_names)
    {
        //同步缓存
        foreach ($user_names as $user_name)
        {
            $this->Cache_Rm($user_name);
            $this->Cache_Rm($user_name . CACHE_WITHTRASHED);

            $this->Db_Update($user_name, ['user_name' => $user_name],['status' => USER_STATUS_DISABLED]);
        }

        return true;
    }

    /**
     * 启用用户
     *
     * @param array $user_names 用户名
     *
     * @return int
     */
    public function Enabled(array $user_names)
    {
        //同步缓存
        foreach ($user_names as $user_name)
        {
            $this->Cache_Rm($user_name);
            $this->Cache_Rm($user_name . CACHE_WITHTRASHED);

            $this->Db_Update($user_name, ['user_name' => $user_name],['status' => USER_STATUS_ENABLED]);
        }

        return true;
    }

    /**
     * 获取用户信息
     *
     * @param string $user_name   用户名
     * @param bool   $withTrashed 是否包含删除数据
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetInfo($user_name, bool $withTrashed = false)
    {
        $field = [
            'nick_name',
            'full_name',
            'sex',
            'avator',
            'adcode',
            'company',
            'birthday',
            'description',
            'reg_ip',
            'status',
            'create_time',
        ];

        if ($withTrashed)
        {
            $user = self::withTrashed()->field($field)
                ->cache($this->Cache_Key($user_name . CACHE_WITHTRASHED), CACHE_TIME_SQL)
                ->where(['user_name' => $user_name])
                ->find();
        }
        else
        {
            $user = $this->field($field)
                ->cache($this->Cache_Key($user_name), CACHE_TIME_SQL)
                ->where(['user_name' => $user_name])
                ->find();
        }

        if (!$user)
        {
            return [];
        }

        $info = $user->toArray();

        return $info;
    }

    /**
     * 获取用户头像路径
     *
     * @param string $user_name 用户名
     *
     * @return string
     */
    public function GetAvator(string $user_name)
    {
        $info = $this->GetInfo($user_name, true);

        return $info['avator'] ?? '';
    }

    /**
     * 获取用户昵称
     *
     * @param string $user_name 用户名
     *
     * @return string
     */
    public function GetNickName(string $user_name)
    {
        $info = $this->GetInfo($user_name, true);

        return $info['nick_name'] ?? '';
    }

    /**
     * 获取用户昵称
     *
     * @param string $user_name 用户名
     *
     * @return string
     */
    public function GetFullName(string $user_name)
    {
        $info = $this->GetInfo($user_name, true);

        return $info['full_name'] ?? '';
    }


    /**
     * 检查用户名是否存在
     *
     * @param string $user_name  要检查的内容
     * @param string $exceptName 要排除的用户名
     *
     * @return bool
     */
    public function CheckExist(string $user_name, string $exceptName = null)
    {
        $info = $this->GetInfo($user_name);
        if (!$info)
        {
            return false;
        }

        if (isset($exceptName) && ($user_name == $exceptName))
        {
            return false;
        }

        return true;
    }

    /**
     * 用户列表
     * left join UserAuth
     *
     * @param string|null $user_name_key
     * @param string|null $full_name_key
     * @param string|null $phoneKey
     * @param int|null    $sex
     * @param null        $create_st
     * @param null        $create_et
     * @param bool        $all 包含删除的数据
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetList(
        string $user_name_key = null, string $full_name_key = null, string $phoneKey = null, int $sex = null,
        $create_st = null, $create_et = null, bool $all = false, string $orderField = null, int $isASC = YES, $page = DEF_PAGE, $pageSize = DEF_PAGE_SIZE)
    {
        $map   = [];
        $joins = [];
        $order = [];

        if (isset($user_name_key))
        {
            //对 '_' 进行转义
            $user_nameKey = str_replace('_', '\_', $user_name_key);

            $map[] = [
                'u.user_name',
                'like',
                "%{$user_nameKey}%",
            ];
        }

        if (isset($full_name_key))
        {
            //对 '_' 进行转义
            $full_nameKey = str_replace('_', '\_', $full_name_key);

            $map[] = [
                'u.full_name',
                'like',
                "%{$full_nameKey}%",
            ];
        }

        if (isset($orderField))
        {
            $sortType = $isASC == YES ? 'ASC' : 'DESC';
            if ($orderField == 'create_time')
            {
                $order['u.id'] = $sortType;
            }
        }

        if (isset($phoneKey))
        {
            //对 '_' 进行转义
            $phoneKey = str_replace('_', '\_', $phoneKey);

            $map[] = [
                'ua.phone',
                'like',
                "%{$phoneKey}%",
            ];

            $joins[] = [
                'user_auth ua',
                'u.user_name=ua.user_name',
                'left',
            ];
        }

        if (isset($sex))
        {
            $map[] = [
                'u.user_name',
                '=',
                $sex];
        }

        if (isset($create_st) || isset($create_et))
            sql_map_region($map, 'u.create_time', $create_st, $create_et);

        if (!$all)
        {
            //查询字段是否（不）是Null
            //!!! 不是值
            $map[] = [
                'u.delete_time',
                'NULL',
                null,
            ];
        }

        $count = $this->withTrashed()
            ->alias('u')
            ->where($map)
            ->joins($joins)
            ->count();

        if (0 == $count)
        {
            $list = [

            ];
        }
        else
        {
            $list = $this->withTrashed()
                ->field([
                    'u.user_name',
                ])
                ->alias('u')
                ->where($map)
                ->joins($joins)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

            $list = $list->toArray();

            //用户信息
            foreach ($list as &$item)
            {
                $info = $this->GetInfo($item['user_name'], true);
                $item = array_merge($item, $info);
            }
        }

        return [
            $list,
            $count,
        ];
    }

    // +--------------------------------------------------------------------------
    // |  批量导入
    // +--------------------------------------------------------------------------
    /***
     * 读取文件中的数据
     *
     * @param string $sourceFile
     * @param int    $startRowNo 有效数据的开始行号
     * @param int    $colMaxNum  总列表
     *
     * @return array
     * @throws \Exception
     */
    public function ReadFile(string $sourceFile, int $startRowNo = 2, int $colMaxNum = 6)
    {
        try
        {
            $excelObj = IOFactory::load($sourceFile);

            //读第一个sheet
            $sheet = $excelObj->setActiveSheetIndex(0);

            //1.0 校验行数、列数是否合法
            $colNum = count($sheet->getColumnDimensions());

            //A：用户名|B：姓名|C：手机号|D：邮箱|E：性别|F：机构名称
            //A：用户名|B：姓名|C：手机号|H：邮箱|D：机构名称
            if ($colNum < $colMaxNum)
            {
                E(\EC::FILE_COLS_ERROR, "表格必须为{$colNum}列（A：用户名|B：姓名|C：手机号等）", false);
            }

            $rowNum = $sheet->getHighestRow();

            //2.0 开始读取数据
            $maxCol = $sheet->getHighestColumn();
            $rows   = $sheet->rangeToArray("A{$startRowNo}:" . $maxCol . $rowNum, '');

            //释放资源
            $excelObj->disconnectWorksheets();
            unset($excelObj);

            return $rows;
        }
        catch (\Throwable $e)
        {
            if (isset($excelObj))
            {
                //释放资源
                $excelObj->disconnectWorksheets();
                unset($excelObj);
            }

            E($e->getCode(), $e->getMessage());
        }
    }

    /***
     * 标记不合法的数据
     *
     * @param               $rows
     *
     * @param int           $startRowNo
     * @param int           $userNameIndex
     * @param int           $nameIndex
     * @param int           $phoneIndex
     * @param int           $emailIndex
     * @param               $sexIndex
     * @param int           $companyIndex
     *
     * @return array 不合法的数据
     *      return [
     *      'errName'     => $errName,
     *      'errFullName' => $errFullName
     *      'errEmail'    => $errEmail,
     *      'errPhone'    => $errPhone,
     *      'errCompany'  => $errCompany,
     *      ];
     */
    public function CheckValue(&$rows, int $startRowNo,
        int $userNameIndex, int $nameIndex, int $phoneIndex, int $emailIndex, $sexIndex, int $companyIndex)
    {
        $errNone = $errName = $errFullName = $errPhone = $errEmail = $errCompany = [];
        foreach ($rows as $key => &$row)
        {
            $name     = strtolower(trim($row[$userNameIndex]));
            $fullName = trim($row[$nameIndex]);
            $phone    = trim($row[$phoneIndex]);
            $email    = strtolower(trim($row[$emailIndex]));
            $sex      = isset($row[$sexIndex]) ? trim($row[$sexIndex]) : '';
            $company  = trim($row[$companyIndex]);

            //删除空行
            if ($name == '' && $fullName == '' && $phone == '' && $email == '' && $sex == '' && $company == '')
            {
                $errNone[] = $key + $startRowNo;
                continue;
            }

            //检查用户名
            if (!validate_username($name))
            {
                $errName[] = $key + $startRowNo;
            }

            //检查姓名
            if (($fullName != '') && (mb_strlen($fullName) > 20 || mb_strlen($fullName) < 2))
            {
                $errFullName[] = $key + $startRowNo;
            }

            //检查手机号
            if (($phone != '') && !validate_phone($phone))
            {
                $errPhone[] = $key + $startRowNo;
            }

            //检查邮箱
            if (($email != '') && !validate_email($email))
            {
                $errEmail[] = $key + $startRowNo;
            }

            //检查性别
            if ($sex == '男')
            {
                $sex = USER_SEX_MAN;
            }
            else if ($sex == '女')
            {
                $sex = USER_SEX_WOMEN;
            }
            else
            {
                $sex = USER_SEX_UNKOWN;
            }

            $row[$userNameIndex] = $name;
            $row[$nameIndex]     = $fullName;
            $row[$phoneIndex]    = $phone;
            $row[$emailIndex]    = $email;
            $row[$sexIndex]      = $sex;
            $row[$companyIndex]  = $company;
        }

        return [
            'errNone'     => $errNone,
            'errName'     => $errName,
            'errFullName' => $errFullName,
            'errEmail'    => $errEmail,
            'errPhone'    => $errPhone,
        ];
    }

    /***
     * 删除错误的数据
     *
     * @param       $rows
     * @param int   $startRowNo
     * @param array $errArray
     */
    public function DeleteErrData(&$rows, int $startRowNo, array $errArray)
    {
        // 合并错误类型
        $all = [];
        foreach ($errArray as $key => $v)
        {
            if ($v)
            {
                $all = array_merge($all, $v);
            }
        }

        if ($all)
        {
            $all = array_unique($all);
            sort($all, SORT_NUMERIC);

            // 删除错误数据
            foreach ($all as $v)
            {
                unset($rows[$v - $startRowNo]);
            }
        }
    }

    /**
     * 生成报表
     *
     * @param string $sourceFile xls文件路径
     * @param array  $errArray   错误信息
     * @param        $startRowNo
     * @param int    $startPCol  内容写入的起始列
     *
     * @return mixed
     * @throws \Exception
     * @author jiangjiaxiong
     */
    public function BatchReport($sourceFile, $errArray, $startRowNo, $startPCol)
    {
        try
        {
            $excelObj = IOFactory::load($sourceFile);

            //读第一个sheet
            $sheet = $excelObj->setActiveSheetIndex(0);

            /////////////////////////////////////////////////////////////////////////////////////////////
            //s1.1 整理错误信息
            $errRows = [];
            foreach ($errArray as $key => $errs)
            {
                foreach ($errs as $row)
                {
                    $errMsg = '';
                    switch ($key)
                    {
                        case 'errNone':
                            $errMsg = '';
                            break;
                        case 'errName':
                            $errMsg = '用户名为空或格式错误';
                            break;
                        case 'errFullName':
                            $errMsg = '姓名格式错误（长度2-20）';
                            break;
                        case 'errPhone':
                            $errMsg = '手机号格式错误';
                            break;
                        case 'errEmail':
                            $errMsg = '邮箱格式错误';
                            break;
                        case 'errDBNameDup':
                            $errMsg = '用户名已被注册';
                            break;
                        case 'errDBPhoneDup':
                            $errMsg = '手机号已被注册';
                            break;
                        case 'errDBEmailDup':
                            $errMsg = '邮箱已被注册';
                            break;
                        case 'errDBOther':
                            $errMsg = '数据保存失败(请重试)';
                            break;
                        default:
                            break;
                    }

                    if (!isset($errRows[$row]))
                    {
                        $errRows[$row] = $errMsg . ' ';
                    }
                    else
                    {
                        $errRows[$row] .= '- ' . $errMsg . ' ';
                    }
                }

            }
            ksort($errRows);

            //s1.2 写入错误信息
            foreach ($errRows as $row => $errMsg)
            {
                if ($errMsg && !in_array($row, $errArray['errNone']))
                {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($startPCol + 1) . $row, $errMsg);
                }
            }

            //s1.3 写入结果（成功还是失败）
            $total      = $sheet->getHighestRow();
            $errRowsKey = array_keys($errRows);
            for ($i = $startRowNo; $i <= $total; $i++)
            {
                if (in_array($i, $errRowsKey))
                {
                    if (!in_array($i, $errArray['errNone']))
                    {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($startPCol) . $i, '失败');

                        $sheet->getStyle(Coordinate::stringFromColumnIndex($startPCol) . $i)
                            ->getFont()
                            ->getColor()
                            ->setARGB(Color::COLOR_RED);
                    }
                }
                else
                {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($startPCol) . $i, '成功');

                    $sheet->getStyle(Coordinate::stringFromColumnIndex($startPCol) . $i)
                        ->getFont()
                        ->getColor()
                        ->setARGB(Color::COLOR_DARKGREEN);
                }

                $sheet->getStyle(Coordinate::stringFromColumnIndex($startPCol) . $i)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }

            //设置属性，防止null
            $properties = $excelObj->getProperties();
            if(!$properties->getCreator())
            {
                $properties->setCreator("华科飞扬");
            }
            if(!$properties->getLastModifiedBy())
            {
                $properties->setLastModifiedBy("华科飞扬");
            }
            $excelObj->setProperties($properties);
            
            /////////////////////////////////////////////////////////////////////////////////////////////
            //实例化Excel写入类
            //获取后缀 .xlsx .xls
            $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
            if(strtolower($ext) == 'xls')
            {
                $writerType = 'Xls';
            }
            else
            {
                $writerType = 'Xlsx';
            }
            $writer = IOFactory::createWriter($excelObj, $writerType);
            $writer->save($sourceFile);

            return $sourceFile;
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }
}