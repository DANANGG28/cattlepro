<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=template_import_sapi.xls');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center" ss:Horizontal="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11"/>
  </Style>

  <Style ss:ID="header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#0A3622" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#083019"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#083019"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#083019"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#083019"/>
   </Borders>
  </Style>

  <Style ss:ID="guide">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Italic="1" ss:Color="#B45309"/>
   <Interior ss:Color="#FEF3C7" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
   </Borders>
  </Style>

  <Style ss:ID="data">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#F3F4F6"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#F3F4F6"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#F3F4F6"/>
   </Borders>
  </Style>
 </Styles>

 <Worksheet ss:Name="Data Sapi">
  <Table ss:DefaultRowHeight="28">
   <Column ss:Width="140"/>
   <Column ss:Width="140"/>
   <Column ss:Width="160"/>
   <Column ss:Width="120"/>
   <Column ss:Width="150"/>
   <Column ss:Width="160"/>

   <Row ss:Height="36">
    <Cell ss:StyleID="header"><Data ss:Type="String">Kode Sapi</Data></Cell>
    <Cell ss:StyleID="header"><Data ss:Type="String">Jenis</Data></Cell>
    <Cell ss:StyleID="header"><Data ss:Type="String">Tanggal Lahir</Data></Cell>
    <Cell ss:StyleID="header"><Data ss:Type="String">Berat (kg)</Data></Cell>
    <Cell ss:StyleID="header"><Data ss:Type="String">Status</Data></Cell>
    <Cell ss:StyleID="header"><Data ss:Type="String">Tanggal Status</Data></Cell>
   </Row>

   <Row ss:Height="26">
    <Cell ss:StyleID="guide"><Data ss:Type="String">Wajib diisi</Data></Cell>
    <Cell ss:StyleID="guide"><Data ss:Type="String">&#8595; Pilih dari list</Data></Cell>
    <Cell ss:StyleID="guide"><Data ss:Type="String">YYYY-MM-DD</Data></Cell>
    <Cell ss:StyleID="guide"><Data ss:Type="String">Angka</Data></Cell>
    <Cell ss:StyleID="guide"><Data ss:Type="String">&#8595; Pilih dari list</Data></Cell>
    <Cell ss:StyleID="guide"><Data ss:Type="String">YYYY-MM-DD</Data></Cell>
   </Row>


   <!-- Dummy data -->
   <Row>
    <Cell ss:StyleID="data"><Data ss:Type="String">BRANGUS-001</Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String">Brangus</Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"><?php echo date('Y-m-d', strtotime('-6 months')); ?></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="Number">385</Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String">Sudah Inseminasi Buatan</Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"><?php echo date('Y-m-d'); ?></Data></Cell>
   </Row>

<?php for ($i = 0; $i < 49; $i++): ?>
   <Row>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="data"><Data ss:Type="String"></Data></Cell>
   </Row>
<?php endfor; ?>

  </Table>

  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>2</SplitHorizontal>
   <TopRowBottomPane>2</TopRowBottomPane>
  </WorksheetOptions>

  <DataValidation xmlns="urn:schemas-microsoft-com:office:excel">
   <Range>R3C2:R52C2</Range>
   <Type>List</Type>
   <Value>&quot;Limousin,Simental,PO,Brangus,Brahman,Angus,Bali,Madura,Aceh&quot;</Value>
  </DataValidation>

  <DataValidation xmlns="urn:schemas-microsoft-com:office:excel">
   <Range>R3C5:R52C5</Range>
   <Type>List</Type>
   <Value>&quot;Kosong,Sudah Birahi,Sudah Inseminasi Buatan,Bunting&quot;</Value>
  </DataValidation>

 </Worksheet>
</Workbook>
