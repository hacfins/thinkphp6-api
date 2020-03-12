<?php

namespace office;

use PhpOffice\PhpSpreadsheet\
{IOFactory, Spreadsheet, Style\Alignment, Style\Border};

/**
 * Excel 写入类
 */
class ExcelWrite
{
    private $_spreadSheet = null;

    public function __construct()
    {
        $this->_spreadSheet = new Spreadsheet();
    }

    public function __destruct()
    {
        if ($this->_spreadSheet)
        {
            $this->_spreadSheet->disconnectWorksheets();
            unset($this->_spreadSheet);
        }
    }

    /**
     * 设置标题
     *
     * @param array $nameArr 标题列表
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function SetHeader(array $nameArr = [])
    {
        //计算列数量
        $count = count($nameArr);

        if ($count > 0)
        {
            $sheet = $this->_spreadSheet->getActiveSheet();

            $styleArray = [
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color'       => ['argb' => '666666'],
                    ],
                ],
                'font'      => [
                    'bold' => true,
                    'size' => 12
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ];

            // 数字转字母从65开始，循环设置表头
            for ($i = 65; $i < $count + 65; $i++)
            {
                $pCoordinate = strtoupper(chr($i));
                $sheet->setCellValue($pCoordinate . '1', $nameArr[$i - 65]);

                //固定列宽
                $sheet->getColumnDimension($pCoordinate)->setWidth(40);

                //设置单元格样式
                $sheet->getStyle($pCoordinate . '1')->applyFromArray($styleArray);
            }
        }
    }

    /**
     * 写入数据
     *
     * @param array $dataArr
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function Write($dataArr = [])
    {
        $sheet = $this->_spreadSheet->getActiveSheet();

        $styleArrayBody = [
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_NONE,
                    'color'       => ['argb' => '666666'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ];

        $startRow = $sheet->getHighestRow() + 1;

        $columnDimensions = $sheet->getColumnDimensions();
        $columnCnt        = count($columnDimensions);

        foreach ($dataArr as $rowIndex => $dataItem)
        {
            //$key+2,因为第一行是表头，所以写到表格时从第二行开始写
            for ($i = 65; $i < $columnCnt + 65; $i++)
            {
                $pCoordinate = strtoupper(chr($i)) . ($rowIndex + $startRow);

                //数字转字母从65开始：
                $sheet->setCellValue($pCoordinate, $dataItem[$i - 65]);

                //添加所有边框/居中
                $sheet->getStyle($pCoordinate)->applyFromArray($styleArrayBody);
            }
        }
    }

    /**
     * 保存数据
     *
     * @param string $dir 保存的路径
     *
     * @return string 成功返回保存后的全路径
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function Save(string $dir = DIR_TEMPS)
    {
        if (!is_dir($dir))
            mk_dir($dir);
        $filePath = $dir . guid() . '.xlsx';

        $writer = IOFactory::createWriter($this->_spreadSheet, 'Xlsx');
        $writer->save($filePath);

        return $filePath;
    }
}