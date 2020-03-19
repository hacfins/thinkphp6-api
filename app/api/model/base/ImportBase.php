<?php

namespace app\api\model\base;

use app\api\model\Base;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

/*
 * 导入信息表
 */

class ImportBase extends Base
{
    protected $_colNum   = 2;
    protected $_colError = '表格必须为2列（A：手机号|B：姓名）';
    protected $_errArr   = [
        'errNone'       => '',
        'errName'       => '用户名为空或格式错误',
        'errFullName'   => '姓名格式错误（长度2-20）',
        'errPhone'      => '手机号格式错误',
        'errEmail'      => '邮箱格式错误',
        'errDBNameDup'  => '用户名已被注册',
        'errDBPhoneDup' => '手机号已被注册',
        'errDBEmailDup' => '邮箱已被注册',
        'errDBOther'    => '数据保存失败(请重试)',
    ];

    // +--------------------------------------------------------------------------
    // |  批量导入
    // +--------------------------------------------------------------------------
    /***
     * 读取文件中的数据
     *
     * @param $sourceFile
     * @param $readFirstRow
     *
     * @return array
     */
    public function ReadFile($sourceFile)
    {
        try
        {
            $excelObj = IOFactory::load($sourceFile);

            //读第一个sheet
            $sheet = $excelObj->setActiveSheetIndex(0);

            //1.0 校验行数、列数是否合法
            $colNum = count($sheet->getColumnDimensions());

            //A：手机号|B：姓名
            if ($colNum < $this->_colNum)
            {
                E(\EC::FILE_COLS_ERROR, $this->_colError, false);
            }

            $rowNum = $sheet->getHighestRow();

            //2.0 开始读取数据
            $maxCol = $sheet->getHighestColumn();
            $rows   = $sheet->rangeToArray('A2:' . $maxCol . $rowNum, '');

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
     * @return array 不合法的数据,例如
     *      return [
     *      'errFullName' => $errFullName
     *      'errPhone'    => $errPhone,
     *      ];
     */
    public function CheckValue(&$rows)
    {

    }

    /***
     * 删除错误的数据
     *
     * @param $rows
     * @param $errArray
     */
    public function DeleteErrData(&$rows, array $errArray)
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
                unset($rows[$v - 2]);
            }
        }
    }

    /**
     * 生成报表
     *
     * @param string $sourceFile xls文件路径
     * @param array  $errArray   错误信息
     * @param int    $startPCol  内容写入的起始列
     *
     * @return mixed
     * @author jiangjiaxiong
     *
     */
    public function BatchReport($sourceFile, $errArray, $startPCol)
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
                    $errMsg = $this->_errArr[$key] ?? '';
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
            for ($i = 2; $i <= $total; $i++)
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

            /////////////////////////////////////////////////////////////////////////////////////////////
            //实例化Excel写入类
            $writer = IOFactory::createWriter($excelObj, 'Xls');
            $writer->save($sourceFile);

            return $sourceFile;
        }
        catch (\Throwable $e)
        {
            E($e->getCode(), $e->getMessage());
        }
    }
}