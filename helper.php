<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mmanager_events
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class SimpleXLSXGen {

    public $curSheet;
    protected $sheets;
    protected $template;
    protected $SI, $SI_KEYS;

    public function __construct() {
        $this->curSheet = -1;
        $this->sheets = [ ['name' => 'Sheet1', 'rows' => [] ] ];
        $this->SI = [];		// sharedStrings index
        $this->SI_KEYS = []; //  & keys
        $this->template = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Override PartName="/_rels/.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
<Override PartName="/xl/_rels/workbook.xml.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
{SHEETS}
<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>',
            'docProps/app.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
<TotalTime>0</TotalTime>
<Application>'.__CLASS__.'</Application></Properties>',
            'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<dcterms:created xsi:type="dcterms:W3CDTF">{DATE}</dcterms:created>
<dc:language>en-US</dc:language>
<dcterms:modified xsi:type="dcterms:W3CDTF">{DATE}</dcterms:modified>
<cp:revision>1</cp:revision>
</cp:coreProperties>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
{SHEETS}',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="{REF}"/><cols>{COLS}</cols><sheetData>{ROWS}</sheetData></worksheet>',
            'xl/sharedStrings.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="{CNT}" uniqueCount="{CNT}">{STRINGS}</sst>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2"><font><name val="Calibri"/><family val="2"/></font><font><name val="Calibri"/><family val="2"/><b/></font></fonts>
<fills count="1"><fill><patternFill patternType="none"/></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" /></cellStyleXfs>
<cellXfs count="6">
	<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
	<xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
	<xf numFmtId="9" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>	
	<xf numFmtId="10" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
	<xf numFmtId="14" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
	<xf numFmtId="20" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
	<xf numFmtId="22" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
	<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right"/></xf>	
</cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<fileVersion appName="'.__CLASS__.'"/><sheets>
{SHEETS}
</sheets></workbook>'
        ];
        // <col min="1" max="1" width="22.1796875" bestFit="1" customWidth="1"/>
        // <row r="1" spans="1:2" x14ac:dyDescent="0.35"><c r="A1" t="s"><v>0</v></c><c r="B1"><v>100</v></c></row><row r="2" spans="1:2" x14ac:dyDescent="0.35"><c r="A2" t="s"><v>1</v></c><c r="B2"><v>200</v></c></row>
        // <si><t>Простой шаблон</t></si><si><t>Будем делать генератор</t></si>
    }
    public static function fromArray( array $rows, $sheetName = null ) {
        $xlsx = new static();
        return $xlsx->addSheet( $rows, $sheetName );
    }

    public function addSheet( array $rows, $name = null ) {
        $this->curSheet++;

        $this->sheets[$this->curSheet] = ['name' => $name ?: 'Sheet'.($this->curSheet+1)];

        if ( is_array( $rows ) && isset( $rows[0] ) && is_array($rows[0]) ) {
            $this->sheets[$this->curSheet]['rows'] = $rows;
        } else {
            $this->sheets[$this->curSheet]['rows'] = [];
        }
        return $this;
    }

    public function __toString() {
        $fh = fopen( 'php://memory', 'wb' );
        if ( ! $fh ) {
            return '';
        }

        if ( ! $this->_write( $fh ) ) {
            fclose( $fh );
            return '';
        }
        $size = ftell( $fh );
        fseek( $fh, 0);

        return (string) fread( $fh, $size );
    }

    public function saveAs( $filename ) {
        $fh = fopen( $filename, 'wb' );
        if (!$fh) {
            return false;
        }
        if ( !$this->_write($fh) ) {
            fclose($fh);
            return false;
        }
        fclose($fh);

        return true;
    }

    public function download() {
        return $this->downloadAs( gmdate('YmdHi') . '.xlsx' );
    }

    public function downloadAs( $filename ) {
        $fh = fopen('php://memory','wb');
        if (!$fh) {
            return false;
        }

        if ( !$this->_write( $fh )) {
            fclose( $fh );
            return false;
        }

        $size = ftell($fh);

        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T' , time() ));
        header('Content-Length: '.$size);

        while( ob_get_level() ) {
            ob_end_clean();
        }
        fseek($fh,0);
        fpassthru( $fh );

        fclose($fh);
        return true;
    }

    protected function _write( $fh ) {


        $dirSignatureE= "\x50\x4b\x05\x06"; // end of central dir signature
        $zipComments = 'Generated by '.__CLASS__.' PHP class, thanks sergey.shuchkin@gmail.com';

        if (!$fh) {
            return false;
        }

        $cdrec = '';	// central directory content
        $entries= 0;	// number of zipped files
        $cnt_sheets = count( $this->sheets );

        foreach ($this->template as $cfilename => $template ) {
            if ( $cfilename === '[Content_Types].xml' ) {
                $s = '';
                for ( $i = 0; $i < $cnt_sheets; $i++) {
                    $s .= '<Override PartName="/xl/worksheets/sheet'.($i+1).
                        '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
                }
                $template = str_replace('{SHEETS}', $s, $template);
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            }
            elseif ( $cfilename === 'xl/_rels/workbook.xml.rels' ) {
                $s = '';
                for ( $i = 0; $i < $cnt_sheets; $i++) {
                    $s .= '<Relationship Id="rId'.($i+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'.
                        ' Target="worksheets/sheet'.($i+1).".xml\"/>\n";
                }
                $s .= '<Relationship Id="rId'.($i+2).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>';
                $template = str_replace('{SHEETS}', $s, $template);
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            }
            elseif ( $cfilename === 'xl/workbook.xml' ) {
                $s = '';
                foreach ( $this->sheets as $k => $v ) {
                    $s .= '<sheet name="' . $v['name'] . '" sheetId="' . ( $k + 1) . '" state="visible" r:id="rId' . ( $k + 2) . '"/>';
                }
                $template = str_replace('{SHEETS}', $s, $template);
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            }
            elseif ( $cfilename === 'docProps/core.xml' ) {
                $template = str_replace('{DATE}', gmdate('Y-m-d\TH:i:s\Z'), $template);
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            } elseif ( $cfilename === 'xl/sharedStrings.xml' ) {
                if (!count($this->SI)) {
                    $this->SI[] = 'No Data';
                }
                $si_cnt = count($this->SI);
                $this->SI = '<si><t>'.implode("</t></si>\r\n<si><t>", $this->SI).'</t></si>';
                $template = str_replace(['{CNT}', '{STRINGS}'], [ $si_cnt, $this->SI ], $template );
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            } elseif ( $cfilename === 'xl/worksheets/sheet1.xml' ) {
                foreach ( $this->sheets as $k => $v ) {
                    $filename = 'xl/worksheets/sheet'.($k+1).'.xml';
                    $xml = $this->_sheetToXML($this->sheets[$k], $template);
                    $this->_writeEntry($fh, $cdrec, $filename, $xml );
                    $entries++;
                }
                $xml = null;
            }
            else {
                $this->_writeEntry($fh, $cdrec, $cfilename, $template);
                $entries++;
            }
        }
        $before_cd = ftell($fh);
        fwrite($fh, $cdrec);

        // end of central dir
        fwrite($fh, $dirSignatureE);
        fwrite($fh, pack('v', 0)); // number of this disk
        fwrite($fh, pack('v', 0)); // number of the disk with the start of the central directory
        fwrite($fh, pack('v', $entries)); // total # of entries "on this disk"
        fwrite($fh, pack('v', $entries)); // total # of entries overall
        fwrite($fh, pack('V', mb_strlen($cdrec,'8bit')));     // size of central dir
        fwrite($fh, pack('V', $before_cd));         // offset to start of central dir
        fwrite($fh, pack('v', mb_strlen($zipComments,'8bit'))); // .zip file comment length
        fwrite($fh, $zipComments);

        return true;
    }

    protected function _writeEntry($fh, &$cdrec, $cfilename, $data) {
        $zipSignature = "\x50\x4b\x03\x04"; // local file header signature
        $dirSignature = "\x50\x4b\x01\x02"; // central dir header signature

        $e = [];
        $e['uncsize'] = mb_strlen($data, '8bit');

        // if data to compress is too small, just store it
        if($e['uncsize'] < 256){
            $e['comsize'] = $e['uncsize'];
            $e['vneeded'] = 10;
            $e['cmethod'] = 0;
            $zdata = $data;
        } else{ // otherwise, compress it
            $zdata = gzcompress($data);
            $zdata = substr(substr($zdata, 0, - 4 ), 2); // fix crc bug (thanks to Eric Mueller)
            $e['comsize'] = mb_strlen($zdata, '8bit');
            $e['vneeded'] = 10;
            $e['cmethod'] = 8;
        }

        $e['bitflag'] = 0;
        $e['crc_32']  = crc32($data);

        // Convert date and time to DOS Format, and set then
        $lastmod_timeS  = str_pad(decbin(date('s')>=32?date('s')-32:date('s')), 5, '0', STR_PAD_LEFT);
        $lastmod_timeM  = str_pad(decbin(date('i')), 6, '0', STR_PAD_LEFT);
        $lastmod_timeH  = str_pad(decbin(date('H')), 5, '0', STR_PAD_LEFT);
        $lastmod_dateD  = str_pad(decbin(date('d')), 5, '0', STR_PAD_LEFT);
        $lastmod_dateM  = str_pad(decbin(date('m')), 4, '0', STR_PAD_LEFT);
        $lastmod_dateY  = str_pad(decbin(date('Y')-1980), 7, '0', STR_PAD_LEFT);

        # echo "ModTime: $lastmod_timeS-$lastmod_timeM-$lastmod_timeH (".date("s H H").")\n";
        # echo "ModDate: $lastmod_dateD-$lastmod_dateM-$lastmod_dateY (".date("d m Y").")\n";
        $e['modtime'] = bindec("$lastmod_timeH$lastmod_timeM$lastmod_timeS");
        $e['moddate'] = bindec("$lastmod_dateY$lastmod_dateM$lastmod_dateD");

        $e['offset'] = ftell($fh);

        fwrite($fh, $zipSignature);
        fwrite($fh, pack('s', $e['vneeded'])); // version_needed
        fwrite($fh, pack('s', $e['bitflag'])); // general_bit_flag
        fwrite($fh, pack('s', $e['cmethod'])); // compression_method
        fwrite($fh, pack('s', $e['modtime'])); // lastmod_time
        fwrite($fh, pack('s', $e['moddate'])); // lastmod_date
        fwrite($fh, pack('V', $e['crc_32']));  // crc-32
        fwrite($fh, pack('I', $e['comsize'])); // compressed_size
        fwrite($fh, pack('I', $e['uncsize'])); // uncompressed_size
        fwrite($fh, pack('s', mb_strlen($cfilename, '8bit')));   // file_name_length
        fwrite($fh, pack('s', 0));  // extra_field_length
        fwrite($fh, $cfilename);    // file_name
        // ignoring extra_field
        fwrite($fh, $zdata);

        // Append it to central dir
        $e['external_attributes']  = (substr($cfilename, -1) === '/'&&!$zdata)?16:32; // Directory or file name
        $e['comments']             = '';

        $cdrec .= $dirSignature;
        $cdrec .= "\x0\x0";                  // version made by
        $cdrec .= pack('v', $e['vneeded']); // version needed to extract
        $cdrec .= "\x0\x0";                  // general bit flag
        $cdrec .= pack('v', $e['cmethod']); // compression method
        $cdrec .= pack('v', $e['modtime']); // lastmod time
        $cdrec .= pack('v', $e['moddate']); // lastmod date
        $cdrec .= pack('V', $e['crc_32']);  // crc32
        $cdrec .= pack('V', $e['comsize']); // compressed filesize
        $cdrec .= pack('V', $e['uncsize']); // uncompressed filesize
        $cdrec .= pack('v', mb_strlen($cfilename,'8bit')); // file name length
        $cdrec .= pack('v', 0);                // extra field length
        $cdrec .= pack('v', mb_strlen($e['comments'],'8bit')); // file comment length
        $cdrec .= pack('v', 0); // disk number start
        $cdrec .= pack('v', 0); // internal file attributes
        $cdrec .= pack('V', $e['external_attributes']); // internal file attributes
        $cdrec .= pack('V', $e['offset']); // relative offset of local header
        $cdrec .= $cfilename;
        $cdrec .= $e['comments'];
    }

    protected function _sheetToXML(&$sheet, $template) {

        $COLS = [];
        $ROWS = [];
        if ( count($sheet['rows']) ) {
            $CUR_ROW = 0;
            $COL = [];
            foreach( $sheet['rows'] as $r ) {
                $CUR_ROW++;
                $row = '<row r="'.$CUR_ROW.'">';
                $CUR_COL = 0;
                foreach( $r as $v ) {
                    $CUR_COL++;
                    if ( !isset($COL[ $CUR_COL ])) {
                        $COL[ $CUR_COL ] = 0;
                    }
                    if ( $v === null || $v === '' ) {
                        continue;
                    }
                    $vl = mb_strlen( (string) $v );

                    $COL[ $CUR_COL ] = max( $vl, $COL[ $CUR_COL ] );

                    $cname = $this->num2name($CUR_COL) . $CUR_ROW;

                    $ct = $cs = null;

                    if ( is_string($v) ) {

                        if ( $v === '0' || preg_match( '/^[-+]?[1-9]\d{0,14}$/', $v ) ) { // Integer as General
                            $cv = ltrim( $v, '+' );
                            if ( $vl > 10 ) {
                                $cs = 1; // [1] 0
                            }
                        } elseif ( preg_match('/^[-+]?(0|[1-9]\d*)\.\d+$/', $v ) ) {
                            $cv = ltrim($v,'+');
                        } elseif ( preg_match('/^([-+]?\d+)%$/', $v, $m) ) {
                            $cv = round( $m[1] / 100, 2);
                            $cs = 2; // [9] 0%
                        } elseif ( preg_match('/^([-+]\d+\.\d+)%$/', $v, $m) ) {
                            $cv = round( $m[1] / 100, 4 );
                            $cs = 3; // [10] 0.00%
                        } elseif ( preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)$/', $v, $m ) ){
                            $cv = $this->date2excel($m[1],$m[2],$m[3]);
                            $cs = 4; // [14] mm-dd-yy
                        } elseif ( preg_match('/^(\d\d)\/(\d\d)\/(\d\d\d\d)$/', $v, $m ) ){
                            $cv = $this->date2excel($m[3],$m[2],$m[1]);
                            $cs = 4; // [14] mm-dd-yy
                        } elseif ( preg_match('/^(\d\d):(\d\d):(\d\d)$/', $v, $m ) ){
                            $cv = $this->date2excel(0,0,0,$m[1],$m[2],$m[3]);
                            $cs = 5; // [14] mm-dd-yy
                        } elseif ( preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/', $v, $m ) ) {
                            $cv = $this->date2excel( $m[1], $m[2], $m[3], $m[4], $m[5], $m[6] );
                            $cs = 6; // [22] m/d/yy h:mm
                        } elseif ( preg_match('/^(\d\d)\/(\d\d)\/(\d\d\d\d) (\d\d):(\d\d):(\d\d)$/', $v, $m ) ) {
                            $cv = $this->date2excel( $m[3], $m[2], $m[1], $m[4], $m[5], $m[6] );
                            $cs = 6; // [22] m/d/yy h:mm
                        } elseif ( mb_strlen( $v ) > 160 ) {
                            $ct = 'inlineStr';
                            $cv = str_replace(['&','<','>',"\x03"],['&amp;','&lt;','&gt;',''], $v);
                        } else {
                            if ( preg_match('/^[0-9+-.]+$/', $v ) ) { // Long ?
                                $cs = 7; // Align Right
                            }
                            $v = ltrim($v,"\0"); // disabled type detection
                            $ct = 's'; // shared string
                            $v = str_replace(['&','<','>',"\x03"],['&amp;','&lt;','&gt;',''], $v);
                            $cv = false;
                            $skey = '~'.$v;
                            if ( isset($this->SI_KEYS[ $skey ]) ) {
                                $cv = $this->SI_KEYS[ $skey ];
                            }

                            if ( $cv === false ) {
                                $this->SI[] = $v;
                                $cv  = count( $this->SI ) - 1;
                                $this->SI_KEYS[$skey] = $cv;
                            }
                        }
                    } elseif ( is_int( $v ) || is_float( $v ) ) {
                        $cv = $v;
                    } else {
                        continue;
                    }

                    $row .= '<c r="' . $cname . '"'.($ct ? ' t="'.$ct.'"' : '').($cs ? ' s="'.$cs.'"' : '').'>'
                        .($ct === 'inlineStr' ? '<is><t>'.$cv.'</t></is>' : '<v>' . $cv . '</v>')."</c>\r\n";
                }
                $ROWS[] = $row . "</row>\r\n";
            }
            foreach ( $COL as $k => $max ) {
                $COLS[] = '<col min="'.$k.'" max="'.$k.'" width="'.min( $max+1, 60).'" />';
            }
            $REF = 'A1:'.$this->num2name(count($COLS)) . $CUR_ROW;
        } else {
            $COLS[] = '<col min="1" max="1" bestFit="1" />';
            $ROWS[] = '<row r="1"><c r="A1" t="s"><v>0</v></c></row>';
            $REF = 'A1:A1';
        }
        return str_replace(['{REF}','{COLS}','{ROWS}'],
            [ $REF, implode("\r\n", $COLS), implode("\r\n",$ROWS) ],
            $template );
    }

    public function num2name($num) {
        $numeric = ($num - 1) % 26;
        $letter  = chr( 65 + $numeric );
        $num2    = (int) ( ($num-1) / 26 );
        if ( $num2 > 0 ) {
            return $this->num2name( $num2 ) . $letter;
        }
        return $letter;
    }

    public function date2excel($year, $month, $day, $hours=0, $minutes=0, $seconds=0) {
        $excelTime = (($hours * 3600) + ($minutes * 60) + $seconds) / 86400;

        if ( $year === 0 ) {
            return $excelTime;
        }

        // self::CALENDAR_WINDOWS_1900
        $excel1900isLeapYear = True;
        if (((int)$year === 1900) && ($month <= 2)) { $excel1900isLeapYear = False; }
        $myExcelBaseDate = 2415020;

        //    Julian base date Adjustment
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            --$year;
        }
        //    Calculate the Julian Date, then subtract the Excel base date (JD 2415020 = 31-Dec-1899 Giving Excel Date of 0)
        $century = substr($year,0,2);
        $decade = substr($year,2,2);
        $excelDate = floor((146097 * $century) / 4) + floor((1461 * $decade) / 4) + floor((153 * $month + 2) / 5) + $day + 1721119 - $myExcelBaseDate + $excel1900isLeapYear;

        return (float) $excelDate + $excelTime;
    }
}

