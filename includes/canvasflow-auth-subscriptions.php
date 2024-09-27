<?php
class Canvasflow_Auth_Subscriptions {
    public static function get_valid_subscriptions($user_id) {
        try {
            $has_subscription = wcs_user_has_subscription($user_id);
            if(!$has_subscription) {
                return array(
                    'user_id' => $user_id,
                    'has_subscription' => $has_subscription
                );
            }

            $subscriptions = wcs_get_users_subscriptions($user_id); 
            $valid_subscriptions = array();
            foreach ( $subscriptions as $sub_id => $subscription ) {
                if ( $subscription->get_status() == 'active' ) {
                    $end_date = $subscription->get_date('end');
                    array_push($valid_subscriptions, array(
                        'end_date' => $end_date
                    ));
                }
            }
            
            
            return array(
                'user_id' => $user_id,
                'has_subscription' => $has_subscription,
                'subscriptions' => $valid_subscriptions
            );
        } catch (Exception $e) {
            return array();
        }
    }

    static function has_active_subscription($user_id) {
        if( $user_id == 0 ) 
            return false;

    
        global $wpdb;
    
        // Get all active subscriptions count for a user ID
        $count_subscriptions = $wpdb->get_var("
            SELECT count(p.ID)
            FROM {$wpdb->prefix}posts as p
            JOIN {$wpdb->prefix}postmeta as pm 
                ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_subscription' 
            AND p.post_status = 'wc-active'
            AND pm.meta_key = '_customer_user' 
            AND pm.meta_value > 0
            AND pm.meta_value = '$user_id'
        ");
    
        return $count_subscriptions == 0 ? false : true;
    }
}
?>

