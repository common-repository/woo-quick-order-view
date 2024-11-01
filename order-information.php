<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $wpdb;
$post = $order->post;
$order_number = $order->get_order_number() ;
if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = array();
		}

		$payment_method = ! empty( $order->payment_method ) ? $order->payment_method : '';

?>
   <div class="order-detail-section">
         <div class="order-detail-head">
              <h2><?php echo esc_html( sprintf( __('Order', 'woocommerce-quick-order-view').' %s #%s '. __('details', 'woocommerce-quick-order-view'), $order_type_object->labels->singular_name, $order_number ) ); ?> </h2>
         </div>
         <div class="order-detail-main">
             <div class="order-detail-content-box">
                   <div class="order-detail-content">
                  <span><?php

					if ( $payment_method ) {
						printf( __( 'Payment via', 'woocommerce-quick-order-view' ).' %s', ( isset( $payment_gateways[ $payment_method ] ) ? esc_html( $payment_gateways[ $payment_method ]->get_title() ) : esc_html( $payment_method ) ) );

						if ( $transaction_id = $order->get_transaction_id() ) {
								if ( isset( $payment_gateways[ $payment_method ] ) && ( $url = $payment_gateways[ $payment_method ]->get_transaction_url( $order ) ) ) {
								echo ' (<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>)';
							} else {
								echo ' (' . esc_html( $transaction_id ) . ')';
							}
						}
						echo '. ';
					}

					if ( $ip_address = get_post_meta( $post->ID, '_customer_ip_address', true ) ) {
						echo __( 'Customer IP', 'woocommerce-quick-order-view' ) . ': ' . esc_html( $ip_address );
					}
				?></span>

                     <span>Order date: <b><?php echo date_i18n( 'Y-m-d', strtotime( $post->post_date ) ); ?> @ <?php echo date_i18n( 'H', strtotime( $post->post_date ) ); ?>:<?php echo date_i18n( 'i', strtotime( $post->post_date ) ); ?></b></span>
                     <span>Order status: <b><?php echo ucfirst(esc_html($order->get_status())); ?></b></span>
                   </div>
                   
              </div>  
             <div class="order-detail-tabs">
                 <ul class="tabs-menu">
                    <li class="current"><a href="#tab-1-<?php echo $order_number ?>"><?php _e( 'Items', 'woocommerce-quick-order-view' ); ?></a></li>
                    <li><a href="#tab-2-<?php echo $order_number ?>"><?php _e( 'Billing Address', 'woocommerce-quick-order-view' ); ?></a></li>
                    <li><a href="#tab-3-<?php echo $order_number ?>"><?php _e( 'Shipping Address', 'woocommerce-quick-order-view' ); ?></a></li>
					<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' == get_option( 'woocommerce_enable_order_comments', 'yes' ) ) && $post->post_excerpt ) { ?>
                    	<li><a href="#tab-4-<?php echo $order_number ?>"><?php _e( 'Customer Notes', 'woocommerce-quick-order-view' ); ?></a></li>
					<?php } ?>
                    <li><a href="#tab-5-<?php echo $order_number ?>"><?php _e( 'Order Notes', 'woocommerce-quick-order-view' ); ?></a></li>                
                </ul>
                 <div class="tab">
                    <div id="tab-1-<?php echo $order_number ?>" class="tab-content tab-1">
                         <div class="order-product">
                           <table cellspacing="0" cellpadding="0" style="width: 100%;">
                                <thead>
                                    <tr>
										<th class="td" scope="col" ><?php _e( 'Image', 'woocommerce-quick-order-view' ); ?></th>
                                        <th class="td" scope="col"><?php _e( 'Product', 'woocommerce-quick-order-view' ); ?></th>
                                        <th class="td" scope="col"><?php _e( 'Quantity', 'woocommerce-quick-order-view' ); ?></th>
                                        <th class="td" scope="col"><?php _e( 'Unit Price', 'woocommerce-quick-order-view' ); ?></th>
                                        <th class="td" scope="col"><?php _e( 'Total', 'woocommerce-quick-order-view' ); ?></th>

                                    </tr>
                                </thead>
                                
                                <tbody>
								<?php 
								$items = $order->get_items();
                                foreach ($items as $item_id => $item ) :
								 
                                    $_product     =  $order->get_product_from_item( $item );
                                    $item_meta    = new WC_Order_Item_Meta( $item, $_product );
									$product_link  = $_product ? admin_url( 'post.php?post=' . absint( $_product->id ) . '&action=edit' ) : '';
									$thumbnail     = $_product ?$_product->get_image( 'thumbnail', array( 'title' => '' ), false ) : '';
									$tax_data      = empty( $legacy_order ) && wc_tax_enabled() ? maybe_unserialize( isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '' ) : false;
									$item_total    = ( isset( $item['line_total'] ) ) ? esc_attr( wc_format_localized_price( $item['line_total'] ) ) : '';
									$item_subtotal = ( isset( $item['line_subtotal'] ) ) ? esc_attr( wc_format_localized_price( $item['line_subtotal'] ) ) : '';
								?>
                                    <tr class="item" data-order_item_id="<?php echo $item_id; ?>">
                                        <td class="thumb">
                                            <?php
                                                echo '<div  class="wqov-order-item-thumbnail"><img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" alt="' . esc_attr__( 'Product Image', 'woocommerce-quick-order-view' ) . '" height="100" width="100" style="vertical-align:middle; margin-right: 10px;" /></div>';
                                            ?>
                                        </td>
                                        <td class="name" data-sort-value="<?php echo esc_attr( $item['name'] ); ?>">
                                            <?php
                                                echo $product_link ? '<a href="' . esc_url( $product_link ) . '" class="wc-order-item-name">' .  esc_html( $item['name'] ) . '</a>' : '<div class="class="wc-order-item-name"">' . esc_html( $item['name'] ) . '</div>';
                                    
                                                if ( $_product && $_product->get_sku() ) {
                                                    echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'woocommerce-quick-order-view' ) . '</strong> ' . esc_html( $_product->get_sku() ) . '</div>';
                                                }
                                    
                                                if ( ! empty( $item['variation_id'] ) ) {
                                                    echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'woocommerce-quick-order-view' ) . '</strong> ';
                                                    if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
                                                        echo esc_html( $item['variation_id'] );
                                                    } elseif ( ! empty( $item['variation_id'] ) ) {
                                                        echo esc_html( $item['variation_id'] ) . ' (' . __( 'No longer exists', 'woocommerce-quick-order-view' ) . ')';
                                                    }
                                                    echo '</div>';
                                                }
                                            ?>
                                        
                                        </td>
                                    	
                                        <td class="quantity" >
                                            <div class="view">
                                                <?php
                                                    echo '<small class="times">&times;</small> ' . ( isset( $item['qty'] ) ? esc_html( $item['qty'] ) : '1' );
                                    				if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
														echo '<small class="refunded">' . ( $refunded_qty * -1 ) . '</small>';
													}
                                                    
                                                ?>
                                            </div>
                                          
                                        </td>
                                          <td class="item_cost" >
                                            <div class="view">
                                                <?php
                                                    if ( isset( $item['line_total'] ) ) {
                                                        echo wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false )), array( 'currency' => $order->get_order_currency() ) );
                                    
                                                    }
												
                                                ?>
                                            </div>
                                        </td>
                                        <td class="line_cost"  data-sort-value="<?php echo esc_attr( isset( $item['line_total'] ) ? $item['line_total'] : '' ); ?>">
                                            <div class="view">
                                            <?php 
													echo $order->get_formatted_line_subtotal( $item );
													if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
														echo '<small class="refunded">' . wc_price( $refunded, array( 'currency' => $order->get_order_currency() ) ) . '</small>';
													}
											 ?>
                                            </div>
                                            
                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
							 </tbody>
                             
                          </table> 
                          <div class="wqov-order-totals-items">
								<?php
                                    $coupons = $order->get_items( array( 'coupon' ) );
                                    if ( $coupons ) {
                                        ?>
                                        <div class="wqov-used-coupons">
                                            <ul class="wc_coupon_list"><?php
                                                echo '<li><strong>' . __( 'Coupon(s) Used', 'woocommerce-quick-order-view' ) . '</strong></li>';
                                                foreach ( $coupons as $item_id => $item ) {
                                                    $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) );
                            
                                                    $link = $post_id ? add_query_arg( array( 'post' => $post_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) : add_query_arg( array( 's' => $item['name'], 'post_status' => 'all', 'post_type' => 'shop_coupon' ), admin_url( 'edit.php' ) );
                            
                                                    echo '<li class="code"><a href="' . esc_url( $link ) . '" class="tips" data-tip="' . esc_attr( wc_price( $item['discount_amount'], array( 'currency' => $order->get_order_currency() ) ) ) . '"><span>' . esc_html( $item['name'] ). '</span></a></li>';
                                                }
                                            ?></ul>
                                        </div>
                                        <?php
                                    }
                                ?>
                                <table class="wqov-order-totals">
                                    <tr>
                                        <td class="label td" scope="row"><b><?php _e( 'Subtotal', 'woocommerce-quick-order-view' ); ?>:</b></td>
                                        
                                        <td class="total td" scope="row">
                                            <?php echo $subtotal = $order->get_subtotal_to_display( false, false ); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label td" scope="row"><b><?php _e( 'Discount', 'woocommerce-quick-order-view'); ?>:</b></td>
                                        
                                        <td class="total td" scope="row">
                                            <?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_order_currency() ) ); ?>
                                        </td>
                                    </tr>
                            
                            
                            	<?php if(method_exists($order,'get_total_shipping_refunded')){ ?>	
                                    <tr>
                                        <td class="label td" scope="row"><b><?php _e( 'Shipping', 'woocommerce-quick-order-view' ); ?>:</b></td>
                                        
                                        <td class="total td" scope="row"><?php
                                            if ( ( $refunded = $order->get_total_shipping_refunded() ) > 0 ) {
                                                echo '<del>' . strip_tags( wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_order_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_total_shipping() - $refunded, array( 'currency' => $order->get_order_currency() ) ) . '</ins>';
                                            } else {
                                                echo wc_price( $order->get_total_shipping(), array( 'currency' => $order->get_order_currency() ) );
                                            }
                                        ?></td>
                                    </tr>
                                    <?php } ?>
                            
                                    <?php if ( wc_tax_enabled() ) : ?>
                                        <?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
                                            <tr>
                                                <td class="label td" scope="row"><b><?php echo $tax->label; ?>:</b></td>
                                                
                                                <td class="total td" scope="row"><?php
                                                    if ( ( $refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ) > 0 ) {
                                                        echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( WC_Tax::round( $tax->amount, wc_get_price_decimals() ) - WC_Tax::round( $refunded, wc_get_price_decimals() ), array( 'currency' => $order->get_order_currency() ) ) . '</ins>';
                                                    } else {
                                                        echo $tax->formatted_amount;
                                                    }
                                                ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                            
                                    
                            
                                    <tr>
                                        <td class="label td" scope="row"><b><?php _e( 'Total', 'woocommerce-quick-order-view' ); ?>:</b></td>
                                    
                                        <td class="total td" scope="row">
                                            <div class="view"><?php echo $order->get_formatted_order_total(); ?></div>
                                        </td>
                                    </tr>
                            
                                    
                            	<?php if($order->get_total_refunded()>0){?>
                                    <tr>
                                        <td class="label refunded-total td" scope="row"><b><?php _e( 'Refunded', 'woocommerce-quick-order-view'); ?>:</b></td>
                                    
                                        <td class="total refunded-total td" scope="row">-<?php echo wc_price( $order->get_total_refunded(), array( 'currency' => $order->get_order_currency() ) ); ?></td>
                                    </tr>
                            	<?php } ?>
                                    
                            
                                </table>
                                <div class="clear"></div>
                        </div>
                      </div>
                    </div>
                    <div id="tab-2-<?php echo $order_number ?>" class="tab-content">
                        <div class="billing-address">
					       <?php echo $order->get_formatted_billing_address(); 
                          
								if ( $order->billing_email ) {
									echo '<p><strong>Email:</strong> ' . make_clickable( esc_html( $order->billing_email ) ) . '</p>';
								}
								
								if ( $order->billing_phone ) {
									echo '<p><strong>Phone:</strong> ' . make_clickable( esc_html( $order->billing_phone ) ) . '</p>';
								}
							?>
                           
                        </div>
                    </div>
                    <div id="tab-3-<?php echo $order_number ?>" class="tab-content">
                        <div class="shipping-address">
                                <?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
                                   <?php echo $shipping; ?>                                      
                                <?php endif; ?>
                       </div>
                    </div>
                    <?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' == get_option( 'woocommerce_enable_order_comments', 'yes' ) ) && $post->post_excerpt ) { ?>
                    <div id="tab-4-<?php echo $order_number ?>" class="tab-content">
                        <div class="customer-box">
		                    <?php 		
									echo nl2br( esc_html( $post->post_excerpt ) );
								
                            ?>
                       </div>
                    </div>
                    <?php } ?>
                    <div id="tab-5-<?php echo $order_number ?>" class="tab-content">
                       <div class="order-box">
		                  <?php 		
                            $args = array(
                                'post_id'   => $order->id,
                                'orderby'   => 'comment_ID',
                                'order'     => 'DESC',
                                'approve'   => 'approve',
                                'type'      => 'order_note'
                            );

														remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

														$notes = get_comments( $args );

														add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
												
														echo '<ul class="order_notes">';
												
														if ( $notes ) {
												
															foreach( $notes as $note ) {
												
																$note_classes   = array( 'note' );
																$note_classes[] = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? 'customer-note' : 'system';
																$note_classes[] = $note->comment_author === __( 'WooCommerce', 'woocommerce' ) ? 'system-note' : '';
																
																?>
																<li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo esc_attr( implode( ' ', $note_classes ) ); ?>">
																	<div class="note_content">
																		<?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
																	</div>
																	<p class="meta">
																		<abbr class="exact-date" title="<?php echo $note->comment_date; ?>"><?php printf( __( 'added on', 'woocommerce-quick-order-view' ).' %1$s '. __( 'at', 'woocommerce-quick-order-view' ).' %2$s', date_i18n( wc_date_format(), strtotime( $note->comment_date ) ), date_i18n( wc_time_format(), strtotime( $note->comment_date ) ) ); ?></abbr>
																		<?php if ( $note->comment_author !== __( 'WooCommerce', 'woocommerce' ) ) printf( ' ' . __( 'by', 'woocommerce-quick-order-view' ).' %s', $note->comment_author ); ?>
																		
																	</p>
																</li>
																<?php
															}
												
														} else {
															echo '<li>' . __( 'There are no notes yet.', 'woocommerce-quick-order-view' ) . '</li>';
														}
												
														echo '</ul>'; 
												?>
                                                       
                        
                        
                    	</div>
                    </div>
                    
                </div>
             </div>
         </div>
   </div>