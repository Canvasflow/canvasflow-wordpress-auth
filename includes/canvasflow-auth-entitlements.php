<?php
class Canvasflow_Auth_Entitlements {

    public function get_user_entitlements($user_id) {
        $subscriptions = $this->get_valid_subscriptions($user_id);

        $expiration_date = $this->get_nearest_expiration_date($subscriptions);
        $entitlements = $this->get_entitlements($subscriptions);

        return array(
            'entitlements' => $entitlements,
            'expiration_date' =>$expiration_date,
        );
    }

    private function get_valid_subscriptions($user_id) {
        $valid_subscriptions = array();
        $has_subscription = wcs_user_has_subscription($user_id);
        if(!$has_subscription) {
            return $valid_subscriptions;
        }

        $subscriptions = wcs_get_users_subscriptions($user_id); 
        foreach ( $subscriptions as $sub_id => $subscription ) {
            if ( $subscription->get_status() == 'active' ) {
                array_push($valid_subscriptions, $subscription);
            }
        }
        
        return $valid_subscriptions;
    }

    private function get_nearest_expiration_date($subscriptions) {
        if(count($subscriptions) == 0) {
            return null;
        }
        $expiration_date = null;
        foreach ( $subscriptions as $sub_id => $subscription ) {
            $end_date = new DateTime($subscription->get_date('end'));
            // If nothing is set just use as default
            if($expiration_date === null) {
                $expiration_date = $end_date;
                continue;
            }

            if($end_date < $expiration_date) {
                $expiration_date = $end_date;
            }
        }
        return $expiration_date->format(DateTime::ATOM);
    }

    private function get_entitlements($subscriptions) {
        $entitlements = array();
        if(count($subscriptions) == 0) {
            return $entitlements;
        }   

        foreach ($subscriptions as $sub_id => $subscription ) {
            $tags = $this->get_tags($subscription);
            foreach($tags as $tag) {
                $entitlements[$tag] = 1;
            }
        }

        return array_keys($entitlements);
    }

    private function get_tags($subscription) {
        $response = array();
        if (sizeof($subscription_items = $subscription->get_items()) == 0) {
            return $tags;
        }

        $subscription_items = $subscription->get_items();
        foreach ($subscription_items as $item_id => $item) {
            $product = $item->get_product();
            $tags = $product->tag_ids;
            foreach($tags as $tag) {
                array_push($response, get_term($tag)->name);
            }
        }

        return $response;
    }   
}
?>