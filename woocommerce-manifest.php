<?php
/*
Plugin Name: WooCommerce print manifest
Plugin URI: http://www.vaibhavign.com
Description: Print manifest
Version: 0.1
Author: Vaibhav Sharma
Author Email: http://www.vaibhavign.com
*/

/**
 * Copyright (c) `date "+%Y"` Vaibhav Sharma. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Manifest{
    public function __construct(){
        register_activation_hook( __FILE__, array( $this, 'createTables' ));
        add_action('admin_menu', array( &$this, 'woocommerce_manifest_admin_menu' )); 
        add_action('wp_ajax_my_actions', array(&$this, 'my_actions_callback'));
        add_action('wp_ajax_my_orderaction',array(&$this,'my_orderaction_callback'));
    }
    
    function my_orderaction_callback(){
        global $wpdb;
        $oI = $_POST["orderId"];
       
        $aramax = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."postmeta WHERE `meta_value` =  '$oI'"); 
       // echo '<pre>';
       // print_r($aramax);
        if(count($aramax)>0){
        $theorder = new WC_Order( $aramax[0]->post_id );    
        } else {
        $theorder = new WC_Order( $_POST['orderId'] );
        }
         echo '<tr id="'.$theorder->id.'tr">
             <td style="padding:7px 7px 8px; "><input style="margin:0 0 0 8px;" type="checkbox" name="check[]" value="'.$theorder->id.'" /></td>
             <td style="padding:7px 7px 8px; ">'.$theorder->order_custom_fields['_tracking_number'][0].'</td>
             <td style=" padding:7px 7px 8px; ">'.$theorder->id.'</td>
             <td style="padding:7px 7px 8px; ">'.$theorder->order_date.'</td>
             <td style="padding:7px 7px 8px; ">'.$theorder->shipping_first_name.'</td>
             <td style="padding:7px 7px 8px; ">'.$theorder->order_total.'</td>
             <td style="padding:7px 7px 8px; ">'.$theorder->payment_method.'</td>
             <td style="padding:7px 7px 8px; ">'.$theorder->status.'</td>
             <td style="padding:7px 7px 8px; "><a class="rem" rel="'.$theorder->id.'">Remove</a></td>
             </tr>';
             exit;
     }

    function my_actions_callback(){
      global $woocommerce,$wpdb;
      $coutn = 0;
      $orderString = '';
      
      $terms1 = get_term_by('slug', 'readyshipped', 'shop_order_status');
      $aramax = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_relationships WHERE `term_taxonomy_id` = ".$terms1->term_taxonomy_id);
      foreach($aramax as $key=>$val){  
        $meta_values = get_post_meta($val->object_id);
        $meta_values['_tracking_provider'][0];
        if($meta_values['_tracking_provider'][0]==$_POST['valselected']){
            $orderString .= $val->object_id.'('.$meta_values["_tracking_number"][0].'),';
            $onlyOrders  .= $val->object_id.',';
            $onlyShipments .= $meta_values["_tracking_number"][0].',';
            $coutn++;
        }
           
      }

     echo $coutn.'$'.substr($orderString,0,-1).'$'.substr($onlyOrders,0,-1).'$'.substr($onlyShipments,0,-1);
     exit;
    }
    
    /**
     * creating dropdown of shipping providers
     * @return string
     */
    
    function getShippingProvider(){
        $shippingArray = array('select'=>'select','blue-dart'=>'bluedart','aramex'=>'aramex','quantium'=>'quantium','indiapost'=>'indiapost','dtdc'=>'dtdc');
        $selection = "<select id='selectprovider' name='selectprovider' >";
        foreach($shippingArray as $key=>$val){
            $selection .= "<option value='$key'>$val</option>";
        }
        
        $selection .= '</select>';
        return $selection; 
    }
    
       /**
        * 
        * Create admin menu page
        */ 
                            
       function woocommerce_manifest_admin_menu() {
              add_menu_page(__('Manifest','wc-checkout-cod-pincodes'), __('Manifest','wc-checkout-cod-pincodes'), 'manage_options', 'eshopbox-manifest', array( &$this, 'eshopbox_manifest_page' ) );
         //  add_submenu_page('woocommerce', __( 'Manifest', 'wc-checkout-cod-pincodes' ), __( 'Manifest', 'woocommerce-manifest' ), 'manage_woocommerce', 'woocommerce-manifest', array( &$this, 'woocommerce_manifest_page' ) );
	}
        
        /**
         * Create admin manifest page
         * @global type $woocommerce
         */

 	function eshopbox_manifest_page() {
            global $woocommerce;
        	if ( !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-pip' ) );
		}
               
                wp_enqueue_media();
                // when form post 
                
                if($_POST['manifest']=='true'){
                    if(count($_POST['check'])>0){
                        
                      foreach($_POST['check'] as $key=>$value)
{
 $order_id=$value;
 $orderobj=new WC_Order($order_id);
 $orderobj->update_status('shipped');
 $orderStrings .= $value.',';
}
  
                }
                    
                }
            ?>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e( 'Create Manifest', 'wc-checkout-cod-pincodes' ); ?></h2>
    <?php if(count($_POST['check'])>0){     
    ?>
    <div id="message" class="updated fade"><p><strong><?php _e( 'Order id '.substr($orderStrings,0,-1).'  have been marked as shipped.', 'wc_shipment_tracking' ); ?></strong></p></div>
    <?php } ?>
    <p><?php // do nothing ?></p>
    
    <div id="content">
        <input type="hidden" name="cod_fields_submitted" value="submitted">
        <div id="poststuff">
            <div class="postbox">
                <h3 class="hndle"><?php _e( 'Manifest', 'woocommerce-pip' ); ?></h3>
                <div class="inside pip-preview">
                    <table class="form-table">
                        <tr>
                            <th>
                                 <label for="woocommerce_cod_aramex"><b><?php _e( 'Select provider :', 'woocommerce-manifest' ); ?></b></label>
                             </th>
                             <td>
                                 <?php echo $this->getShippingProvider();  ?><br />
                             </td>
                       </tr>

                       <tr>
                            <th>
                                <label for="woocommerce_cod_aramex"><b><?php _e( 'Pending Shipments :', 'woocommerce-manifest' ); ?></b></label>
                            </th>
                            <td id="noshipments">0
                            </td>
                       </tr> 
                       <tr>
                            <th>
                                 <label for="woocommerce_cod_aramex"><b><?php _e( 'Order ids :', 'woocommerce-manifest' ); ?></b></label>
                            </th>
                            <td id="noorders">Nil
                            </td>
                       </tr> 
                       <tr>
                            <th>
                                 <label for="woocommerce_cod_aramex"><b><?php _e( 'Enter order id :', 'woocommerce-manifest' ); ?></b></label>
                            </th>
                            <td id="ordertext">
                                <input type="text" name="ordert" id="ordert" />
                            </td>
                       </tr>  
                       </table>
                       </div>
                       </div>
       </div>
    </div>
</div> 
<form name="manifestform" id="manifestform" style="margin:4px 15px 0 0;" method="post" action="<?php echo $this->plugin_url() ?>/printmanifest.php" target="_blank">                               
    <div id="manifesttable">
    <input type="submit" id="submit" class="button" name="submit" style="margin-bottom: 10px; margin-right:20px" value="Create Manifest" />
 <input type="submit" id="asd" class="button markasship" name="dsd" style="margin-bottom: 10px" value="Mark Ship" /> 
        <table width="100%" cellspacing="0" cellpadding="0" class="widefat">
            <thead>
                <tr>
        <th style="padding:7px 7px 8px; "><input type="checkbox" name="checkall" id="checkall" class="select-all" value="'.$theorder->id.'" /></th>
        <th style="padding:7px 7px 8px; ">AWB No</th>
        <th style="padding:7px 7px 8px; ">Order Id</th>
        <th style=" padding:7px 7px 8px;">Date</th>
        <th style="padding:7px 7px 8px;">Name</th>
        <th style="padding:7px 7px 8px;">Amount</th>
        <th style="padding:7px 7px 8px;">Payment Method</th>

        <th style="padding:7px 7px 8px;">Status</th>
        </tr></thead>
            <tfoot>
                <tr>
        <th style="padding:7px 7px 8px; "><input type="checkbox" class="select-all" name="checkall" id="checkall" value="'.$theorder->id.'" /></th>
        <th style="padding:7px 7px 8px; ">AWB No</th>
        <th style="padding:7px 7px 8px; ">Order Id</th>
        <th style=" padding:7px 7px 8px;">Date</th>
        <th style="padding:7px 7px 8px;">Name</th>
        <th style="padding:7px 7px 8px;">Amount</th>
        <th style="padding:7px 7px 8px;">Payment Method</th>

        <th style="padding:7px 7px 8px;">Status</th>
        </tr></tfoot>
       
   

    
    <tbody id="manifdetail"></tbody>
    </table>
</div>
<input type="hidden" id="shipprovider" name="shipprovider" value="" />
<input type="hidden" id="onlyorders" name="onlyorders" value="" />
<input type="hidden" id="onlyshipments" name="onlyshipments" value="" />
<input type="hidden" name="manifest" value="true" /> 
<input type="submit" id="submit" class="button" name="submit" style="margin-top: 10px; margin-right:20px" value="Create Manifest" />
<input type="submit" id="markasship" class="button markasship" name="markasship" style="margin-top: 10px;" value="Mark Ship" />

</form>

<?php          
// ajax call
$woocommerce->add_inline_js("
    jQuery(document).ready(function(){
    
jQuery('.markasship').on('click',function(event){
   // event.preventDefault();
   var checkcheck = 0;
        $(':checkbox').each(function() {
           if(this.checked == true){
               // alert('checked');
                checkcheck = 1;
            }
        });   
      if(checkcheck==1){
    jQuery('#manifestform').attr('action','');
    jQuery('#manifestform').attr('target','');
    

     $('#manifestform').submit();
} else {
alert('Please select a shipment');
return false;
}
});

jQuery('.rem').live('click',function(event){
    event.preventDefault();
   // alert('tining tining');
    jQuery('#'+jQuery(this).attr('rel')+'tr').remove();

});


$('.select-all').on('click',function(event) {   
    if(this.checked) {
        // Iterate each checkbox
        $(':checkbox').each(function() {
            this.checked = true;                        
        });
    }
});

          jQuery('#ordert').keyup(function(event){
          var tex = jQuery(this).val();
          var checkfl = 0;
              if(event.keyCode==13){
            //  alert(jQuery('#onlyshipments').val());
              orderString = jQuery('#onlyorders').val(); 
             // alert(orderString);
                var arrayOrders = orderString.split(',');
             //   alert(arrayOrders[0]);
              jQuery.each(arrayOrders,function(i,v){
              if(arrayOrders[i]==tex){
              checkfl = 1;
              }
               
                });
                
var arrayShipments = jQuery('#onlyshipments').val().split(',');
              jQuery.each(arrayShipments,function(i,v){
              if(arrayShipments[i]==tex){
              checkfl = 1;
              }
               
                });
                
if(checkfl==0){
alert('Invalid order/shipment id');
return false;
}
                
                  var textBoxText = jQuery(this).val();
                  jQuery(this).val('');
                  var orderData = {
                  action: 'my_orderaction',
                  orderId : textBoxText
               };

               jQuery.post(ajaxurl,orderData,function(response){
                      jQuery('#manifdetail').after(response);
               });

              }
          });
    });  

    jQuery('#selectprovider').bind('change',function(){
   
    jQuery('#shipprovider').val(jQuery(this).val());
      jQuery('#loadimg').show();
          var data = {
          action: 'my_actions',
          whatever: 1234,
          valselected : jQuery(this).val()
  };

jQuery.post(ajaxurl, data, function(response) {

         splitResponse = response.split('$');
         if(splitResponse[0]==0){
             alert('No pending shipments');
             jQuery('#noshipments').html('0');
             jQuery('#noorders').html('Nil');
             jQuery('#ordert').val('');

         } else {
         jQuery('#noshipments').html(splitResponse[0]);
         jQuery('#noorders').html(splitResponse[1]);
         jQuery('#onlyorders').val(splitResponse[2]);
         jQuery('#onlyshipments').val(splitResponse[3]);
         
         jQuery('#ordert').val('');
     }
  });
});

");  
}     

/**
     * Get the plugin url.
     *
     * @access public
     * @return string
     */
    public function plugin_url() {
        if ( $this->plugin_url ) return $this->plugin_url;
        return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     *
     * @access public
     * @return string
     */
    public function plugin_path() {
        if ( $this->plugin_path ) return $this->plugin_path;
        return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
  
    /**
     * 
     * create table for plugin
     */
    public function createTables(){
        global $wpdb;
        $table_name = $wpdb->prefix . "manifest"; 
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `orderid` varchar(255) NOT NULL,
        `dates` bigint(20) NOT NULL,
        `provider` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($sql);  
    }
}
new WC_Manifest();