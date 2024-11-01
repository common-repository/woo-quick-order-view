<?php 
/*
Plugin Name: WooCommerce Quick Order View
Plugin URI: http://www.webnware.com
Description:This plugin allows admin to view orders on orders listing page rather than going to order detail page.There will be a link called quick view which will appear on hovering the order title in the order listing page.Admin can also view multiple orders using bulk actions.
Version: 1.0.1
Author: Webnware
Author URI: http://www.webnware.com
*/
add_action( 'plugins_loaded', 'wqov_load_textdomain' );
function wqov_load_textdomain() {
  load_plugin_textdomain( 'woocommerce-quick-order-view', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action('wp_ajax_wqov_load_user_order_info','wqov_load_user_order_info_callback');
function wqov_load_user_order_info_callback(){
	
	if(isset($_REQUEST['user_id']))
	{
			 wqov_common_css_styles();
			 $customer_orders = get_posts( array(
													'numberposts' => -1,
													'meta_key'    => '_customer_user',
													'meta_value'  => (int)$_REQUEST['user_id'],
													'post_type'   => wc_get_order_types(),
													'post_status' => array_keys( wc_get_order_statuses() ),
											) );
			if(count($customer_orders)>0)
			{
				
				foreach($customer_orders as $key=>$order_info)
				{
					if((int)$order_info->ID>0){
						wqov_prepare_quick_order_view_data($order_info->ID);
					}
					
				}
				
			}else{
				 
				echo '<div class="order-detail-section no-orders-message">'.__('Sorry, No order found.', 'woocommerce-quick-order-view').'</div>';
				}									
	}
	exit(0);
}

add_action('wp_ajax_wqov_load_order_info','wqov_load_order_info_callback');
function wqov_load_order_info_callback(){

if(isset($_REQUEST['order_ids']) && !empty($_REQUEST['order_ids'])){
			wqov_common_css_styles();	
		    $order_id_req = rtrim($_REQUEST['order_ids'],',');
			$order_ids = explode(',',$order_id_req); 
			if(0<count($order_ids)){
				
			 foreach($order_ids as $order_id){
					if((int)$order_id>0){
				 		wqov_prepare_quick_order_view_data($order_id);
				 	}
				}	
			}
	 }
   	 	
	 exit(0);
	
}

function wqov_prepare_quick_order_view_data($order_id)
{
	if (is_admin()) {

		$order = wc_get_order($order_id);
		echo '<div class="wqov_order_row">';
			include 'order-information.php';
		echo '</div><hr class="wqov_order_row_end">';
	}
}

add_filter('post_row_actions','wqov_admin_action_row', 10, 2);

function wqov_admin_action_row($actions, $post){
    //check for your post type
    if ($post->post_type =="shop_order"){
        $actions['wqov_quick_order_view'] = '<a order_id="'.$post->ID.'" class="wqov_quick_order_view_link" href="javascript:void(0);">'.__('Quick View', 'woocommerce-quick-order-view').'</a>';
     }
    return $actions;
}

add_action('admin_footer-edit.php', 'wqov_admin_footer_function');
function wqov_admin_footer_function() {
	
	global $post_type;
 
  if($post_type == 'shop_order') 
  {
	  
	?>    
    <a href="#TB_inline?width=100%&inlineId=wqov_quick_order_view_popup" id="wqov_quick_order_view_popup_link" class="thickbox" style="display:none;"></a>	
    <div id="wqov_quick_order_view_popup" style="display:none;">
 	</div>
    
	<script>
	function get_wqov_order_popup_data(order_id){
		wqov_ajax_start('');
		jQuery.ajax({
		  type:"POST",
		url: '<?php echo admin_url('admin-ajax.php');?>',
		data: {
			'action':'wqov_load_order_info',
			'order_ids':order_id
		},
		success:function(data) {
			// This outputs the result of the ajax request
			wqov_ajax_stop()
			jQuery("#wqov_quick_order_view_popup").html(data);
			jQuery('#wqov_quick_order_view_popup_link').trigger('click');
			var TB_window = jQuery('#TB_window');
			var TB_ajaxContent = jQuery('#TB_ajaxContent');
			TB_ajaxContent.css('width','96%');
			TB_window.css('width','80%');
			//var  TB_window_margin_left =  TB_window.css('margin-left').replace('px','')-200;;
			//TB_window.css('margin-left',TB_window_margin_left+'px');
		   
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
		}); 
	}
	
	jQuery(".wqov_quick_order_view_link").click(function(){
		 var order_id = jQuery(this).attr("order_id");
		 get_wqov_order_popup_data(order_id);
	});
	
	jQuery(document).ready(function() {
	
		jQuery('<option>').val('wqov_quick_order_view').text('<?php _e('Quick View', 'woocommerce-quick-order-view')?>').appendTo("select[name='action']");
		jQuery('<option>').val('wqov_quick_order_view').text('<?php _e('Quick View', 'woocommerce-quick-order-view')?>').appendTo("select[name='action2']");
		
		jQuery('#doaction , #doaction2 ').click(function(){
			
			var which_clicked = jQuery(this).attr('id') ; 
			
			if(which_clicked == 'doaction')
				var current_bulk_action = jQuery("select[name='action']").val();
			else
				var current_bulk_action = jQuery("select[name='action2']").val();
			
			if(current_bulk_action == 'wqov_quick_order_view'){
		   
			 if(jQuery('input[name="post[]"]:checked').length>0){
					var order_ids = '';
					 jQuery('input[name="post[]"]:checked').each(function(){
						 
						 order_ids +=jQuery(this).val()+',';
					 });
					 get_wqov_order_popup_data(order_ids);
				 }else
				 {
				   alert('<?php _e('Please select atleast one order for quick view.', 'woocommerce-quick-order-view')?>');	 
				 }
				  return false;
			}
			
			});
	});
	
	

</script>

	<?php 
	wqov_common_scripts();		
  }
}


function wqov_add_thickbox_script()
{
	$screen = get_current_screen();
	
	if($screen->id == 'users'){
			add_thickbox();
	}
	if(is_admin() &&  isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'shop_order')
	 {   add_thickbox();
	 }
  
}
add_action('admin_head','wqov_add_thickbox_script');


/********************** user orders******************/
function wqov_user_quick_order_view_link($actions, $user_object) {

	 $actions['wqov_user_quick_order_view'] = '<a user_id="'.$user_object->ID.'" class="wqov_user_quick_order_view" href="javascript:void(0);">'.__('View Orders', 'woocommerce-quick-order-view').'</a>';

	return $actions;

}
add_filter('user_row_actions', 'wqov_user_quick_order_view_link', 11, 2);

add_action('admin_footer-users.php', 'wqov_users_admin_footer_function');
function wqov_users_admin_footer_function() {
	
	
	  
	?>    
    <a href="#TB_inline?width=100%&inlineId=wqov_user_quick_order_view_popup" id="wqov_user_quick_order_view_popup_link" class="thickbox" style="display:none;"></a>	
    <div id="wqov_user_quick_order_view_popup" style="display:none;">
 	</div>
    
	<script>
	function get_wqov_user_order_popup_data(user_id){
		wqov_ajax_start('');
		jQuery.ajax({
		  type:"POST",
		url: '<?php echo admin_url('admin-ajax.php');?>',
		data: {
			'action':'wqov_load_user_order_info',
			'user_id':user_id
		},
		success:function(data) {
			// This outputs the result of the ajax request
			wqov_ajax_stop()
			jQuery("#wqov_user_quick_order_view_popup").html(data);
			jQuery('#wqov_user_quick_order_view_popup_link').trigger('click');
			var TB_window = jQuery('#TB_window');
			var TB_ajaxContent = jQuery('#TB_ajaxContent');
			TB_ajaxContent.css('width','96%');
			TB_window.css('width','80%');
			//var  TB_window_margin_left =  TB_window.css('margin-left').replace('px','')-200;;
			//TB_window.css('margin-left',TB_window_margin_left+'px');
		   
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
		}); 
	}
	
	jQuery(".wqov_user_quick_order_view").click(function(){
		 var user_id = jQuery(this).attr("user_id");
		 get_wqov_user_order_popup_data(user_id);
	});
	
</script>
	<?php 
wqov_common_scripts();	
  
}
/********************** user orders******************/

function wqov_common_scripts(){?>
<script>
	function wqov_ajax_start(text)
	{
		if(jQuery('body').find('#resultLoading').attr('id') != 'resultLoading'){
			jQuery('body').append('<div id="resultLoading" style="display:none"><div>Please wait...</div><div class="bg"></div></div>');
		}
		
		jQuery('#resultLoading').css({
			'width':'100%',
			'height':'100%',
			'position':'fixed',
			'z-index':'10000000',
			'top':'0',
			'left':'0',
			'right':'0',
			'bottom':'0',
			'margin':'auto'
		});
		jQuery('#resultLoading .bg').css({
			'background':'#000000',
			'opacity':'0.7',
			'width':'100%',
			'height':'100%',
			'position':'absolute',
			'top':'0'
		});
		jQuery('#resultLoading>div:first').css({
			'width': '250px',
			'height':'75px',
			'text-align': 'center',
			'position': 'fixed',
			'top':'0',
			'left':'0',
			'right':'0',
			'bottom':'0',
			'margin':'auto',
			'font-size':'16px',
			'z-index':'10',
			'color':'#ffffff'
		});
		jQuery('#resultLoading .bg').height('100%');
		jQuery('#resultLoading').fadeIn(300);
		jQuery('body').css('cursor', 'wait');
	}
	
	function wqov_ajax_stop()
	{
		jQuery('#resultLoading .bg').height('100%');
		jQuery('#resultLoading').fadeOut(300);
		jQuery('body').css('cursor', 'default');
	}

</script>
<script>
       jQuery(document).ready(function() {
        jQuery(document).delegate(".tabs-menu a","click",function(event) {
            event.preventDefault();
            jQuery(this).parent().addClass("current");
            jQuery(this).parent().siblings().removeClass("current");
            var tab = jQuery(this).attr("href");
			 jQuery(this).parents('.order-detail-tabs').find(".tab-content").not(tab).css("display", "none");
            jQuery(this).parents('.order-detail-tabs').find(tab).fadeIn();
        });
    });
    
</script>
<?php }

function wqov_common_css_styles(){?>
	<style>

.order-detail-head{ 
  background:#000000;
  border-radius:5px;
  max-width:300px;
  margin-top: 20px; 
  margin-bottom:10px;
  width:100%;
  display:inline-block;}
  
.order-detail-head h2{ 
  font-size:20px; 
  padding:12px 20px;
  color:#FFFFFF!important; 
  text-transform:uppercase;
  margin: 0 !important;
  }
#TB_ajaxContent{
	height:85vh!important;
}
#TB_window{
	margin: 0 auto!important;
	top:10px !important;
    left: 0;
    right: 0;
}
.order-detail-head h2{}  
  
.order-detail-main{  background:#EEEEEE;  padding:0px 20px 1px 20px;}
 
.order-detail-content-box{ padding:10px 0px;}   
  
.order-detail-content{ display:inline-block;  width:49%;  vertical-align:top;  text-align:left;margin-bottom:10px; margin-top:10px;}

.order-detail-content span{  font-size:14px;  color:#666666;  display:block;  padding-bottom:5px;}

.order-detail-btn{  vertical-align:top;  width:50%;  text-align:right;

  background:#23282D;
  font-size:16px;
  color:#FFFFFF;
  padding:15px 5px;
  border-radius:5px;
  max-width:225px;
  width:100%;
  text-decoration:none;
  text-align:center;
  text-transform:uppercase;
  display:inline-block;}

.order-detail-btn a:hover{ background:#00CCFF;}  
 
.tabs-menu {height:30px;}

.tabs-menu li {height:30px; line-height:30px; display:inline-block; margin-right: 10px;}

.tabs-menu li.current {position: relative; background-color: #fff; z-index: 5;}

.tabs-menu li a {padding:10px 20px; color:#000000; box-shadow:none; text-decoration:none; }

.tabs-menu .current a {color: #2e7da3;}

.tab{background-color:#fff; margin-bottom:20px; width:auto;  font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;}

.tab-content{padding:20px; display: none;}

.billing-address .billing-title, .shipping-title{ color:#23282d;}

.tab-1 {display: block;}

.order-product table { border:1px solid #000000; border-bottom:0px;border-collapse:collapse;}

.order-product table thead th{
    padding:5px 8px; 
	text-align:left!important; 
	
	border:1px solid #000000;  
	border-left:0px; 
	border-top:0px;}

.order-product table thead th:last-child{ border-right:0px;}

.order-product table .td:last-child{ border-right:0px;}

.order-product table tbody td {
   text-align:left!important;  
   padding:5px 8px; 
 
   border:1px solid #000000;
   border-left:0px; 
   border-top:0px;
   }

.td{
  padding:5px 8px; 
  border:1px solid #000000; 
  border-top:0px; 
  border-left:0px;}

.sssss{ padding-top:10px;}
.wqov-order-totals-items{
 	margin-top:20px;
}
.wqov-used-coupons{
	float: left;
    width: 60%;
	text-align: left;
}
.wqov-order-totals{
	float: right;
    width: 40%;
    margin: 0;
    padding: 0;
    text-align: right;
}
.order-product small.refunded {
	display: block;
	color: #a00;
	white-space: nowrap;
	margin-top: .5em;
}
.order-product small.refunded:before {
    font-family: Dashicons;
    speak: none;
    font-weight: 400;
    font-variant: normal;
    text-transform: none;
    -webkit-font-smoothing: antialiased;
    text-indent: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    content: "";
    position: relative;
    top: auto;
    left: auto;
    margin: -1px 4px 0 0;
    vertical-align: middle;
    line-height: 1em;
}
.order-product .refunded-total {
    color: #a00;
}

.order-product .thumb{
	width:56px;
}
.order-product .thumb .wqov-order-item-thumbnail {
    width: 48px;
    height: 48px;
    border: 2px solid #e8e8e8;
    background: #f8f8f8;
    color: #ccc;
    position: relative;
    font-size: 21px;
    display: block;
    text-align: center;
	margin-bottom: 5px
}

.order-product .thumb .wqov-order-item-thumbnail img {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    position: relative;
}
.no-orders-message{
	color:#C00;
	margin-top:30px;
	margin-left:30px;
	
	
}
ul.order_notes li.system-note .note_content {
    background: #d7cad2;
}
ul.order_notes li .note_content {
    padding: 10px;
    background: #efefef;
    position: relative;
}
</style>
<?php }
?>