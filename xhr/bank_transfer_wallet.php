<?php 
if ($f == "bank_transfer_wallet") {
    if (Wo_CheckSession($hash_id) === true) {
        $request   = array();
        $request[] = (empty($_FILES["thumbnail"]));
        if (in_array(true, $request) || empty($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] < 1) {
            $error = $error_icon . $wo['lang']['please_check_details'];
        }
        if (empty($error)) {
            $description = !empty($_POST['description']) ? Wo_Secure($_POST['description']) : '';
            $fileInfo      = array(
                'file' => $_FILES["thumbnail"]["tmp_name"],
                'name' => $_FILES['thumbnail']['name'],
                'size' => $_FILES["thumbnail"]["size"],
                'type' => $_FILES["thumbnail"]["type"],
                'types' => 'jpeg,jpg,png,bmp,gif'
            );
            $media         = Wo_ShareFile($fileInfo);
            $mediaFilename = $media['filename'];
            if (!empty($mediaFilename)) {
                $insert_id = Wo_InsertBankTrnsfer(array('user_id' => $wo['user']['id'],
                                                       'description' => $description,
                                                       'price'       => Wo_Secure($_POST['price']),
                                                       'receipt_file' => $mediaFilename,
                                                       'mode'         => 'wallet'));
                //create invoice
                $invoiceID  = Wo_InvoiceData($wo['user']['id'],"wallet",$_POST['price'],0,$_POST['price'],"wallet account has been recharged",1);
                $html       = WoGenerateInvoiceHTML($invoiceID);
                $file       = Wo_GeneratePDF($invoiceID,$html);
                $send_message_data       = array(
                    'from_email'    => ($wo['config']['invoice_email'])?$wo['config']['invoice_email']:$wo['config']['siteEmail'],
                    'from_name'     => ($wo['config']['invoice_company'])?$wo['config']['invoice_company']:$wo['config']['siteName'],
                    'to_email'      => $wo['user']['email'],
                    'to_name'       => $wo['user']['name'],
                    'subject'       => 'Purchase Invoice',
                    'charSet'       => 'utf-8',
                    'message_body'  => $html,
                    'attachment'    => $file,
                    'is_html'       => true
                );
                Wo_SendMessage($send_message_data);
                if (!empty($insert_id)) {
                    $data = array(
                        'message' => $success_icon . $wo['lang']['bank_transfer_request'],
                        'status' => 200
                    );
                }
            }
            else{
                $error = $error_icon . $wo['lang']['file_not_supported'];
                $data = array(
                    'status' => 500,
                    'message' => $error
                );
            }
        } else {
            $data = array(
                'status' => 500,
                'message' => $error
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}

