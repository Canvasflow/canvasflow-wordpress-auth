<?php
/**
 * Handles the entitlement for the user
 *
 */
class CFA_Entitlements {
    /**
     * Get entitlement from the user
     *
     * @param integer $user_id
     * @param array
     */
    public function get_user_entitlements($user_id) {
        $subscriptions = $this->get_valid_subscriptions($user_id);

        $expiration_date = $this->get_nearest_expiration_date($subscriptions);
        $features = $this->get_features($subscriptions);

        return array(
            'entitlements' => $features,
            'expiration_date' =>$expiration_date,
        );
    }

    /**
     * Get valid subscriptions from a user
     *
     * @param integer $user_id
     * @param WC_Subscription[]
     */
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

    /**
     * Get the closest expiration date
     *
     * @param WC_Subscription[] $subscriptions
     * @return string|NULL
     */
    private function get_nearest_expiration_date($subscriptions) {
        if(count($subscriptions) == 0) {
            return NULL;
        }
        $expiration_date = NULL;
        foreach ( $subscriptions as $sub_id => $subscription ) {
            $end_date = new DateTime($subscription->get_date('end'));
            // If nothing is set just use as default
            if($expiration_date == NULL) {
                $expiration_date = $end_date;
                continue;
            }

            if($end_date < $expiration_date) {
                $expiration_date = $end_date;
            }
        }
        return $expiration_date->format(DateTime::ATOM);
    }

    /**
     * Get the features from subscriptions
     *
     * @param WC_Subscription[] $subscriptions
     * @return string[]
     */
    private function get_features($subscriptions) {
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

    /**
     * Get the tags from a subscription
     *
     * @param WC_Subscription $subscription
     * @return string[]
     */
    private function get_tags($subscription) {
        $response = array();
        if (sizeof($subscription_items = $subscription->get_items()) == 0) {
            return $response;
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