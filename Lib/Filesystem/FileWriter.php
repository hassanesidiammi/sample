<?php

/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class FileWriter {
    const TEMPLATE_PATH = '/app/bin/export_template.phar';
    const FILE_TYPE     = 'Excel5';

    /** @var PHPExcel excelObject */
    protected $excelObject;
    protected $excelWriter;
    protected $style;

    public function __construct() {
        $templatePath      = Configuration::get('baseDir').self::TEMPLATE_PATH;
        $objReader         = PHPExcel_IOFactory::createReader(self::FILE_TYPE);
        $this->excelObject = $objReader->load($templatePath);
        $this->style       = $this->excelObject->getActiveSheet()->getStyle('A1');

        // dd($this->style);
    }

    public function write($data, $fileName, $sheetTitle=false) {
        $sheetTitle = $sheetTitle ?: $fileName;
        $this->excelObject->removeSheetByIndex(0);
        $this->excelObject->createSheet(0);
        $this->excelObject->setActiveSheetIndex(0);
        $this->excelObject->getActiveSheet()->setTitle(substr($sheetTitle, 0, 31));

        $line = 0;

        foreach ($data as $rows) {
            $i  = -1;
            $line++;
            foreach ($rows as $row) {
                $i++;
                $styleName = (!empty($row['style']) && in_array($row['style'], ['normal', 'title', 'highlighted', ])) ? $row['style'] : 'normal';
                switch ($styleName ) {
                    case 'title':
                        $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->applyFromArray([
                                'fill' => [
                                    'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => ['argb' => 'FFE20030']
                                ],
                                'alignment' => [
                                    'horizontal' => empty($row['center']) ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER : PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                                    'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP,
                                ]
                            ]
                        );
                        $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getAlignment()->setVertical(
                            PHPExcel_Style_Alignment::VERTICAL_TOP
                        );
                        $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getAlignment()->setVertical(
                            PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                        );
                        break;
                    case 'highlighted':
                        $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->applyFromArray([
                                'fill' => [
                                    'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                                'alignment' => [
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                ],
                                'font' => ['color' => ['argb' => 'FFFFFFFF']],
                            ]
                        );
                        break;
                    default :
                        $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->applyFromArray([
                                'fill' => [],
                                'alignment' => [
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                ],
                            ]
                        );
                }

                $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getFont()->setBold(!empty($row['bold']));

                $this->excelObject->getActiveSheet()->getCell();
                if (!empty($row['float'])){
                    $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getAlignment()->setHorizontal(
                        'left' == $row['float'] ? PHPExcel_Style_Alignment::HORIZONTAL_LEFT :PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                    );
                }
                if (!empty($row['multiline'])){
                    $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getAlignment()->setWrapText(true);
                }
                $this->excelObject->getActiveSheet()->setCellValueByColumnAndRow($i, $line, $row['value']);
                if (!empty($row['mergeNext'])){
                    $this->excelObject->getActiveSheet()->mergeCellsByColumnAndRow($i, $line, $i+$row['mergeNext'], $line);
                    $this->excelObject->getActiveSheet()->getStyleByColumnAndRow($i, $line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $i += $row['mergeNext'];
                }
            }
        }

        if(26 > count($data[0])) {
            foreach (range('A', chr(65 + count($data[0]))) as $coordinate){
                $this->excelObject->getActiveSheet()->getColumnDimension($coordinate)->setWidth(13);
                $this->excelObject->getActiveSheet()->getRowDimension($coordinate)->setVisible(true);
            }
        }

        $this->excelWriter = PHPExcel_IOFactory::createWriter($this->excelObject, self::FILE_TYPE);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

        @$this->excelWriter->save('php://output');
        die;
    }
}