class ModMManagerExportHelper{

    public static function getEvents($params){
        $filter_time = $params->get('filter_time','all');
        try{
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);

            $query->select($db->quoteName(array('event.id','event.name', 'event.eventdate', 'event.headerimg', 'event.ordering')));
            $query->from($db->quoteName('#__nxmarathonmanager_event','event'));
            $query->where($db->quoteName('event.published') . ' = ' . $db->quote('1'));
            if($filter_time !== 'all'){
                $now = new Date('now');
                if($filter_time === 'future'){
                    $operator = '>';
                }else{
                    $operator = '<';
                }
                $query->where($db->quoteName('event.eventdate') . $operator . $db->quote($now));
            }
            $query->order('event.eventdate DESC');

            $db->setQuery($query);

            //var_dump($query->dump());

            $results = $db->loadObjectList();

            // Bring the date in form
            foreach ($results as $result){
                $result->eventdate = HtmlHelper::date($result->eventdate, Text::_('DATE_FORMAT_FILTER_DATE'));
            }

            return $results;

        } catch (Exception $e){
            $db->transactionRollback();
            JFactory::getApplication()->enqueueMessage( JText::_('MOD_MMANAGER_EVENTS') .'<br>'.  $e->getMessage(), 'error');
        }
    }

    public static function countRegistrations($eventId,$paid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__nxmarathonmanager_registration', 'reg'));
        $query->where($db->quoteName('reg.eventid') . ' = '.$db->quote($eventId));
        if($paid){
            $query->where($db->quoteName('reg.paid') . ' = 1');
        }
        $db->setQuery($query);
        return $db->loadResult();

    }

    public static function getRegistrations($eventId = NULL){
        if($eventId) {
            try {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('*');
                $query->from($db->quoteName('#__nxmarathonmanager_registration','reg'));
                $query->where($db->quoteName('reg.published') . ' = ' . $db->quote('1'));
                $query->where($db->quoteName('reg.eventid') . ' = ' . $db->quote($eventId));
                $db->setQuery($query);
                $data = $db->loadObjectList();
                foreach ($data as $registration){
                    $registration->created = HtmlHelper::date($registration->created, Text::_('d.m.y H:i'));
                }
                return $data;

            } catch (Exception $e) {
                return $e->getMessage();
            }
        }else{
            return false;
        }
    }

    public static function getRegistrationsAjax(){
        $jinput = JFactory::getApplication()->input;
        $data = json_decode($jinput->get('data', null, null));

        $teams =  self::getRegistrations($data);
        return $teams;

    }

    public static function setPaymentAjax(){
        $jinput = JFactory::getApplication()->input;
        $input = $jinput->get('data', null, null);

        // Check Permission
        $user = Factory::getUser();
        $can_update = $user->authorise('registration.edit', 'com_nxmarathonmanager');

        if(!$can_update){
            return (string) 'Keine Berechtigung für den User mit der ID '.$user->id;
        }

        $data = json_decode($input);
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            // Fields to update.
            $fields = array(
                $db->quoteName('paid') . ' = ' . $db->quote($data->status)
            );

            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote($data->registrationId)
            );

            $query->update($db->quoteName('#__nxmarathonmanager_registration'))->set($fields)->where($conditions);

            $db->setQuery($query);

            return $db->execute();

        }catch( Exception $e){
            return $e->getMessage();
        }
    }

    public static function createXlsAjax(){
        $jinput = JFactory::getApplication()->input;
        $input = $jinput->get('data', null, null);

        $db_regs = self::getRegistrations($input);

        //Todo: Registrationen in die korrekte form bringen

        // combine the data
        $data           = array();
        $headline       = ['registration_id','eventid','Registrationsdatum','Teamname','Startnummer','Kategorie','Parcours','Sprache Last Info','Kontakt','Mail','Telefon','Ankunft','Registration','Bezahlt'];
        for($i=1; $i<6; $i++){
            $headline = array_merge($headline,['Läufer '.$i.' Vorname','Nachname','Jahrgang','Vergünstigung','Wohnort']);
        }
        // Add two spacers (SI-CARD)
        $headline[] = 'SI-Card 1';
        $headline[] = 'SI-Card 2';
        // Add Country (since 1.1)
        $headline[] = 'Country Läufer 1';
        $headline[] = 'Country Läufer 2';
        $registrations  = self::designRegistrationsForExport($db_regs);

        $data[]         = $headline;
        $data           = array_merge($data, $registrations);
        // Do the xls
        $xlsx           = SimpleXLSXGen::fromArray( $data );
        $filepath       = 'tmp/export.xlsx';
        $status         = $xlsx->saveAs($filepath);



        if($status){
            return JUri::base() .  $filepath;
        }else{
            return false;
        }

    }

    /**
     * Returns an array of arrays containing the registrations including all joined information for export.
     *
     * @param $db_regs      array        array of registration objects
     * @return              array        array of arrays with all needed data for export
     * @since: 1.0
     */
    public static function designRegistrationsForExport($db_regs){
        $registrationsArr = array();
        $startnumbers = array(
            1 => 101,
            3 => 301,
            5 => 501,
            7 => 701,
            9 => 901
        );
        // Changed in Version 1.1.4 >> DV wünscht Startnummer ab nn1
        $countries = self::getCountries();
        error_log(json_encode($countries));

        foreach ($db_regs as $regData){

            $arrival = $regData->arrival_date;

            $runnersArr = json_decode($regData->participants, 1);
            $i = 0;
            $runnerMails = '';
            $contactName = '';
            foreach($runnersArr as $runner){
                if($i === 0){
                    $contactName = $runner['runner_firstname'] . ' ' . $runner['runner_lastname'];
                }
                if($i > 2){
                    break;
                }
                if(key_exists('runner_email', $runner) && strlen($runner['runner_email']) && strpos($runner['runner_email'],'@')){
                    $runnerMails.= ";\n" . $runner['runner_email'];
                }
                $i++;
            }

            $category = self::getCategoryInfo($regData->teamcategory);
            // get the startnumber
            $num = $startnumbers[$category->catId];
            // +1 to the startnumbers
            $startnumbers[$category->catId]++;

            $registration = array();
            $registration[] = $regData->id;
            $registration[] = $regData->eventid;
            $registration[] = $regData->created;
            $registration[] = $regData->teamname;
            $registration[] = $num;        // Number
            $registration[] = $category->labeledId;         // Category ID
            $registration[] = $category->label;             // Category Name
            $registration[] = $regData->lastinfolang;       // Changed to Last Info Language (1.1.6)
            $registration[] = $contactName;     // Kontakt
            $registration[] = $regData->contactemail . $runnerMails;
            $registration[] = $regData->emergency_number;
            $registration[] = self::arrivalTypes($regData->arrival_type);
            $registration[] = (is_numeric($arrival) && (int)$arrival == $arrival) ? self::unixToDate($arrival) : $arrival;
            $registration[] = (intval($regData->paid)) ? 'Ja':'Nein';

            // Runners

            $runnerCountry = array();
            $runnerCount = 0;
            foreach($runnersArr as $runnerItem){
                $registration[] = (array_key_exists('runner_firstname',$runnerItem)) ? $runnerItem['runner_firstname'] : '';
                $registration[] = (array_key_exists('runner_lastname',$runnerItem)) ? $runnerItem['runner_lastname'] : '';
                $registration[] = (array_key_exists('runner_year',$runnerItem)) ? $runnerItem['runner_year'] : '';
                $registration[] = (array_key_exists('pt_reduction',$runnerItem)) ? self::publicTransportReduction($runnerItem['pt_reduction']) : '';
                $registration[] = (array_key_exists('runner_location',$runnerItem)) ? $runnerItem['runner_location'] : '';
                if($runnerCount < 2 && array_key_exists('runner_country',$runnerItem)){
                    error_log($countries[$runnerItem['runner_country']]['name']);
                    error_log("Country Code: " . $countries[$runnerItem['runner_country']]['countrycode']);
                    $runnerCountry[] = strtoupper($countries[$runnerItem['runner_country']]['countrycode']);
                }
                $runnerCount++;
            }
            //Fill up columns (since version 1.1)
            $colsToFill = (5 - count($runnersArr)) * 5;         // max amount runners(5) per team - effective registered(count runnserArr) * cols per runner(5)
            $colsOffset = 2;                                    // offset for empty columns (later used by timing hobbit)
            /*
             * {"participants0":{"runner_gender":"m","runner_firstname":"MArco","runner_lastname":"Rensch","runner_location":"Mels","runner_country":"2","runner_year":"1986","runner_email":"marco.rensch@nx-designs.ch","pt_reduction":"0"}}
             */
            // Leerspalten für Zeitmessung einfügen (bis und mit --> AN / AO)
            $colsCounter = 0;
            while ($colsCounter < $colsToFill+$colsOffset){
                $registration[] = '';
                $colsCounter++;
            }

            //

            //Runner Country (since v1.1)
            $registration[] = $runnerCountry[0];
            $registration[] = $runnerCountry[1];

            $registrationsArr[] = $registration;

            usort($registrationsArr, function($a, $b) {return strcmp($a[4], $b[4]);});
        }
        return $registrationsArr;
    }

    public static function unixToDate($unix){
        $date = new Date($unix);
        return $date->format('d.M.Y');
    }

    public static function publicTransportReduction($type){
        switch($type){
            case '0':
            default:
                $reduct = 'keine';
                break;
            case 'ga':
                $reduct = 'GA';
                break;
            case 'ht':
                $reduct = 'Halbtax';
        }
        return $reduct;
    }

    public static function arrivalTypes($type){
        switch($type){
            case 'car':
                $arriveBy = 'Auto';
                break;
            case 'public':
                $arriveBy = 'ÖV';
                break;
            case 'both':
            default:
                $arriveBy = 'beides';
        }
        return $arriveBy;
    }

    /**
     * Reads the information for a certain category and returns its details
     *
     * @param $catId            ID of the category
     * @return stdClass         Category Identification number & Category label
     */
    public static function getCategoryInfo($catId = NULL){
        if($catId) {
            try {
                $category = new stdClass();

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('*');
                $query->from($db->quoteName('#__nxmarathonmanager_teamcategory'));
                $query->where($db->quoteName('id') . ' = ' . $db->quote($catId));
                $db->setQuery($query);
                $cat = $db->loadObject();

                if($cat){
                    $category->catId = (int) $cat->event_main_catid;
                    $category->labeledId = (string) $cat->event_main_catid . '.' . $cat->event_group_catid;
                    $category->label = (string) $cat->name;
                }else{
                    return false;
                }

                return $category;

            } catch (Exception $e) {
                return $e->getMessage();
            }
        }else{
            return false;
        }
    }

    /**
     * Returns all countries configured in MarathonManager as associative array
     * @return mixed|null
     */
    public static function checkCountriesAjax(){
        $data = self::getCountries();
        return $data;
    }
    public static function getCountries(){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select(array('a.id','a.name','a.countrycode'))
            ->from($db->quoteName('#__nxmarathonmanager_country', 'a'));
        //$query->order('a.ordering ASC');

        // Reset the query using our newly populated query object.
        $db->setQuery($query);

        // Load the results as a list of stdClass objects (see later for more options on retrieving data).
        $results = $db->loadAssocList('id');
        return $results;
    }

}
