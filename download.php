<?php

require "assets/init.php";
set_time_limit(0);
error_reporting(0);

$hash_id = $_REQUEST['hash'];
if (Wo_CheckSession($hash_id) === true) {
    if( $_REQUEST['ids'] != ''){
        $pdfPaths = [];
        $invoiceIDS = explode(',',Wo_Secure($_REQUEST['ids']));
        $invoices = $db->where('id', $invoiceIDS,'in' )->get(T_INVOICES, null, 'id,file,invoice_name');
        if(count($invoices) > 0){
            foreach ($invoices as $invoice){
                if($invoice->file){
                    $index = ($invoice->invoice_name)?:$invoice->id;
                    $pdfPaths[$index] = $invoice->file;
                }
            }
        }
    }else if($_REQUEST['month'] > 0){
        $month      = Wo_Secure($_REQUEST['month']);
        $query = 'select id,file,invoice_name from '.T_INVOICES.' where created_at >= "'.date('Y').'-'.$month.'-1" and created_at <= "'.date('Y').'-'.$month.'-31"';
        $invoices   = $db->rawQuery($query );
        if(count($invoices) > 0){
            foreach ($invoices as $invoice){
                if($invoice->file){
                    $index = ($invoice->invoice_name)?:$invoice->id;
                    $pdfPaths[$index] = $invoice->file;
                }
            }
        }
    }

    if(count($pdfPaths) > 0){
        $zipFile = tempnam('/invoiceTmp/', 'zip');
        $zip     = new ZipArchive();
        $zipFile = tempnam('/invoiceTmp/', 'zip');
        $zip->open($zipFile, ZipArchive::CREATE);
        foreach ($pdfPaths as $index => $pdf){
            $ext = pathinfo($pdf, PATHINFO_EXTENSION);
            if($ext === 'pdf'){
                $zip->addFile($pdf,"$index-".time().".$ext");
            }
        }
        $zip->close();
        header('Content-type: application/zip');
        echo file_get_contents($zipFile);
        unlinke($zipFile);
        exit();
    }

}
exit('Please click on download link again, this link is expired');
?>