<?php
if($_REQUEST['test']){
    require_once('assets/init.php');
}
if($_REQUEST['invoice_id'] <=0){
    die();
}
$query = "select i.*,u.username,u.email from invoices as i left join Wo_Users as u on u.user_id=i.user_id where id = ".Wo_Secure($_REQUEST[invoice_id]);
$result = mysqli_query($sqlConnect,$query);
if(!$result){
    die;
}
$detail = mysqli_fetch_assoc($result);
if($detail['vat'] > 0) {
    $vatPercent             = ($detail['vat'] / 100)+1;
    $priceWithoutVat        = round($detail['total_price'] / $vatPercent,2);
    $vatAmount              = $detail['total_price'] - $priceWithoutVat;
}else{
    $priceWithoutVat    = $detail['total_price'];
}
$company    = ($wo['config']['invoice_company'])?:false;
$email      = ($wo['config']['invoice_email'])?:false;
$phone      = ($wo['config']['invoice_phone'])?:false;
$adminAddress   = ($wo['config']['invoice_address'])?:false;
$adminVatNum    = ($wo['config']['invoice_comp_num'])?:false;
$adminTicNum    = ($wo['config']['tic_number'])?:false;


$customer   = ($detail['company_name'])?:$detail['username'];
$email      = $detail['email'];
$address    = $detail['company_address'];
$vatNumber  = $detail['vat_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #5D6975;
            text-decoration: underline;
        }

        body {
            position: relative;
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            color: #001028;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-family: Arial;
        }

        header {
            padding: 10px 0;
            margin-bottom: 30px;
        }

        #logo {
            text-align: center;
            margin-bottom: 10px;
        }

        #logo img {
            width: 90px;
        }

        h1 {
            border-top: 1px solid  #5D6975;
            border-bottom: 1px solid  #5D6975;
            color: #5D6975;
            font-size: 2.4em;
            line-height: 1.4em;
            font-weight: normal;
            text-align: center;
            margin: 0 0 20px 0;
        }

        #project {
            float: left;
        }

        #project span {
            color: #5D6975;
            text-align: right;
            width: 52px;
            margin-right: 10px;
            display: inline-block;
            font-size: 0.8em;
        }

        #company {
            float: right;
            text-align: right;
        }

        #project div,
        #company div {
            white-space: nowrap;
        }

        table.design {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table.design tr:nth-child(2n-1) td {
            background: #F5F5F5;
        }

        table.design th,
        table.design td {
            text-align: center;
        }

        table.design th {
            padding: 5px 20px;
            color: #5D6975;
            border-bottom: 1px solid #C1CED9;
            white-space: nowrap;
            font-weight: normal;
        }

        table.design .service,
        table.design .desc {
            text-align: left;
        }

        table.design td {
            padding: 20px;
            text-align: right;
        }

        table.design td.service,
        table.design td.desc {
            vertical-align: top;
        }

        table.design td.unit,
        table.design td.qty,
        table.design td.total {
            font-size: 1.2em;
        }

        table.design td.grand {
            border-top: 1px solid #5D6975;;
        }

        #notices .notice {
            color: #5D6975;
            font-size: 1.2em;
        }

        footer {
            color: #5D6975;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #C1CED9;
            padding: 8px 0;
            text-align: center;
        }
    </style>
</head>
<body>
<header class="clearfix">
    <div id="logo">
    </div>
    <h1>INVOICE (<?=$wo['config']['siteName']?>)</h1>
    <div id="project">
        <?php if($detail['invoice_num'] != ''):?>
            <div><span>INVOICE</span> <?=$detail['invoice_num']?></div>
        <?php endif;?>
        <div><span>CLIENT</span> <?=$customer?> <?= $detail['invoice_name'] != ''? '- '.$detail['invoice_name']:''?></div>
        <div><span>EMAIL</span> <a href="mailto:<?=$email?>"><?=$email?></a></div>
        <?php if($address!=""):?>
            <div><span>ADDRESS</span> <?=$address?></div>
        <?php endif;?>
        <?php if($vatNumber!=""):?>
            <div><span>Vat Num</span> <?=$vatNumber?></div>
        <?php endif;?>
        <div><span>DATE</span> <?=date_format(date_create($detail['created_at']),"d M Y H:i:s");?></div>
    </div>
</header>
<main>
    <table class="design">
        <thead>
        <tr>
            <th class="service">SERVICE</th>
            <th class="desc">DESCRIPTION</th>
            <th>PRICE</th>
            <th>QTY</th>
            <th>TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="service"><?=$detail['product_title']?></td>
            <td class="desc"><?=$detail['desc']?></td>
            <td class="unit"><?=$detail['product_price']." ".$wo['config']['currency']?></td>
            <td class="qty"><?=$detail['qty']?></td>
            <td class="total"><?=$detail['total_price']." ".$wo['config']['currency']?></td>
        </tr>
<!--        <tr>-->
<!--            <td class="service">Development</td>-->
<!--            <td class="desc">Developing a Content Management System-based Website</td>-->
<!--            <td class="unit">$40.00</td>-->
<!--            <td class="qty">80</td>-->
<!--            <td class="total">$3,200.00</td>-->
<!--        </tr>-->
        <tr>
            <td colspan="4">SUBTOTAL</td>
            <td class="total"><?=$priceWithoutVat." ".$wo['config']['currency']?></td>
        </tr>
        <?php if($detail['vat'] > 0):?>
        <tr>
            <td colspan="4">TAX <?=$detail['vat']?>%</td>
            <td class="total"><?=$vatAmount." ".$wo['config']['currency']?></td>
        </tr>
        <?php endif;?>
        <tr>
            <td colspan="4" class="grand total">GRAND TOTAL</td>
            <td class="grand total"><?=$detail['total_price']." ".$wo['config']['currency']?></td>
        </tr>
        </tbody>
    </table>

    <div id="company" class="clearfix" style="line-height: 24px;font-size: 14px;font-weight: 200;">
        <div><?=$company?></div>
        <div><?=$adminAddress?></div>
        <div><?=$phone?></div>
        <div><a href="mailto:<?=$email?>"><?$email?></a></div>
        <?php if($adminVatNum):?>
            <div>VAT # ( <?=$adminVatNum?> )</div>
        <? endif; ?>
        <?php if($adminTicNum):?>
            <div>TIC # ( <?=$adminTicNum?> )</div>
        <? endif; ?>
    </div>
<!--    <div id="notices">-->
<!--        <div>NOTICE:</div>-->
<!--        <div class="notice">A finance charge of 1.5% will be made on unpaid balances after 30 days.</div>-->
<!--    </div>-->
</main>
<footer>
    Invoice was created on a computer and is valid without the signature and seal.
</footer>
</body>
</html>