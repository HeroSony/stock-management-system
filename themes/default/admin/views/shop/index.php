<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('shop_settings'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo admin_form_open_multipart("shop_settings", $attrib);
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('shop_name', 'shop_name'); ?>
                            <?= form_input('shop_name', set_value('shop_name', $shop_settings->shop_name), 'class="form-control tip" id="shop_name" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?= form_input('description', set_value('description', $shop_settings->description), 'class="form-control tip" id="description" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('logo', 'logo'); ?>
                            <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="logo" data-show-upload="false" data-show-preview="false" class="form-control file">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('shipping', 'shipping'); ?>
                            <?= form_input('shipping', set_value('shipping', $shop_settings->shipping), 'class="form-control tip" id="shipping"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('warehouse', 'warehouse'); ?>
                            <?php
                            $wh[''] = lang('select').' '.lang('warehouse');
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                            }
                            ?>
                            <?= form_dropdown('warehouse', $wh, set_value('warehouse', $shop_settings->warehouse), 'class="form-control tip" id="warehouse"  required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('biller', 'biller'); ?>
                            <?php
                            $bl[''] = lang('select').' '.lang('biller');
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            ?>
                            <?= form_dropdown('biller', $bl, set_value('biller', $shop_settings->biller), 'class="form-control tip" id="biller"  required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('phone', 'phone'); ?>
                            <?= form_input('phone', set_value('phone', $shop_settings->phone), 'class="form-control tip" id="phone" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('email', 'email'); ?>
                            <?= form_input('email', set_value('email', $shop_settings->email), 'class="form-control tip" id="email" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('about_link', 'about_link'); ?>
                            <?php
                            $pgs[''] = lang('select').' '.lang('page');
                            foreach ($pages as $page) {
                                $pgs[$page->slug] = $page->title;
                            }
                            ?>
                            <?= form_dropdown('about_link', $pgs, set_value('about_link', $shop_settings->about_link), 'class="form-control tip" id="about_link"  required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('terms_link', 'terms_link'); ?>
                            <?= form_dropdown('terms_link', $pgs, set_value('terms_link', $shop_settings->terms_link), 'class="form-control tip" id="terms_link"  required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('privacy_link', 'privacy_link'); ?>
                            <?= form_dropdown('privacy_link', $pgs, set_value('privacy_link', $shop_settings->privacy_link), 'class="form-control tip" id="privacy_link"  required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('contact_link', 'contact_link'); ?>
                            <?= form_dropdown('contact_link', $pgs, set_value('contact_link', $shop_settings->contact_link), 'class="form-control tip" id="contact_link"  required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('payment_text', 'payment_text'); ?>
                            <?= form_input('payment_text', set_value('payment_text', $shop_settings->payment_text), 'class="form-control tip" id="payment_text" required="required"'); ?>
                        </div>

                    </div>
                    <div class="col-md-6">

                        <div class="form-group">
                            <?= lang('follow_text', 'follow_text'); ?>
                            <?= form_input('follow_text', set_value('follow_text', $shop_settings->follow_text), 'class="form-control tip" id="follow_text" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('facebook', 'facebook'); ?>
                            <?= form_input('facebook', set_value('facebook', $shop_settings->facebook), 'class="form-control tip" id="facebook" required="required"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('twitter', 'twitter'); ?>
                            <?= form_input('twitter', set_value('twitter', $shop_settings->twitter), 'class="form-control tip" id="twitter"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('google_plus', 'google_plus'); ?>
                            <?= form_input('google_plus', set_value('google_plus', $shop_settings->google_plus), 'class="form-control tip" id="google_plus"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('instagram', 'instagram'); ?>
                            <?= form_input('instagram', set_value('instagram', $shop_settings->instagram), 'class="form-control tip" id="instagram"'); ?>
                        </div>

                        <div class="form-group">
                            <?= lang('cookie_message', 'cookie_message'); ?>
                            <?= form_input('cookie_message', set_value('cookie_message', $shop_settings->cookie_message), 'class="form-control tip" id="cookie_message"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('cookie_link', 'cookie_link'); ?>
                            <?= form_dropdown('cookie_link', $pgs, set_value('cookie_link', $shop_settings->cookie_link), 'class="form-control tip" id="cookie_link"'); ?>
                        </div>

                    </div>

                <div class="col-md-12">
                    <?= form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
                </div>
                </div>
                <?= form_close(); ?>
                <?php if ( ! DEMO ) { ?>
                <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="well well-sm" style="margin-bottom:0;">
                        <p><?= lang('call_back_heading'); ?></p>
                        <p class="text-info">
                            <code><?= site_url('social_auth/endpoint?hauth_done=XXXXXX'); ?></code><br>
                            <code><?= base_url('index.php/social_auth/endpoint?hauth_done=XXXXXX'); ?></code>
                        </p>
                        <p><?= lang('replace_xxxxxx_with_provider'); ?></p>
                        <p><strong><?= lang('enable_config_file'); ?></strong></p>
                        <p><code>app/config/hybridauthlib.php</code></p>
                        <p><?= lang('documentation_at'); ?>: <a href="http://hybridauth.github.io/hybridauth/userguide.html" target="_blank">http://hybridauth.github.io/hybridauth/userguide.html</a></p>
                    </div>
                </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</div>
