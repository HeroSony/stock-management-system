<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= $page_title . " " . lang("no") . " " . $inv->id; ?></title>
    <style>
        * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6em; margin: 0; padding: 0; } img { max-width: 100%; } body { -webkit-font-smoothing: antialiased; height: 100%; -webkit-text-size-adjust: none; width: 100% !important; } a { color: #348eda; } .btn-primary { Margin-bottom: 10px; width: auto !important; } .btn-primary td { background-color: #62cb31; border-radius: 3px; font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; font-size: 14px; text-align: center; vertical-align: top; } .btn-primary td a { background-color: #62cb31; border: solid 1px #62cb31; border-radius: 3px; border-width: 4px 20px; display: inline-block; color: #ffffff; cursor: pointer; font-weight: bold; line-height: 2; text-decoration: none; } .last { margin-bottom: 0; } .first { margin-top: 0; } .padding { padding: 10px 0; } table.body-wrap { padding: 20px; width: 100%; } table.body-wrap .container { border: 1px solid #e4e5e7; } table.footer-wrap { clear: both !important; width: 100%; } .footer-wrap .container p { color: #666666; font-size: 12px; } table.footer-wrap a { color: #999999; } h1, h2, h3 { color: #111111; font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; font-weight: 200; line-height: 1.2em; margin: 10px 0 10px; } h1 { font-size: 36px; } h2 { font-size: 28px; } h3 { font-size: 22px; } p, ul, ol {font-size: 14px;font-weight: normal;margin-bottom: 10px;} ul li, ol li {margin-left: 5px;list-style-position: inside;} .container { clear: both !important; display: block !important; Margin: 0 auto !important; max-width: 600px !important; } .body-wrap .container { padding: 20px; } .content { display: block; margin: 0 auto; max-width: 600px; } .content table { width: 100%; }
    </style>
</head>

<body bgcolor="#f7f9fa">
<table class="body-wrap" bgcolor="#f7f9fa">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                        <h2>
                        <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                         </h2>
                         </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="clear:both;height:15px;"></div>
                            <!-- <strong><?= lang('receipt').' '.lang('from').' '.$Settings->site_name; ?></strong> -->
                            <div style="text-align:left;">
                                <img src="<?= admin_url('misc/barcode/'.$this->sma->base64url_encode($inv->reference_no).'/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                                <?= $this->sma->qrcode('link', urlencode(admin_url('pos/view/' . $inv->id)), 2); ?>
                            </div>
                            <div style="clear:both;height:15px;"></div>
                                <div id="receiptData" style="border:1px solid #DDD; padding:10px; text-align:center;">

                                    <div class="text-center">
                                        <h3 style="text-transform:uppercase;"><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                                        <?php
                                        echo "<p>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
                                            "<br>" . lang("tel") . ": " . $biller->phone . "</p>";
                                        ?>
                                    </div>
                                    <?php
                                    if ($pos->cf_title1 != "" && $pos->cf_value1 != "") {
                                        echo $pos->cf_title1 . ": " . $pos->cf_value1 . "<br>";
                                    }
                                    if ($pos->cf_title2 != "" && $pos->cf_value2 != "") {
                                        echo $pos->cf_title2 . ": " . $pos->cf_value2 . "<br>";
                                    }
                                    echo "<p>" . lang("reference_no") . ": " . $inv->reference_no . "<br>";
                                    echo lang("customer") . ": " . $inv->customer . "<br>";
                                    echo lang("date") . ": " . $this->sma->hrld($inv->date) . "</p>";
                                    ?>
                                    <div style="clear:both;"></div>
                                    <table width="100%" style="margin:15px 0;">
                                        <tbody>
                                        <?php
                                        $r = 1;
                                        foreach ($rows as $row) {
                                            echo '<tr><td colspan="2">#' . $r . ': &nbsp;&nbsp;' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '') . '</td></tr>';
                                            echo '<tr><td style="border-bottom:1px solid #DDD;">' . $this->sma->formatQuantity($row->quantity) . ' x ';
                                            if ($row->item_discount != 0) {
                                                echo '<del>' . $this->sma->formatMoney($row->net_unit_price + ($row->item_discount / $row->quantity) + ($row->item_tax / $row->quantity)) . '</del> ';
                                            }
                                            echo $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)).' ('.$this->sma->formatMoney($row->net_unit_price).' + '.$this->sma->formatMoney($row->item_tax / $row->quantity) . ')</td><td style="text-align:right;border-bottom:1px solid #DDD;">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';
                                            $r++;
                                        }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th style="text-align:left;border-bottom:1px solid #DDD;"><?= lang("total"); ?></th>
                                            <th style="text-align:right;border-bottom:1px solid #DDD;"><?= $this->sma->formatMoney($inv->total); ?></th>
                                        </tr>
                                        <?php
                                        if ($inv->order_tax != 0 && $Settings->tax2) {
                                            echo '<tr><th style="text-align:left;border-bottom:1px solid #DDD;">' . lang("tax") . '</th><th style="text-align:right;border-bottom:1px solid #DDD;">' . $this->sma->formatMoney($inv->order_tax) . '</th></tr>';
                                        }
                                        if ($inv->total_discount != 0) {
                                            echo '<tr><th style="text-align:left;border-bottom:1px solid #DDD;">' . lang("discount") . '</th><th style="text-align:right;border-bottom:1px solid #DDD;">' . $this->sma->formatMoney($inv->total_discount) . '</th></tr>';
                                        }
                                        ?>
                                        <tr>
                                            <th style="text-align:left;border-bottom:1px solid #DDD;"><?= lang("grand_total"); ?></th>
                                            <th style="text-align:right;border-bottom:1px solid #DDD;"><?= $this->sma->formatMoney($inv->grand_total); ?></th>
                                        </tr>
                                        <?php if ($inv->rounding) { ?>
                                            <tr>
                                                <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("rounding");?></th>
                                                <th style="text-align:right;border-bottom:1px solid #DDD;"><?= $this->sma->formatMoney($inv->rounding);?></th>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("grand_total");?></th>
                                                <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->sma->formatMoney(($inv->grand_total + $inv->rounding));?></th>
                                            </tr>
                                        <?php } else { ?>
                                            <tr>
                                                <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("grand_total");?></th>
                                                <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->sma->formatMoney($inv->grand_total);?></th>
                                            </tr>
                                        <?php } if ($inv->paid < $inv->grand_total) { ?>
                                            <tr>
                                                <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("paid_amount");?></th>
                                                <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->sma->formatMoney($inv->paid);?></th>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("due_amount");?></th>
                                                <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->sma->formatMoney((($inv->grand_total + $inv->rounding)) - ($inv->paid));?></th>
                                            </tr>
                                        <?php } ?>
                                        <!--<tr><th style="text-align:left;border-bottom:1px solid #DDD;"><?= lang("total_items"); ?></th> <th class="text-right;border-bottom:1px solid #DDD;"><?= $inv->total_items; ?></th></tr>-->
                                        </tfoot>
                                    </table>
                                    <?php
                                    if ($payments) {
                                        echo '<table width="100%" style="margin:15px 0;border-bottom:1px solid #DDD;"><tbody>';
                                        foreach ($payments as $payment) {
                                            echo '<tr>';
                                            if ($payment->paid_by == 'cash' && $payment->amount) {
                                                echo '<td>' . lang("amount") . ': ' . $payment->amount . '</td>';
                                                echo '<td>' . lang("change") . ': ' . $this->sma->formatMoney($payment->amount - $inv->total) . '</td>';
                                            }
                                            if (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') && $payment->cc_no) {
                                                echo '<td>' . lang("amount") . ': ' . $payment->amount . '</td>';
                                                echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                                                echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                                            }
                                            if ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                                echo '<td>' . lang("amount") . ': ' . $payment->amount . '</td>';
                                                echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                                            }
                                            echo '</tr>';
                                        }
                                        echo '</tbody></table>';
                                    }
                                    ?>

                                    <p class="text-center">
                                        <?= $this->sma->decode_html($biller->invoice_footer); ?>
                                    </p>
                                    <div style="clear:both;"></div>
                                </div>

                            </div>
                            <div style="clear:both;height:25px;"></div>
                            <strong><?= $Settings->site_name; ?></strong>
                            <!-- <p><?= base_url(); ?></p> -->
                            <div style="clear:both;height:15px;"></div>
                            <p style="border-top:1px solid #CCC;margin-bottom:0;">This email is sent to <?= $customer->company; ?> (<?= $customer->email; ?>).</p>
                        </td>
                    </tr>
                </table>
            </div>

        </td>
        <td></td>
    </tr>
</table>

</body>
</html>
